/**
 * Background Sync Composable
 *
 * Provides automatic synchronization of offline trips when internet connection
 * is restored. Implements retry logic with exponential backoff, respects user
 * settings, and provides real-time sync status tracking.
 *
 * Features:
 * - Auto-sync on online event
 * - Exponential backoff retry mechanism (5s, 15s, 45s)
 * - Network quality detection
 * - Concurrent sync prevention
 * - Sync history tracking (max 10 entries)
 * - Integration with settings store
 * - Page visibility detection for foreground sync
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useBackgroundSync } from '@/composables/useBackgroundSync';
 *
 * const {
 *   isSyncing,
 *   lastSyncAt,
 *   syncHistory,
 *   startManualSync
 * } = useBackgroundSync();
 * </script>
 *
 * <template>
 *   <div v-if="isSyncing">Syncing...</div>
 *   <button @click="startManualSync">Sync Now</button>
 * </template>
 * ```
 */

import { useDebounceFn } from '@vueuse/core';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

import { useOnlineStatus } from '@/composables/useOnlineStatus';
import { useToast } from '@/composables/useToast';
import { syncService } from '@/services/syncService';
import { useSettingsStore } from '@/stores/settings';
import { useTripStore } from '@/stores/trip';
import type {
    BackgroundSyncState,
    SyncHistoryEntry,
    SyncProgress,
    SyncResult,
} from '@/types/sync';

/**
 * Retry delay configuration (exponential backoff).
 *
 * WHY: Progressive delays reduce server load and improve success rate.
 * Delays: 5s, 15s, 45s = reasonable wait times without being too long.
 */
const RETRY_DELAYS_MS = [5000, 15000, 45000]; // 5s, 15s, 45s
const MAX_RETRY_ATTEMPTS = 3;

/**
 * Maximum number of sync history entries to keep in memory.
 *
 * WHY: Follows Miller's Law (7±2 items), prevents memory bloat.
 */
const MAX_SYNC_HISTORY = 10;

/**
 * Debounce delay for online event (milliseconds).
 *
 * WHY: Prevents rapid fire syncs during network instability.
 */
const ONLINE_DEBOUNCE_MS = 1000;

/**
 * Background sync composable return type.
 */
export interface UseBackgroundSyncReturn {
    /** Current sync state */
    state: Readonly<ReturnType<typeof ref<BackgroundSyncState>>>;

    /** Whether sync is currently in progress */
    isSyncing: Readonly<ReturnType<typeof computed<boolean>>>;

    /** Timestamp of last sync attempt */
    lastSyncAt: Readonly<ReturnType<typeof computed<Date | null>>>;

    /** Result of last sync operation */
    lastSyncResult: Readonly<ReturnType<typeof computed<SyncResult | null>>>;

    /** Current retry attempt count */
    retryCount: Readonly<ReturnType<typeof computed<number>>>;

    /** Sync history (max 10 entries) */
    syncHistory: Readonly<ReturnType<typeof computed<SyncHistoryEntry[]>>>;

    /** Current sync progress (if syncing) */
    currentProgress: Readonly<ReturnType<typeof computed<SyncProgress | undefined>>>;

    /** Whether auto-sync is enabled in settings */
    isAutoSyncEnabled: Readonly<ReturnType<typeof computed<boolean>>>;

    /** Manually trigger a sync operation */
    startManualSync: () => Promise<void>;

    /** Cancel pending retry */
    cancelRetry: () => void;
}

/**
 * Background synchronization composable.
 *
 * Monitors network connectivity and automatically syncs offline trips
 * when connection is restored. Implements intelligent retry logic,
 * respects user preferences, and provides comprehensive sync tracking.
 *
 * @returns Object with sync state and control methods
 *
 * @example
 * ```ts
 * // Basic usage - auto-sync on mount
 * const { isSyncing, lastSyncAt } = useBackgroundSync();
 *
 * if (isSyncing.value) {
 *   console.log('Sync in progress...');
 * }
 * ```
 *
 * @example
 * ```ts
 * // Manual sync trigger
 * const { startManualSync, isSyncing } = useBackgroundSync();
 *
 * async function handleSyncClick() {
 *   if (!isSyncing.value) {
 *     await startManualSync();
 *   }
 * }
 * ```
 */
