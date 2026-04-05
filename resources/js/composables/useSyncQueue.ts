/**
 * Sync Queue Composable
 *
 * Vue 3 composable providing reactive state and actions for the sync queue
 * management UI. Integrates with background sync service, IndexedDB, and
 * provides modal control and manual sync operations.
 *
 * Features:
 * - Reactive pending count and items list
 * - Auto-refresh every 30 seconds
 * - Modal state management with ESC key handler
 * - Manual sync with progress tracking
 * - Integration with background sync events
 * - Toast notifications for user feedback
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useSyncQueue } from '@/composables/useSyncQueue'
 *
 * const {
 *   pendingCount,
 *   isModalOpen,
 *   openModal,
 *   closeModal,
 *   syncNow
 * } = useSyncQueue()
 * </script>
 *
 * <template>
 *   <button @click="openModal">
 *     Sync Queue ({{ pendingCount }})
 *   </button>
 *   
 *   <SyncQueueModal 
 *     :show="isModalOpen"
 *     @close="closeModal"
 *   />
 * </template>
 * ```
 */

import { useIntervalFn } from '@vueuse/core';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import { useBackgroundSync } from '@/composables/useBackgroundSync';
import { useOnlineStatus } from '@/composables/useOnlineStatus';
import { useToast } from '@/composables/useToast';
import { indexedDBService } from '@/services/indexeddb';
import { syncService } from '@/services/syncService';
import type { IDBSyncQueueItem } from '@/types/indexeddb';
import type {
    BadgeStatus,
    SyncProgress,
    SyncQueueItemDisplay,
    SyncResult,
    SyncStatus,
} from '@/types/sync';

/**
 * Auto-refresh interval for pending count (milliseconds).
 *
 * WHY: 30 seconds balances freshness with performance.
 * Prevents excessive database queries while keeping UI reasonably up-to-date.
 */
const AUTO_REFRESH_INTERVAL_MS = 30000; // 30 seconds

/**
 * Sync queue composable return type.
 */
export interface UseSyncQueueReturn {
    // Core Data
    /** Number of pending sync items */
    pendingCount: Readonly<ReturnType<typeof ref<number>>>;

    /** Array of pending sync queue items */
    pendingItems: Readonly<ReturnType<typeof ref<IDBSyncQueueItem[]>>>;

    /** Transformed items for UI display */
    displayItems: Readonly<ReturnType<typeof computed<SyncQueueItemDisplay[]>>>;

    // Sync Status
    /** Whether a sync operation is currently in progress */
    isSyncing: Readonly<ReturnType<typeof ref<boolean>>>;

    /** Timestamp of the last sync attempt */
    lastSyncAt: Readonly<ReturnType<typeof ref<Date | null>>>;

    /** Result of the last sync operation */
    lastSyncResult: Readonly<ReturnType<typeof ref<SyncResult | null>>>;

    /** Current sync progress (if syncing) */
    currentProgress: Readonly<ReturnType<typeof ref<SyncProgress | undefined>>>;

    // Modal State
    /** Whether the sync queue modal is currently open */
    isModalOpen: Readonly<ReturnType<typeof ref<boolean>>>;

    // Loading States
    /** Whether initial data is being loaded */
    isLoading: Readonly<ReturnType<typeof ref<boolean>>>;

    /** Whether data is being refreshed */
    isRefreshing: Readonly<ReturnType<typeof ref<boolean>>>;

    // Computed State
    /** Badge status based on current state */
    badgeStatus: Readonly<ReturnType<typeof computed<BadgeStatus>>>;

    /** Whether user is online */
    isOnline: Readonly<ReturnType<typeof computed<boolean>>>;

    /** Formatted last sync time string */
    formattedLastSyncTime: Readonly<ReturnType<typeof computed<string>>>;

    // Actions
    /** Refresh pending count and items from IndexedDB */
    refreshQueueData: () => Promise<void>;

    /** Open sync queue modal */
    openModal: () => void;

    /** Close sync queue modal */
    closeModal: () => void;

    /** Trigger manual sync of all pending items */
    syncNow: () => Promise<void>;

