/**
 * Sync-related TypeScript types.
 *
 * Defines interfaces and types for synchronization operations between
 * IndexedDB (offline storage) and Laravel backend API.
 */

/**
 * Sync operation status.
 *
 * Indicates the current state of a sync operation or sync queue item.
 */
export type SyncStatus = 'pending' | 'syncing' | 'completed' | 'failed';

/**
 * Result of a sync operation.
 *
 * Contains counts of successful and failed sync attempts.
 *
 * @example
 * ```ts
 * const result: SyncResult = {
 *   successCount: 8,
 *   failureCount: 2,
 *   totalAttempted: 10
 * };
 * ```
 */
export interface SyncResult {
    /** Number of successfully synced items */
    successCount: number;

    /** Number of items that failed to sync */
    failureCount: number;

    /** Total number of items attempted to sync */
    totalAttempted: number;
}

/**
 * Toast notification type.
 *
 * Determines the visual style and semantic meaning of a toast message.
 */
export type ToastType = 'success' | 'error' | 'info' | 'warning';

/**
 * Toast notification data.
 *
 * Represents a single toast message shown to the user.
 *
 * @example
 * ```ts
 * const toast: Toast = {
 *   id: Date.now(),
 *   message: 'Sinkronisasi berhasil!',
 *   type: 'success'
 * };
 * ```
 */
export interface Toast {
    /** Unique identifier for the toast (for dismissal) */
    id: number;

    /** Message text to display */
    message: string;

    /** Toast type (determines color and icon) */
    type: ToastType;

    /** Optional duration in milliseconds (default: 5000) */
    duration?: number;
}

/**
 * Sync progress information.
 *
 * Tracks the current progress of a batch sync operation.
 *
 * @example
 * ```ts
 * const progress: SyncProgress = {
 *   current: 3,
 *   total: 10,
 *   currentItemType: 'trip'
 * };
 * ```
 */
export interface SyncProgress {
    /** Current item being synced (1-indexed) */
    current: number;

    /** Total number of items to sync */
    total: number;

    /** Type of item currently being synced */
    currentItemType: 'trip' | 'speed_log';

    /** Optional error message if current item failed */
    error?: string;
}

/**
 * Sync history entry.
 *
 * Records details of a completed sync operation for tracking and debugging.
 *
 * @example
 * ```ts
 * const entry: SyncHistoryEntry = {
 *   timestamp: new Date(),
 *   result: { successCount: 5, failureCount: 0, totalAttempted: 5 },
 *   trigger: 'auto',
 *   durationMs: 2340
 * };
 * ```
 */
export interface SyncHistoryEntry {
    /** When this sync operation occurred */
    timestamp: Date;

    /** Result of the sync operation */
    result: SyncResult;

    /** What triggered this sync */
    trigger: 'auto' | 'manual' | 'retry';

    /** How long the sync took in milliseconds */
    durationMs: number;

    /** Optional error message if sync failed */
    error?: string;
}

/**
 * Background sync state.
 *
 * Tracks the state of the automatic background synchronization system.
 *
 * @example
 * ```ts
 * const state: BackgroundSyncState = {
 *   isSyncing: false,
 *   lastSyncAt: new Date(),
 *   lastSyncResult: { successCount: 5, failureCount: 0, totalAttempted: 5 },
 *   retryCount: 0,
 *   nextRetryAt: null,
 *   syncHistory: []
 * };
 * ```
 */
export interface BackgroundSyncState {
    /** Whether a sync operation is currently in progress */
    isSyncing: boolean;

    /** Timestamp of the last sync attempt (successful or failed) */
    lastSyncAt: Date | null;

    /** Result of the last sync operation */
    lastSyncResult: SyncResult | null;

    /** Current retry attempt count (0-3) */
    retryCount: number;

    /** When the next retry will be attempted (if scheduled) */
    nextRetryAt: Date | null;

    /** Recent sync history (max 10 entries) */
    syncHistory: SyncHistoryEntry[];

    /** Current sync progress (if syncing) */
    currentProgress?: SyncProgress;
}

/**
 * Sync queue UI state.
 *
 * Tracks the state of the sync queue management UI, including pending items,
 * modal visibility, and loading states.
 *
 * @example
 * ```ts
 * const state: SyncQueueUIState = {
 *   pendingCount: 5,
 *   pendingItems: [],
 *   isSyncing: false,
 *   lastSyncAt: new Date(),
 *   lastSyncResult: null,
 *   isModalOpen: false
 * };
 * ```
 */
export interface SyncQueueUIState {
    /** Number of pending sync items */
    pendingCount: number;

    /** Array of pending sync queue items from IndexedDB */
    pendingItems: unknown[];

    /** Whether a sync operation is currently in progress */
    isSyncing: boolean;

    /** Timestamp of the last sync attempt */
    lastSyncAt: Date | null;

    /** Result of the last sync operation */
    lastSyncResult: SyncResult | null;

    /** Whether the sync queue modal is currently open */
    isModalOpen: boolean;
}

/**
 * Sync queue item display data.
 *
 * Transformed sync queue item optimized for UI display with
 * formatted dates, colors, and labels.
 *
 * @example
 * ```ts
 * const displayItem: SyncQueueItemDisplay = {
 *   id: 1,
 *   tripDate: '3 Apr 2026, 14:30',
 *   status: 'pending',
 *   statusColor: 'text-yellow-400',
 *   statusLabel: 'Menunggu Sync',
 *   retryCount: 0,
 *   errorMessage: null,
 *   canRetry: false
 * };
 * ```
 */
export interface SyncQueueItemDisplay {
    /** Sync queue item ID from IndexedDB */
    id: number;

    /** Formatted trip date and time for display */
    tripDate: string;

    /** Current sync status */
    status: SyncStatus;

    /** Tailwind CSS color class for status badge */
    statusColor: string;

    /** Human-readable status label in Indonesian */
    statusLabel: string;

    /** Number of retry attempts (0-3) */
    retryCount: number;

    /** Error message if sync failed, null otherwise */
    errorMessage: string | null;

    /** Whether this item can be retried manually */
    canRetry: boolean;
}

/**
 * Badge size variant.
 *
 * Determines the visual size of the sync badge component.
 */
export type BadgeSize = 'sm' | 'md' | 'lg';

/**
 * Badge status type.
 *
 * Indicates the current sync status shown by the badge.
 */
export type BadgeStatus = 'pending' | 'synced' | 'syncing' | 'error';
