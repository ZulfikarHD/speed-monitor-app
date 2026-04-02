/**
 * IndexedDB Type Definitions
 *
 * Defines TypeScript interfaces for offline data storage using IndexedDB.
 * These types support the offline-first architecture, allowing employees
 * to track trips without internet connectivity with automatic sync when online.
 *
 * Database: speedTrackerDB (version 1)
 * Object Stores: trips, speedLogs, syncQueue
 *
 * @see {@link resources/js/services/indexeddb.ts} IndexedDB Service implementation
 *
 * @example
 * ```ts
 * import type { IDBTrip, IDBSpeedLog, IDBSyncQueueItem } from '@/types/indexeddb';
 *
 * const offlineTrip: IDBTrip = {
 *   userId: 1,
 *   startedAt: new Date().toISOString(),
 *   status: 'in_progress',
 *   endedAt: null,
 *   totalDistance: null,
 *   maxSpeed: null,
 *   averageSpeed: null,
 *   violationCount: 0,
 *   durationSeconds: null,
 *   notes: null,
 *   syncedAt: null
 * };
 * ```
 */

/**
 * Trip status enum for IndexedDB storage.
 *
 * Matches TripStatus from trip.ts and backend App\Enums\TripStatus.
 */
export type IDBTripStatus = 'in_progress' | 'completed' | 'auto_stopped';

/**
 * Trip entity for IndexedDB storage.
 *
 * Matches the backend Trip model structure but optimized for local storage.
 * The `id` field is optional (auto-increment in IndexedDB) for new trips
 * created offline that don't have a backend ID yet.
 *
 * @property id - Local IndexedDB ID (auto-increment), optional for new records
 * @property userId - User who owns this trip (matches user_id in backend)
 * @property startedAt - Trip start timestamp in ISO 8601 format
 * @property endedAt - Trip end timestamp, null if trip is still in progress
 * @property status - Current trip status
 * @property totalDistance - Total distance in km, null until calculated
 * @property maxSpeed - Maximum speed in km/h, null until calculated
 * @property averageSpeed - Average speed in km/h, null until calculated
 * @property violationCount - Number of speed limit violations
 * @property durationSeconds - Trip duration in seconds, null until trip ends
 * @property notes - Optional user notes about the trip
 * @property syncedAt - Timestamp of last successful sync to backend, null if never synced
 */
export interface IDBTrip {
    /** Local IndexedDB ID (auto-increment), optional for new records */
    id?: number;

    /** User who owns this trip (matches user_id in backend) */
    userId: number;

    /** Trip start timestamp in ISO 8601 format */
    startedAt: string;

    /** Trip end timestamp, null if trip is still in progress */
    endedAt: string | null;

    /** Current trip status */
    status: IDBTripStatus;

    /** Total distance traveled in kilometers, null until calculated */
    totalDistance: number | null;

    /** Maximum speed recorded in km/h, null until calculated */
    maxSpeed: number | null;

    /** Average speed in km/h, null until calculated */
    averageSpeed: number | null;

    /** Number of speed limit violations detected */
    violationCount: number;

    /** Trip duration in seconds, null until trip ends */
    durationSeconds: number | null;

    /** Optional user notes about the trip */
    notes: string | null;

    /** Timestamp of last successful sync to backend, null if never synced */
    syncedAt: string | null;
}

/**
 * Speed log entity for IndexedDB storage.
 *
 * Represents a single speed measurement recorded during a trip.
 * Speed logs are recorded every 5 seconds during active tracking and
 * stored locally before being synced in batches to the backend.
 *
 * @property id - Local IndexedDB ID (auto-increment), optional for new records
 * @property tripId - Associated trip ID (local IndexedDB trip ID)
 * @property speed - Speed in kilometers per hour
 * @property recordedAt - Timestamp when speed was recorded (ISO 8601)
 * @property isViolation - Whether this speed exceeded the speed limit
 */
export interface IDBSpeedLog {
    /** Local IndexedDB ID (auto-increment), optional for new records */
    id?: number;

    /** Associated trip ID (local IndexedDB trip ID) */
    tripId: number;

    /** Speed in kilometers per hour */
    speed: number;

    /** Timestamp when speed was recorded (ISO 8601 format) */
    recordedAt: string;

    /** Whether this speed exceeded the configured speed limit */
    isViolation: boolean;
}

/**
 * Sync queue item type.
 *
 * Defines the type of data being synced to distinguish between
 * different sync operations and their handlers.
 */