export function useBackgroundSync(): UseBackgroundSyncReturn {
    // ===========================================================================
    // Composables & Stores
    // ===========================================================================

    const { isOnline, effectiveType } = useOnlineStatus();
    const { showSuccess, showError, showWarning } = useToast();
    const settingsStore = useSettingsStore();
    const tripStore = useTripStore();

    // ===========================================================================
    // State
    // ===========================================================================

    /**
     * Background sync state.
     *
     * Tracks all aspects of the sync system including status, history, and retry logic.
     */
    const state = ref<BackgroundSyncState>({
        isSyncing: false,
        lastSyncAt: null,
        lastSyncResult: null,
        retryCount: 0,
        nextRetryAt: null,
        syncHistory: [],
        currentProgress: undefined,
    });

    /**
     * Retry timeout ID for cancellation.
     *
     * WHY: Allows canceling scheduled retries when needed.
     */
    let retryTimeoutId: ReturnType<typeof setTimeout> | null = null;

    /**
     * Sync lock to prevent concurrent sync operations.
     *
     * WHY: Mutex pattern prevents multiple simultaneous syncs.
     */
    let syncLock = false;

    // ===========================================================================
    // Computed Properties
    // ===========================================================================

    /**
     * Whether sync is currently in progress.
     */
    const isSyncing = computed(() => state.value.isSyncing);

    /**
     * Timestamp of last sync attempt.
     */
    const lastSyncAt = computed(() => state.value.lastSyncAt);

    /**
     * Result of last sync operation.
     */
    const lastSyncResult = computed(() => state.value.lastSyncResult);

    /**
     * Current retry attempt count.
     */
    const retryCount = computed(() => state.value.retryCount);

    /**
     * Sync history (max 10 entries).
     */
    const syncHistory = computed(() => state.value.syncHistory);

    /**
     * Current sync progress (if syncing).
     */
    const currentProgress = computed(() => state.value.currentProgress);

    /**
     * Whether auto-sync is enabled in settings.
     *
     * WHY: Centralized check for auto-sync preference.
     */
    const isAutoSyncEnabled = computed(
        () => (settingsStore.settings as any).auto_sync_enabled ?? true,
    );

    // ===========================================================================
    // Core Sync Logic
    // ===========================================================================

    /**
     * Perform sync operation with full error handling and history tracking.
     *
     * Core sync workflow:
     * 1. Check sync lock (prevent concurrent syncs)
     * 2. Check pending trips count
     * 3. Set syncing state
     * 4. Register progress callback
     * 5. Call syncService.syncAllPendingTrips()
     * 6. Handle result (success/partial/failure)
     * 7. Update history
     * 8. Clear retry count on success
     * 9. Schedule retry on failure
     *
     * WHY: Centralized sync logic ensures consistency across auto and manual triggers.
     *
     * @param trigger - What triggered this sync (auto/manual/retry)
     * @returns Promise resolving when sync completes
     */
    async function performSync(
        trigger: 'auto' | 'manual' | 'retry',
    ): Promise<void> {
        // Step 1: Check sync lock
        if (syncLock) {
            return;
        }

        // Step 2: Check if there are pending trips
        const pendingCount = await syncService.getPendingTripCount();

        if (pendingCount === 0) {
            return;
        }

        // Step 3: Acquire lock and set syncing state
        syncLock = true;
        state.value.isSyncing = true;
        state.value.currentProgress = undefined;

        const startTime = Date.now();

        // Show toast notification
        if (trigger === 'auto') {
            showSuccess(`Menyinkronkan ${pendingCount} perjalanan offline...`);
        }

        try {
            // Step 4: Register progress callback
            syncService.onProgress((progress: SyncProgress) => {
                state.value.currentProgress = progress;
            });

            // Step 5: Call sync service
            const result = await syncService.syncAllPendingTrips();

            const durationMs = Date.now() - startTime;

            // Step 6: Update state with result
            state.value.lastSyncAt = new Date();
            state.value.lastSyncResult = result;

            // Step 7: Add to history (keep max 10)
            const historyEntry: SyncHistoryEntry = {
                timestamp: new Date(),
                result,
                trigger,
                durationMs,
            };

            state.value.syncHistory = [
                historyEntry,
                ...state.value.syncHistory,
            ].slice(0, MAX_SYNC_HISTORY);

            // Step 8: Handle result
            if (result.failureCount === 0) {
                // Full success - clear retry count
                state.value.retryCount = 0;
                state.value.nextRetryAt = null;

                showSuccess(
                    `Sinkronisasi berhasil! ${result.successCount} perjalanan tersinkronisasi`,
                );

                // Refresh trip list if on MyTrips page
                // (Components will watch sync state and refresh)
            } else if (result.successCount > 0) {
                // Partial success - show warning
                showWarning(
                    `${result.successCount} dari ${result.totalAttempted} perjalanan tersinkronisasi`,
                );

                // Schedule retry for failed trips
                scheduleRetry();
            } else {
                // Complete failure - schedule retry
                showError('Sinkronisasi gagal. Akan mencoba lagi...');
                scheduleRetry();
            }
        } catch (error: any) {
            // Step 9: Handle sync error
            const durationMs = Date.now() - startTime;

            console.error('[useBackgroundSync] Sync error:', error);

            state.value.lastSyncAt = new Date();
            state.value.lastSyncResult = {
                successCount: 0,
                failureCount: pendingCount,
                totalAttempted: pendingCount,
            };

            // Add error to history
            const historyEntry: SyncHistoryEntry = {
                timestamp: new Date(),
                result: state.value.lastSyncResult,
                trigger,
                durationMs,
                error: error.message || 'Unknown error',
            };

            state.value.syncHistory = [
                historyEntry,
                ...state.value.syncHistory,
            ].slice(0, MAX_SYNC_HISTORY);

            showError('Sinkronisasi gagal. Akan mencoba lagi...');
            scheduleRetry();
        } finally {
            // Step 10: Release lock and clear progress
            syncLock = false;
            state.value.isSyncing = false;
            state.value.currentProgress = undefined;
            syncService.clearProgressCallback();
        }
    }

    /**
     * Schedule retry with exponential backoff.
     *
     * Retry delays:
     * - Attempt 1: 5 seconds
     * - Attempt 2: 15 seconds
     * - Attempt 3: 45 seconds
     * - Give up after 3 attempts
     *
     * WHY: Exponential backoff prevents server overload and improves success rate.
     */
    function scheduleRetry(): void {
        // Check if max retries reached
        if (state.value.retryCount >= MAX_RETRY_ATTEMPTS) {
            state.value.retryCount = 0;
            state.value.nextRetryAt = null;

            return;
        }

        // Clear existing retry timeout
        if (retryTimeoutId !== null) {
            clearTimeout(retryTimeoutId);
        }

        // Calculate delay based on retry count
        const delay = RETRY_DELAYS_MS[state.value.retryCount];
        state.value.nextRetryAt = new Date(Date.now() + delay);

        // Schedule retry
        retryTimeoutId = setTimeout(() => {
            state.value.retryCount++;
            performSync('retry');
        }, delay);
    }

    /**
     * Cancel pending retry.
     *
     * Clears scheduled retry timeout and resets retry state.
     *
     * WHY: Allows user to cancel retry or clear state on manual sync.
     */
    function cancelRetry(): void {
        if (retryTimeoutId !== null) {
            clearTimeout(retryTimeoutId);
            retryTimeoutId = null;
        }

        state.value.retryCount = 0;
        state.value.nextRetryAt = null;
    }

    /**
     * Check if network quality is acceptable for sync.
     *
     * Skip sync on slow-2g (too slow, high failure rate).
     * Delay sync on 2g (wait 5 seconds before starting).
     * Immediate sync on 3g/4g/wifi/unknown.
     *
     * WHY: Network quality detection prevents failed syncs and saves battery.
     *
     * @returns True if network is good enough for sync
     */
    function isNetworkQualityAcceptable(): boolean {
        // Skip on slow-2g
        if (effectiveType.value === 'slow-2g') {
            showWarning(
                'Koneksi terlalu lambat untuk sinkronisasi. Tunggu koneksi lebih baik.',
            );

            return false;
        }

        // Delay on 2g
        if (effectiveType.value === '2g') {
            setTimeout(() => {
                if (isOnline.value) {
                    performSync('auto');
                }
            }, 5000);

            return false;
        }

        // OK for 3g, 4g, wifi, unknown
        return true;
    }

    /**
     * Check if sync should be triggered.
     *
     * Checks:
     * 1. Auto-sync enabled in settings
     * 2. Network is online
     * 3. Network quality acceptable
     * 4. No active trip in progress
     * 5. Pending trips exist
     *
     * WHY: Centralized eligibility check prevents unnecessary sync attempts.
     *
     * @returns True if sync should proceed
     */
    async function shouldTriggerSync(): Promise<boolean> {
        // Check auto-sync setting
        if (!isAutoSyncEnabled.value) {
            return false;
        }

        // Check online status
        if (!isOnline.value) {
            return false;
        }

        // Check network quality
        if (!isNetworkQualityAcceptable()) {
            return false;
        }

        // Check if active trip in progress
        if (tripStore.hasActiveTrip) {
            return false;
        }

        // Check if pending trips exist
        const pendingCount = await syncService.getPendingTripCount();

        if (pendingCount === 0) {
            return false;
        }

        return true;
    }

    /**
     * Trigger auto-sync when conditions are met.
     *
     * Called by online event watcher. Performs eligibility checks
     * before triggering sync.
     *
     * WHY: Debounced to handle rapid online/offline toggles gracefully.
     */
    const triggerAutoSync = useDebounceFn(async () => {
        if (await shouldTriggerSync()) {
            performSync('auto');
        }
    }, ONLINE_DEBOUNCE_MS);

    /**
     * Manually trigger sync operation.
     *
     * Exposed method for manual sync buttons. Bypasses auto-sync setting
     * but still checks other eligibility criteria.
     *
     * WHY: Manual sync always allowed regardless of auto-sync setting.
     *
     * @returns Promise resolving when sync completes
     *
     * @example
     * ```ts
     * const { startManualSync, isSyncing } = useBackgroundSync();
     *
     * async function handleSyncClick() {
     *   if (!isSyncing.value) {
     *     await startManualSync();
     *   }
     * }
     * ```
     */
    async function startManualSync(): Promise<void> {
        // Cancel any pending retry
        cancelRetry();

        // Perform sync
        await performSync('manual');
    }

    // ===========================================================================
    // Watchers
    // ===========================================================================

    /**
     * Watch online status for auto-sync trigger.
     *
     * Triggers sync when:
     * - Transitions from offline → online
     * - Auto-sync is enabled
     * - Network quality acceptable
     * - No active trip
     * - Pending trips exist
     *
     * WHY: Reactive to network changes, no polling needed.
     */
    watch(isOnline, (newOnline, oldOnline) => {
        // Only trigger on offline → online transition
        if (newOnline && !oldOnline) {
            triggerAutoSync();
        }
    });

    // ===========================================================================
    // Lifecycle Hooks
    // ===========================================================================

    /**
     * Page visibility change handler.
     *
     * Triggers sync check when app comes to foreground.
     *
     * WHY: Useful for PWA when user returns to app after being away.
     */
    function handleVisibilityChange(): void {
        if (document.visibilityState === 'visible' && isOnline.value) {
            triggerAutoSync();
        }
    }

    /**
     * Set up event listeners on mount.
     *
     * Registers Page Visibility API listener and performs initial sync check.
     */
    onMounted(() => {
        // Register visibility change listener
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Perform initial sync check if online
        if (isOnline.value) {
            triggerAutoSync();
        }
    });

    /**
     * Clean up event listeners on unmount.
     *
     * Removes listeners and cancels pending retries.
     *
     * WHY: Prevents memory leaks and orphaned timers.
     */
    onUnmounted(() => {
        // Remove visibility change listener
        document.removeEventListener(
            'visibilitychange',
            handleVisibilityChange,
        );

        // Cancel pending retry
        cancelRetry();

        // Clear progress callback
        syncService.clearProgressCallback();
    });

    // ===========================================================================
    // Return API
    // ===========================================================================

    return {
        state,
        isSyncing,
        lastSyncAt,
        lastSyncResult,
        retryCount,
        syncHistory,
        currentProgress,
        isAutoSyncEnabled,
        startManualSync,
        cancelRetry,
    };
}
