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
