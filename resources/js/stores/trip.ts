/**
 * Trip Store - Trip session and speed tracking management.
 *
 * Manages active trip state, real-time speed logging, and trip statistics.
 * Integrates with Geolocation API and backend Trip API for seamless tracking.
 * Provides centralized state management for the speedometer interface.
 *
 * Key Features:
 * - Start/end trip sessions with API integration
 * - Real-time speed log buffering and batching
 * - Automatic statistics calculation (speed, distance, violations)
 * - Bulk speed log syncing to backend
 * - Error handling with retry logic
 *
 * @example
 * ```ts
 * import { useTripStore } from '@/stores/trip';
 *
 * const tripStore = useTripStore();
 *
 * // Start new trip
 * await tripStore.startTrip('Morning commute');
 *
 * // Add speed logs (called from geolocation callback every 5 seconds)
 * tripStore.addSpeedLog(45, Date.now(), 10);
 *
 * // Auto-sync when threshold reached
 * if (tripStore.needsSync) {
 *     await tripStore.syncSpeedLogs();
 * }
 *
 * // End trip
 * await tripStore.endTrip('Arrived safely');
 * ```
 *
 * @example
 * ```ts
 * // Integration with geolocation composable
 * import { useGeolocation } from '@/composables/useGeolocation';
 * import { useTripStore } from '@/stores/trip';
 *
 * const { watchSpeed, stopTracking } = useGeolocation();
 * const tripStore = useTripStore();
 *
 * // Start trip and begin tracking
 * await tripStore.startTrip();
 * watchSpeed((speed, state) => {
 *     tripStore.addSpeedLog(speed, state.timestamp, state.accuracy);
 * });
 *
 * // End trip and stop tracking
 * await tripStore.endTrip();
 * stopTracking();
 * ```
 */

import { useHttp } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import TripController from '@/actions/App/Http/Controllers/TripController';
import type {
    Trip,
    SpeedLog,
    TripStats,
    StartTripResponse,
    EndTripResponse,
    BulkSpeedLogsResponse,
} from '@/types/trip';
import { useSettingsStore } from './settings';

/**
 * Trip store for managing active trip sessions and speed tracking.
 *
 * Provides reactive state for current trip, speed logs, and real-time
 * statistics with actions for trip lifecycle management and API integration.
 */