    /** Retry a specific failed item */
    retryFailedItem: (itemId: number) => Promise<void>;

    // Helpers
    /** Get status color class for a given sync status */
    getItemStatusColor: (status: SyncStatus) => string;

    /** Get status label for a given sync status */
    getItemStatusLabel: (status: SyncStatus) => string;
}

/**
 * Sync queue composable.
 * Provides reactive state and actions for sync queue management UI.
 *
 * @returns Sync queue state and actions
 */
export function useSyncQueue(): UseSyncQueueReturn {
    // ==============================================================================
    // Dependencies
    // ==============================================================================

    const { isOnline } = useOnlineStatus();
    const { showToast } = useToast();
    const backgroundSync = useBackgroundSync();

    // ==============================================================================
    // Reactive State
    // ==============================================================================

    /**
     * Number of pending sync items.
     */
    const pendingCount = ref<number>(0);

    /**
     * Array of pending sync queue items from IndexedDB.
     */
    const pendingItems = ref<IDBSyncQueueItem[]>([]);

    /**
     * Whether a sync operation is currently in progress.
     */
    const isSyncing = ref<boolean>(false);

    /**
     * Timestamp of the last sync attempt.
     */
    const lastSyncAt = ref<Date | null>(null);

    /**
     * Result of the last sync operation.
     */
    const lastSyncResult = ref<SyncResult | null>(null);

    /**
     * Current sync progress (if syncing).
     */
    const currentProgress = ref<SyncProgress | undefined>(undefined);

    /**
     * Whether the sync queue modal is currently open.
     */
    const isModalOpen = ref<boolean>(false);

    /**
     * Whether initial data is being loaded.
     */
    const isLoading = ref<boolean>(false);

    /**
     * Whether data is being refreshed.
     */
    const isRefreshing = ref<boolean>(false);

    // ==============================================================================
    // Computed Properties
    // ==============================================================================

    /**
     * Badge status based on current sync state.
     *
     * WHY: Single source of truth for badge visual state.
     * Prevents inconsistencies between badge and actual state.
     */
    const badgeStatus = computed<BadgeStatus>(() => {
        if (isSyncing.value) {
return 'syncing';
        }

        if (lastSyncResult.value && lastSyncResult.value.failureCount > 0) {
            return 'error';
        }

        if (pendingCount.value > 0) {
            return 'pending';
        }

        return 'synced';
    });

    /**
     * Transformed sync queue items for UI display.
     *
     * WHY: Separates data transformation from UI rendering.
     * Caches formatted values for performance.
     */
    const displayItems = computed<SyncQueueItemDisplay[]>(() => {
        return pendingItems.value.map((item) => ({
            id: item.id!,
            tripDate: formatTripDate(item.createdAt),
            status: item.status,
            statusColor: getItemStatusColor(item.status),
            statusLabel: getItemStatusLabel(item.status),
            retryCount: item.retryCount,
            errorMessage: item.errorMessage || null,
            canRetry: item.status === 'failed' && item.retryCount < 3,
        }));
    });

    /**
     * Formatted last sync time string.
     *
     * WHY: Centralized date formatting for consistency.
     */
    const formattedLastSyncTime = computed<string>(() => {
        if (!lastSyncAt.value) {
            return 'Belum pernah sync';
        }

        const now = new Date();
        const diffMs = now.getTime() - lastSyncAt.value.getTime();
        const diffMinutes = Math.floor(diffMs / 60000);

        if (diffMinutes < 1) {
            return 'Baru saja';
        }

        if (diffMinutes < 60) {
            return `${diffMinutes} menit yang lalu`;
        }

        const diffHours = Math.floor(diffMinutes / 60);

        if (diffHours < 24) {
            return `${diffHours} jam yang lalu`;
        }

        // Format as "3 Apr 2026, 14:30 WIB"
        return lastSyncAt.value.toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'Asia/Jakarta',
            timeZoneName: 'short',
        });
    });

    // ==============================================================================
    // Actions
    // ==============================================================================

    /**
     * Refresh pending count and items from IndexedDB.
     *
     * Lightweight count check is fast, full items list is only
     * fetched when modal is open to optimize performance.
     *
     * WHY: Separates count (always needed) from items (only when viewing).
     * Reduces database query overhead for background updates.
     *
     * @returns Promise resolving when data is refreshed
     */
    async function refreshQueueData(): Promise<void> {
        try {
            isRefreshing.value = true;

            // Always refresh count (lightweight)
            const count = await syncService.getPendingTripCount();
            pendingCount.value = count;

            // Only refresh full items list if modal is open (heavier query)
            if (isModalOpen.value) {
                const items = await indexedDBService.getPendingSyncItems();
                pendingItems.value = items;
            }
        } catch (error) {
            console.error('[useSyncQueue] Failed to refresh queue data:', error);
        } finally {
            isRefreshing.value = false;
        }
    }

    /**
     * Open sync queue modal.
     *
     * Fetches full items list when modal opens to ensure
     * fresh data is displayed.
     */
    function openModal(): void {
        isModalOpen.value = true;

        // Fetch full items list when modal opens
        refreshQueueData();
    }

    /**
     * Close sync queue modal.
     *
     * Clears items list to free memory when modal is closed.
     */
    function closeModal(): void {
        isModalOpen.value = false;

        // Clear items list to free memory
        pendingItems.value = [];
    }

    /**
     * Trigger manual sync of all pending items.
     *
     * Shows toast notifications for success/failure and updates
     * UI state in real-time via progress callback.
     *
     * WHY: Manual sync allows user control when auto-sync is disabled
     * or when they want to force immediate synchronization.
     *
     * @returns Promise resolving when sync completes
     */
    async function syncNow(): Promise<void> {
        if (isSyncing.value) {
            console.warn('[useSyncQueue] Sync already in progress');

            return;
        }

        if (!isOnline.value) {
            showToast('Tidak ada koneksi internet', 'error');

            return;
        }

        if (pendingCount.value === 0) {
            showToast('Tidak ada item untuk disinkronkan', 'info');

            return;
        }

        try {
            isSyncing.value = true;
            lastSyncAt.value = new Date();

            // Set progress callback to update UI in real-time
            syncService.setProgressCallback((progress: SyncProgress) => {
                currentProgress.value = progress;
            });

            // Execute sync
            const result = await syncService.syncAllPendingTrips();

            // Store result
            lastSyncResult.value = result;

            // Show success/error toast
            if (result.failureCount === 0) {
                showToast(
                    `Berhasil sync ${result.successCount} perjalanan`,
                    'success',
                );
            } else if (result.successCount === 0) {
                showToast(
                    `Gagal sync ${result.failureCount} perjalanan`,
                    'error',
                );
            } else {
                showToast(
                    `Sync ${result.successCount} berhasil, ${result.failureCount} gagal`,
                    'warning',
                );
            }

            // Refresh data after sync
            await refreshQueueData();
        } catch (error) {
            console.error('[useSyncQueue] Sync failed:', error);
            showToast('Terjadi kesalahan saat sinkronisasi', 'error');
        } finally {
            isSyncing.value = false;
            currentProgress.value = undefined;

            // Clear progress callback
            syncService.setProgressCallback(undefined);
        }
    }

    /**
     * Retry a specific failed sync item.
     *
     * WHY: Allows user to manually retry individual failed items
     * without re-syncing everything.
     *
     * @param itemId - Sync queue item ID to retry
     * @returns Promise resolving when retry completes
     */
    async function retryFailedItem(itemId: number): Promise<void> {
        if (!isOnline.value) {
            showToast('Tidak ada koneksi internet', 'error');

            return;
        }

        try {
            // Find the item
            const item = pendingItems.value.find((i) => i.id === itemId);

            if (!item) {
                throw new Error(`Item dengan ID ${itemId} tidak ditemukan`);
            }

            // Sync the item
            await syncService.syncOfflineTrip(item);

            showToast('Item berhasil disinkronkan', 'success');

            // Refresh data
            await refreshQueueData();
        } catch (error) {
            console.error('[useSyncQueue] Failed to retry item:', error);
            showToast('Gagal menyinkronkan item', 'error');
        }
    }

    // ==============================================================================
    // Helper Functions
    // ==============================================================================

    /**
     * Get Tailwind CSS color class for sync status badge.
     *
     * @param status - Sync status
     * @returns Tailwind color class
     */
    function getItemStatusColor(status: SyncStatus): string {
        switch (status) {
            case 'pending':
                return 'text-yellow-400 bg-yellow-500/10 border-yellow-500/30';
            case 'syncing':
                return 'text-cyan-400 bg-cyan-500/10 border-cyan-500/30';
            case 'completed':
                return 'text-green-400 bg-green-500/10 border-green-500/30';
            case 'failed':
                return 'text-red-400 bg-red-500/10 border-red-500/30';
            default:
                return 'text-gray-400 bg-gray-500/10 border-gray-500/30';
        }
    }

    /**
     * Get human-readable status label in Indonesian.
     *
     * @param status - Sync status
     * @returns Status label
     */
    function getItemStatusLabel(status: SyncStatus): string {
        switch (status) {
            case 'pending':
                return 'Menunggu Sync';
            case 'syncing':
                return 'Sedang Sync';
            case 'completed':
                return 'Tersinkronisasi';
            case 'failed':
                return 'Gagal';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Format trip date for display.
     *
     * @param dateString - ISO 8601 date string
     * @returns Formatted date string
     */
    function formatTripDate(dateString: string): string {
        const date = new Date(dateString);

        return date.toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'Asia/Jakarta',
        });
    }

    // ==============================================================================
    // Event Handlers
    // ==============================================================================

    /**
     * Handle ESC key press to close modal.
     *
     * WHY: Standard UX pattern for modal dismissal.
     */
    function handleEscKey(event: KeyboardEvent): void {
        if (event.key === 'Escape' && isModalOpen.value) {
            closeModal();
        }
    }

    // ==============================================================================
    // Lifecycle & Watchers
    // ==============================================================================

    /**
     * Watch background sync state for updates.
     *
     * WHY: Auto-updates UI when background sync completes.
     * Keeps badge count fresh without manual intervention.
     */
    watch(
        () => backgroundSync.lastSyncAt.value,
        async (newValue) => {
            if (newValue) {
                lastSyncAt.value = newValue;
                lastSyncResult.value = backgroundSync.lastSyncResult.value;

                // Refresh data after background sync
                await refreshQueueData();
            }
        },
    );

    /**
     * Watch background sync isSyncing state.
     *
     * WHY: Shows sync status even when triggered by auto-sync.
     */
    watch(
        () => backgroundSync.isSyncing.value,
        (newValue) => {
            // Only update if not manually syncing
            if (!isSyncing.value) {
                isSyncing.value = newValue;
            }

            if (newValue) {
                currentProgress.value = backgroundSync.currentProgress.value;
            }
        },
    );

    /**
     * Initialize composable on mount.
     */
    onMounted(async () => {
        // Load initial data
        isLoading.value = true;

        try {
            await refreshQueueData();

            // Set up auto-refresh interval
            useIntervalFn(refreshQueueData, AUTO_REFRESH_INTERVAL_MS);

            // Add ESC key listener
            window.addEventListener('keydown', handleEscKey);
        } catch (error) {
            console.error('[useSyncQueue] Failed to initialize:', error);
        } finally {
            isLoading.value = false;
        }
    });

    /**
     * Clean up on unmount.
     */
    onUnmounted(() => {
        // Remove ESC key listener
        window.removeEventListener('keydown', handleEscKey);
    });

    // ==============================================================================
    // Return
    // ==============================================================================

    return {
        // Core Data
        pendingCount,
        pendingItems,
        displayItems,

        // Sync Status
        isSyncing,
        lastSyncAt,
        lastSyncResult,
        currentProgress,

        // Modal State
        isModalOpen,

        // Loading States
        isLoading,
        isRefreshing,

        // Computed State
        badgeStatus,
        isOnline,
        formattedLastSyncTime,

        // Actions
        refreshQueueData,
        openModal,
        closeModal,
        syncNow,
        retryFailedItem,

        // Helpers
        getItemStatusColor,
        getItemStatusLabel,
    };
}
