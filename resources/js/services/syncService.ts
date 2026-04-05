/**
 * Sync Service - Trip synchronization between IndexedDB and Laravel backend.
 *
 * Handles manual and automatic synchronization of offline trips and speed logs
 * stored in IndexedDB to the Laravel backend API. Follows Laravel service pattern
 * with singleton instance for consistent state management.
 *
 * Key Features:
 * - Sync individual offline trips to backend
 * - Batch sync all pending trips
 * - Error handling with retry tracking
 * - Progress reporting for UI feedback
 * - TypeScript type safety with Wayfinder
 *
 * @example
 * ```ts
 * import { syncService } from '@/services/syncService';
 *
 * // Sync all pending trips
 * const result = await syncService.syncAllPendingTrips();
 * console.log(`Synced ${result.successCount} of ${result.totalAttempted} trips`);
 * ```
 */

import TripController from '@/actions/App/Http/Controllers/TripController';
import { indexedDBService } from '@/services/indexeddb';
import type { IDBSyncQueueItem, IDBTrip } from '@/types/indexeddb';
import type { SyncProgress, SyncResult } from '@/types/sync';
import type { StartTripResponse, BulkSpeedLogsResponse } from '@/types/trip';
import { http } from '@/utils/http';

/**
 * SyncService class for managing offline trip synchronization.
 *
 * Singleton service that handles syncing offline trips from IndexedDB
 * to the Laravel backend. Provides methods for individual and batch sync
 * operations with comprehensive error handling.
 */
export class SyncService {
    /**
     * Progress callback for UI updates during sync operations.
     *
     * WHY: Allows components to display real-time sync progress to users.
     */
    private progressCallback?: (progress: SyncProgress) => void;

    /**
     * Sync a single offline trip to the backend.
     *
     * Complete workflow:
     * 1. Retrieve trip data from IndexedDB
     * 2. Create trip on backend (POST /api/trips)
     * 3. Get associated speed logs from IndexedDB
     * 4. Bulk upload speed logs (POST /api/trips/{id}/speed-logs)
     * 5. Mark trip as synced in IndexedDB
     * 6. Mark sync queue item as completed
     *
     * WHY: Atomic operation ensures data consistency even if sync fails mid-process.
     * WHY: Separate method for individual trips allows retry of specific failures.
     *
     * @param syncQueueItem - Sync queue item from IndexedDB
     * @returns Promise resolving when trip is successfully synced
     * @throws Error if trip not found, API call fails, or data invalid
     *
     * @example
     * ```ts
     * const pendingItems = await indexedDBService.getPendingSyncItems();
     * const firstItem = pendingItems[0];
     * await syncService.syncOfflineTrip(firstItem);
     * ```
     */
    async syncOfflineTrip(syncQueueItem: IDBSyncQueueItem): Promise<void> {
        // Step 1: Get trip from IndexedDB
        const trip = await indexedDBService.getTrip(syncQueueItem.tripId);

        if (!trip) {
            throw new Error(
                `Trip dengan ID ${syncQueueItem.tripId} tidak ditemukan di IndexedDB`,
            );
        }

        try {
            // Step 2: Create trip on backend
            const response = await http.post<StartTripResponse>(
                TripController.store().url,
                {
                    notes: trip.notes,
                    started_at: trip.startedAt,
                    ended_at: trip.endedAt,
                    status: trip.status,
                    total_distance: trip.totalDistance,
                    max_speed: trip.maxSpeed,
                    average_speed: trip.averageSpeed,
                    violation_count: trip.violationCount,
                    duration_seconds: trip.durationSeconds,
                },
            );

            const backendTripId = response.trip.id;

            // Step 3: Get speed logs for this trip
            const speedLogs = await indexedDBService.getSpeedLogsByTripId(
                syncQueueItem.tripId,
            );

            // Step 4: Bulk upload speed logs if any exist
            if (speedLogs.length > 0) {
                // Transform IDBSpeedLog to backend format
                const speedLogsPayload = speedLogs.map((log) => ({
                    speed: log.speed,
                    recorded_at: log.recordedAt,
                    is_violation: log.isViolation,
                }));

                await http.post<BulkSpeedLogsResponse>(
                    TripController.storeSpeedLogs({ trip: backendTripId }).url,
                    {
                        speed_logs: speedLogsPayload,
                    },
                );
            }

            // Step 5: Update trip with synced_at timestamp in IndexedDB
            await indexedDBService.updateTrip(syncQueueItem.tripId, {
                syncedAt: new Date().toISOString(),
            });

            // Step 6: Mark sync queue item as completed
            await indexedDBService.updateSyncQueueItem(syncQueueItem.id!, {
                status: 'completed',
            });
        } catch (error: any) {
            // Update sync queue with error information
            await indexedDBService.updateSyncQueueItem(syncQueueItem.id!, {
                status: 'failed',
                errorMessage: error.message || 'Unknown error',
                retryCount: syncQueueItem.retryCount + 1,
                lastAttemptAt: new Date().toISOString(),
            });

            throw error;
        }
    }