export const useTripStore = defineStore('trip', () => {
    // ========================================================================
    // Dependencies
    // ========================================================================

    const http = useHttp();
    const settingsStore = useSettingsStore();

    // ========================================================================
    // State
    // ========================================================================

    /** Current active trip, null if no trip is in progress */
    const currentTrip = ref<Trip | null>(null);

    /**
     * Speed logs buffer for current trip.
     *
     * WHY: Buffer speed logs in memory before syncing to reduce API calls.
     * WHY: Cleared after successful sync to backend.
     */
    const speedLogs = ref<SpeedLog[]>([]);

    /** Whether trip tracking is currently active */
    const isTracking = ref<boolean>(false);

    /**
     * Real-time trip statistics.
     *
     * Computed locally from speed logs for immediate feedback.
     * Backend recalculates on trip end for accuracy.
     */
    const stats = ref<TripStats>({
        currentSpeed: 0,
        maxSpeed: 0,
        averageSpeed: 0,
        distance: 0,
        duration: 0,
        violationCount: 0,
    });

    /** Loading state for start trip operation */
    const isStarting = ref<boolean>(false);

    /** Loading state for end trip operation */
    const isEnding = ref<boolean>(false);

    /** Loading state for speed log sync operation */
    const isSyncing = ref<boolean>(false);

    /** Timestamp of last successful speed log sync */
    const lastSyncAt = ref<Date | null>(null);

    /** Current error message, null if no error */
    const error = ref<string | null>(null);

    /** Retry attempt counter for failed syncs */
    const syncRetryCount = ref<number>(0);

    /** Maximum number of sync retry attempts before giving up */
    const MAX_SYNC_RETRIES = 3;

    /** Speed log interval in seconds (must match backend) */
    const SPEED_LOG_INTERVAL_SECONDS = 5;

    // ========================================================================
    // Computed Properties
    // ========================================================================

    /**
     * Whether there is an active trip in progress.
     *
     * @returns True if currentTrip exists and status is 'in_progress'
     */
    const hasActiveTrip = computed<boolean>(
        () => currentTrip.value !== null && currentTrip.value.status === 'in_progress',
    );

    /**
     * Number of speed logs waiting to be synced.
     *
     * @returns Count of speed logs in the buffer
     */
    const pendingLogCount = computed<number>(() => speedLogs.value.length);

    /**
     * Whether speed logs should be synced to backend.
     *
     * WHY: Sync every 10 logs (50 seconds of tracking) to balance
     * between API efficiency and data safety.
     *
     * @returns True if 10 or more speed logs are buffered
     */
    const needsSync = computed<boolean>(() => speedLogs.value.length >= 10);

    // ========================================================================
    // Actions
    // ========================================================================

    /**
     * Start a new trip session.
     *
     * Creates a new trip via API and initializes tracking state.
     * Resets all speed logs and statistics for fresh session.
     *
     * @param notes - Optional notes about the trip
     * @returns Promise resolving when trip is successfully started
     * @throws Error if trip creation fails or user already has active trip
     *
     * @example
     * ```ts
     * try {
     *     await tripStore.startTrip('Morning commute to office');
     *     console.log('Trip started:', tripStore.currentTrip.id);
     * } catch (error) {
     *     console.error('Failed to start trip:', error);
     * }
     * ```
     */
    async function startTrip(notes?: string): Promise<void> {
        error.value = null;
        isStarting.value = true;

        try {
            /**
             * POST to /api/trips to create new trip.
             *
             * WHY: Use Wayfinder store() for type-safe route generation.
             * WHY: useHttp provides automatic CSRF token handling.
             */
            const response = await http.post<StartTripResponse>(
                TripController.store().url,
                { notes },
            );

            // Store the newly created trip
            currentTrip.value = response.trip;

            // Reset tracking state for new session
            speedLogs.value = [];
            isTracking.value = true;
            syncRetryCount.value = 0;
            lastSyncAt.value = null;

            // Reset statistics
            stats.value = {
                currentSpeed: 0,
                maxSpeed: 0,
                averageSpeed: 0,
                distance: 0,
                duration: 0,
                violationCount: 0,
            };
        } catch (err: any) {
            const errorMessage =
                err.response?.data?.message ||
                'Gagal memulai perjalanan. Silakan coba lagi.';
            error.value = errorMessage;

            throw new Error(errorMessage);
        } finally {
            isStarting.value = false;
        }
    }

    /**
     * Add a speed log entry to the buffer.
     *
     * Records speed measurement with violation detection and updates
     * real-time statistics. Does not sync to API immediately - logs
     * are batched and synced via syncSpeedLogs().
     *
     * @param speed - Speed in kilometers per hour
     * @param timestamp - Unix timestamp in milliseconds when speed was recorded
     *
     * @example
     * ```ts
     * // Called from geolocation watch callback
     * watchSpeed((speed, state) => {
     *     tripStore.addSpeedLog(speed, state.timestamp);
     *
     *     // Auto-sync when threshold reached
     *     if (tripStore.needsSync) {
     *         tripStore.syncSpeedLogs();
     *     }
     * });
     * ```
     */
    function addSpeedLog(
        speed: number,
        timestamp: number | null,
    ): void {
        if (!hasActiveTrip.value) {
            console.warn('Cannot add speed log: no active trip');

            return;
        }

        /**
         * Get speed limit from settings store.
         *
         * WHY: Settings are centralized and may be updated by admin.
         * WHY: Check on every log for accurate violation detection.
         */
        const speedLimit = settingsStore.settings.speed_limit;

        /**
         * Create speed log entry with violation detection.
         *
         * WHY: is_violation calculated client-side for immediate feedback.
         * WHY: Backend will recalculate for data integrity.
         */
        const log: SpeedLog = {
            speed,
            recorded_at: timestamp
                ? new Date(timestamp).toISOString()
                : new Date().toISOString(),
            is_violation: speed > speedLimit,
        };

        // Add to buffer
        speedLogs.value.push(log);

        // Update real-time statistics
        updateStats(speed);
    }

    /**
     * Update real-time trip statistics.
     *
     * Calculates current statistics from buffered speed logs.
     * Called automatically after each addSpeedLog().
     *
     * WHY: Provide immediate feedback to user during trip.
     * WHY: Backend recalculates final stats on trip end for accuracy.
     *
     * @param currentSpeed - Most recent speed reading in km/h
     */
    function updateStats(currentSpeed: number): void {
        if (!hasActiveTrip.value || speedLogs.value.length === 0) {
            return;
        }

        // Update current speed
        stats.value.currentSpeed = currentSpeed;

        // Calculate max speed from all logs
        stats.value.maxSpeed = Math.max(
            ...speedLogs.value.map((log) => log.speed),
        );

        // Calculate average speed from all logs
        const totalSpeed = speedLogs.value.reduce(
            (sum, log) => sum + log.speed,
            0,
        );
        stats.value.averageSpeed = Math.round(
            (totalSpeed / speedLogs.value.length) * 10,
        ) / 10;

        /**
         * Calculate distance from speed logs.
         *
         * WHY: Distance = sum of (speed * time_interval).
         * WHY: speed * 5 seconds / 3600 = km traveled in 5-second interval.
         * WHY: Assumes logs are recorded every 5 seconds (matches backend).
         */
        const totalDistance = speedLogs.value.reduce((sum, log) => {
            return sum + (log.speed * SPEED_LOG_INTERVAL_SECONDS) / 3600;
        }, 0);
        stats.value.distance = Math.round(totalDistance * 100) / 100; // Round to 2 decimals

        /**
         * Calculate trip duration.
         *
         * WHY: Duration = now - started_at.
         * WHY: Real-time calculation shows live elapsed time.
         */
        if (currentTrip.value?.started_at) {
            const startTime = new Date(currentTrip.value.started_at).getTime();
            const now = Date.now();
            stats.value.duration = Math.floor((now - startTime) / 1000);
        }

        // Count violations
        stats.value.violationCount = speedLogs.value.filter(
            (log) => log.is_violation,
        ).length;
    }

    /**
     * Sync buffered speed logs to backend.
     *
     * Performs bulk insert of speed logs via API and updates trip statistics
     * from backend response. Implements retry logic with exponential backoff
     * for failed syncs.
     *
     * @returns Promise resolving when sync completes successfully
     * @throws Error if sync fails after max retries
     *
     * @example
     * ```ts
     * // Auto-sync when threshold reached
     * if (tripStore.needsSync) {
     *     try {
     *         await tripStore.syncSpeedLogs();
     *         console.log('Synced', tripStore.pendingLogCount, 'logs');
     *     } catch (error) {
     *         console.error('Sync failed:', error);
     *     }
     * }
     * ```
     */
    async function syncSpeedLogs(): Promise<void> {
        // Skip if no logs to sync or no active trip
        if (speedLogs.value.length === 0 || !hasActiveTrip.value) {
            return;
        }

        // Skip if already syncing
        if (isSyncing.value) {
            return;
        }

        error.value = null;
        isSyncing.value = true;

        try {
            /**
             * POST to /api/trips/{id}/speed-logs for bulk insert.
             *
             * WHY: Batch API calls for efficiency (vs one call per log).
             * WHY: Backend calculates updated trip stats from all logs.
             */
            const response = await http.post<BulkSpeedLogsResponse>(
                TripController.storeSpeedLogs(currentTrip.value!.id).url,
                {
                    speed_logs: speedLogs.value.map((log) => ({
                        speed: log.speed,
                        recorded_at: log.recorded_at,
                    })),
                },
            );

            /**
             * Update trip statistics from backend response.
             *
             * WHY: Backend stats are authoritative after sync.
             * WHY: Ensures frontend stats match backend calculations.
             */
            if (currentTrip.value) {
                currentTrip.value.max_speed = response.trip.max_speed;
                currentTrip.value.average_speed = response.trip.average_speed;
                currentTrip.value.total_distance = response.trip.total_distance;
                currentTrip.value.violation_count = response.trip.violation_count;
                currentTrip.value.synced_at = response.trip.synced_at;
            }

            // Clear buffer after successful sync
            speedLogs.value = [];
            lastSyncAt.value = new Date();
            syncRetryCount.value = 0; // Reset retry counter

            console.log(
                `Successfully synced ${response.created_count} speed logs`,
            );
        } catch (err: any) {
            syncRetryCount.value++;

            const errorMessage =
                err.response?.data?.message ||
                'Gagal menyinkronkan data kecepatan.';

            /**
             * Retry logic with exponential backoff.
             *
             * WHY: Network issues may be temporary (poor signal, server load).
             * WHY: Exponential backoff reduces server load: 1s, 2s, 4s delays.
             * WHY: Preserve logs in buffer for retry instead of losing data.
             */
            if (syncRetryCount.value < MAX_SYNC_RETRIES) {
                const retryDelay = Math.pow(2, syncRetryCount.value - 1) * 1000; // 1s, 2s, 4s
                console.warn(
                    `Sync failed (attempt ${syncRetryCount.value}/${MAX_SYNC_RETRIES}). Retrying in ${retryDelay}ms...`,
                );

                // Wait and retry
                await new Promise((resolve) => setTimeout(resolve, retryDelay));

                return syncSpeedLogs(); // Recursive retry
            } else {
                // Max retries reached
                error.value = `${errorMessage} Maksimal ${MAX_SYNC_RETRIES} percobaan.`;
                console.error(
                    'Max sync retries reached. Speed logs preserved in buffer.',
                );

                throw new Error(error.value);
            }
        } finally {
            isSyncing.value = false;
        }
    }

    /**
     * End the current trip session.
     *
     * Syncs any remaining speed logs, marks trip as completed via API,
     * and cleans up tracking state. Final statistics are calculated by backend.
     *
     * @param notes - Optional notes to add/update for the trip
     * @returns Promise resolving when trip is successfully ended
     * @throws Error if trip end fails or no active trip exists
     *
     * @example
     * ```ts
     * try {
     *     await tripStore.endTrip('Arrived at destination');
     *     console.log('Trip ended. Distance:', tripStore.currentTrip.total_distance);
     * } catch (error) {
     *     console.error('Failed to end trip:', error);
     * }
     * ```
     */
    async function endTrip(notes?: string): Promise<void> {
        if (!hasActiveTrip.value) {
            throw new Error('Tidak ada perjalanan aktif untuk diakhiri');
        }

        error.value = null;
        isEnding.value = true;

        try {
            /**
             * Sync remaining speed logs before ending trip.
             *
             * WHY: Ensure all data is saved before final calculation.
             * WHY: Backend needs complete logs for accurate statistics.
             */
            if (speedLogs.value.length > 0) {
                await syncSpeedLogs();
            }

            /**
             * PUT to /api/trips/{id} to end trip.
             *
             * WHY: Backend calculates final statistics from all speed logs.
             * WHY: Updates status to 'completed' and sets ended_at timestamp.
             */
            const response = await http.put<EndTripResponse>(
                TripController.update(currentTrip.value!.id).url,
                { notes },
            );

            // Update trip with final statistics from backend
            currentTrip.value = response.trip;

            // Stop tracking
            isTracking.value = false;

            // Note: Don't clear currentTrip immediately so UI can display final stats
            // UI component should call clearTrip() when navigating away
        } catch (err: any) {
            const errorMessage =
                err.response?.data?.message ||
                'Gagal mengakhiri perjalanan. Silakan coba lagi.';
            error.value = errorMessage;

            throw new Error(errorMessage);
        } finally {
            isEnding.value = false;
        }
    }

    /**
     * Clear trip state and reset tracking.
     *
     * Resets all trip-related state to initial values. Should be called
     * after trip ends and user navigates away, or when starting fresh session.
     *
     * WHY: Clean slate for next trip session.
     * WHY: Prevents stale data from affecting new trips.
     *
     * @example
     * ```ts
     * // After trip ends and user navigates away
     * await tripStore.endTrip();
     * // ... show trip summary ...
     * tripStore.clearTrip(); // Clear when leaving summary page
     * ```
     */
    function clearTrip(): void {
        currentTrip.value = null;
        speedLogs.value = [];
        isTracking.value = false;
        lastSyncAt.value = null;
        syncRetryCount.value = 0;
        error.value = null;

        // Reset statistics
        stats.value = {
            currentSpeed: 0,
            maxSpeed: 0,
            averageSpeed: 0,
            distance: 0,
            duration: 0,
            violationCount: 0,
        };
    }

    // ========================================================================
    // Return Public API
    // ========================================================================

    return {
        // State
        currentTrip,
        speedLogs,
        isTracking,
        stats,
        isStarting,
        isEnding,
        isSyncing,
        lastSyncAt,
        error,

        // Computed Properties
        hasActiveTrip,
        pendingLogCount,
        needsSync,

        // Actions
        startTrip,
        addSpeedLog,
        syncSpeedLogs,
        endTrip,
        clearTrip,
    };
});