export type IDBSyncQueueType = 'trip' | 'speedLogs';

/**
 * Sync queue item status.
 *
 * Tracks the lifecycle of a sync operation from pending to completion.
 *
 * - pending: Waiting to be synced
 * - syncing: Currently being synced to backend
 * - failed: Sync attempt failed, will be retried
 * - completed: Successfully synced to backend
 */
export type IDBSyncQueueStatus = 'pending' | 'syncing' | 'failed' | 'completed';

/**
 * Sync queue item for managing offline-to-online data synchronization.
 *
 * Tracks pending sync operations with retry logic and error handling.
 * Each item represents a trip or batch of speed logs that needs to be
 * sent to the backend API when connectivity is restored.
 *
 * @property id - Local IndexedDB ID (auto-increment), optional for new items
 * @property type - Type of data being synced (trip or speedLogs)
 * @property tripId - Local trip ID this sync operation relates to
 * @property data - Serialized data to sync (trip object or speed logs array)
 * @property status - Current status of the sync operation
 * @property retryCount - Number of sync attempts made (max 3)
 * @property lastAttemptAt - Timestamp of last sync attempt, null if never attempted
 * @property errorMessage - Error message from last failed attempt, null if no error
 * @property createdAt - Timestamp when item was added to sync queue (ISO 8601)
 */
export interface IDBSyncQueueItem {
    /** Local IndexedDB ID (auto-increment), optional for new items */
    id?: number;

    /** Type of data being synced (trip or speedLogs) */
    type: IDBSyncQueueType;

    /** Local trip ID this sync operation relates to */
    tripId: number;

    /**
     * Serialized data to sync to backend.
     * - For 'trip' type: IDBTrip object
     * - For 'speedLogs' type: IDBSpeedLog[] array
     */
    data: unknown;

    /** Current status of the sync operation */
    status: IDBSyncQueueStatus;

    /** Number of sync attempts made (max 3 before marking as permanently failed) */
    retryCount: number;

    /** Timestamp of last sync attempt (ISO 8601), null if never attempted */
    lastAttemptAt: string | null;

    /** Error message from last failed attempt, null if no error or not attempted */
    errorMessage: string | null;

    /** Timestamp when item was added to sync queue (ISO 8601 format) */
    createdAt: string;
}

/**
 * IndexedDB database configuration.
 *
 * Constants for database name, version, and object store names.
 */
export const IDB_CONFIG = {
    /** Database name */
    DB_NAME: 'speedTrackerDB',

    /** Database version (increment when schema changes) */
    DB_VERSION: 1,

    /** Object store names */
    STORES: {
        TRIPS: 'trips',
        SPEED_LOGS: 'speedLogs',
        SYNC_QUEUE: 'syncQueue'
    }
} as const;

/**
 * IndexedDB error types for specific error handling.
 */
export enum IDBErrorType {
    /** Database connection failed */
    CONNECTION_FAILED = 'CONNECTION_FAILED',

    /** Transaction failed or was aborted */
    TRANSACTION_FAILED = 'TRANSACTION_FAILED',

    /** Storage quota exceeded */
    QUOTA_EXCEEDED = 'QUOTA_EXCEEDED',

    /** Browser doesn't support IndexedDB */
    NOT_SUPPORTED = 'NOT_SUPPORTED',

    /** Record not found */
    NOT_FOUND = 'NOT_FOUND',

    /** Unknown error */
    UNKNOWN = 'UNKNOWN'
}

/**
 * IndexedDB error class with type information.
 */
export class IDBError extends Error {
    constructor(
        public type: IDBErrorType,
        message: string,
        public originalError?: Error
    ) {
        super(message);
        this.name = 'IDBError';
    }
}

/**
 * Storage quota information from navigator.storage.estimate().
 */
export interface StorageQuota {
    /** Total storage quota in bytes */
    quota: number;

    /** Used storage in bytes */
    usage: number;

    /** Percentage of quota used (0-100) */
    percentUsed: number;

    /** Remaining storage in bytes */
    remaining: number;
}

/**
 * IndexedDB service statistics.
 *
 * Provides insights into IndexedDB storage usage and performance.
 */
export interface IDBStatistics {
    /** Number of trips stored locally */
    tripCount: number;

    /** Number of speed logs stored locally */
    speedLogCount: number;

    /** Number of pending sync items */
    syncQueueCount: number;

    /** Storage quota information */
    quota: StorageQuota;
}