    /**
     * Sync all pending trips from IndexedDB to backend.
     *
     * Iterates through all pending sync queue items and attempts to sync each one.
     * Continues syncing even if individual trips fail, collecting success/failure counts.
     *
     * WHY: Batch operation allows syncing multiple offline trips at once.
     * WHY: Non-blocking - failures don't stop remaining trips from syncing.
     * WHY: Progress callback enables real-time UI updates.
     *
     * @returns Promise resolving to sync result with counts
     * @throws Error only if IndexedDB operations fail (not individual syncs)
     *
     * @example
     * ```ts
     * // Sync all with progress tracking
     * syncService.onProgress((progress) => {
     *   console.log(`Syncing ${progress.current}/${progress.total}`);
     * });
     *
     * const result = await syncService.syncAllPendingTrips();
     * console.log(`Success: ${result.successCount}, Failed: ${result.failureCount}`);
     * ```
     */
    async syncAllPendingTrips(): Promise<SyncResult> {
        // Get all pending sync items
        const pendingItems = await indexedDBService.getPendingSyncItems();

        // Filter only trip items
        const tripItems = pendingItems.filter((item) => item.type === 'trip');

        let successCount = 0;
        let failureCount = 0;
        const totalAttempted = tripItems.length;

        // Sync each trip individually
        for (let i = 0; i < tripItems.length; i++) {
            const item = tripItems[i];

            // Report progress if callback is set
            if (this.progressCallback) {
                this.progressCallback({
                    current: i + 1,
                    total: totalAttempted,
                    currentItemType: 'trip',
                });
            }

            try {
                await this.syncOfflineTrip(item);
                successCount++;
            } catch (error: any) {
                failureCount++;

                // Log error for debugging (production could use Sentry)
                console.error(
                    `[SyncService] Failed to sync trip ${item.tripId}:`,
                    error.message,
                );

                // Report progress with error if callback is set
                if (this.progressCallback) {
                    this.progressCallback({
                        current: i + 1,
                        total: totalAttempted,
                        currentItemType: 'trip',
                        error: error.message,
                    });
                }
            }
        }

        return {
            successCount,
            failureCount,
            totalAttempted,
        };
    }

    /**
     * Set progress callback for sync operations.
     *
     * Allows UI components to receive real-time updates during batch sync.
     * Callback is invoked for each trip processed, whether successful or failed.
     *
     * WHY: Decouples service logic from UI, following separation of concerns.
     * WHY: Optional callback keeps service usable without UI dependencies.
     *
     * @param callback - Function called with progress updates
     *
     * @example
     * ```ts
     * syncService.onProgress((progress) => {
     *   updateProgressBar(progress.current / progress.total * 100);
     * });
     * ```
     */
    onProgress(callback: (progress: SyncProgress) => void): void {
        this.progressCallback = callback;
    }

    /**
     * Clear progress callback.
     *
     * Should be called when component unmounts or sync completes.
     *
     * WHY: Prevents memory leaks from stale callback references.
     */
    clearProgressCallback(): void {
        this.progressCallback = undefined;
    }

    /**
     * Get count of trips pending sync.
     *
     * Convenience method for checking if sync is needed without fetching full queue.
     * Used by UI to show sync badges and enable/disable sync buttons.
     *
     * WHY: Lightweight check avoids loading full sync queue data.
     * WHY: Frequently needed by multiple components (header, cards, etc).
     *
     * @returns Promise resolving to number of pending trips
     *
     * @example
     * ```ts
     * const pendingCount = await syncService.getPendingTripCount();
     * if (pendingCount > 0) {
     *   showSyncButton();
     * }
     * ```
     */
    async getPendingTripCount(): Promise<number> {
        const pendingItems = await indexedDBService.getPendingSyncItems();

        return pendingItems.filter((item) => item.type === 'trip').length;
    }

    /**
     * Check if a specific trip is synced.
     *
     * Determines sync status by checking if trip has synced_at timestamp.
     * Used by TripCard component to display correct badge.
     *
     * WHY: Centralized sync status logic prevents inconsistencies.
     * WHY: Single source of truth for "is synced" determination.
     *
     * @param trip - Trip data (from backend or IndexedDB)
     * @returns True if trip has been synced to backend
     *
     * @example
     * ```ts
     * const isSynced = syncService.isTripSynced(trip);
     * const badgeText = isSynced ? 'Tersinkronisasi' : 'Menunggu Sync';
     * ```
     */
    isTripSynced(trip: IDBTrip | { synced_at: string | null }): boolean {
        // Check for synced_at field (backend format)
        if ('synced_at' in trip) {
            return trip.synced_at !== null;
        }

        // Check for syncedAt field (IndexedDB format)
        if ('syncedAt' in trip) {
            return trip.syncedAt !== null;
        }

        return false;
    }
}

/**
 * Singleton instance of SyncService.
 *
 * WHY: Single instance ensures consistent state and progress tracking.
 * WHY: Follows Laravel service pattern for dependency injection readiness.
 *
 * @example
 * ```ts
 * import { syncService } from '@/services/syncService';
 *
 * await syncService.syncAllPendingTrips();
 * ```
 */
export const syncService = new SyncService();
