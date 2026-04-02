/**
 * IndexedDB Service for Offline Data Persistence
 *
 * Provides a Promise-based API for storing and retrieving trip data locally
 * when the application is offline. This service enables the offline-first
 * architecture, allowing employees to track trips without internet connectivity
 * with automatic sync when online.
 *
 * Database: speedTrackerDB (version 1)
 * Object Stores: trips, speedLogs, syncQueue
 *
 * Features:
 * - Singleton pattern for connection reuse
 * - TypeScript type safety throughout
 * - Comprehensive error handling
 * - Transaction management
 * - Bulk operations for performance
 * - Automatic database upgrades
 *
 * @example
 * ```ts
 * import { indexedDBService } from '@/services/indexeddb';
 *
 * // Start a trip offline
 * const tripId = await indexedDBService.addTrip({
 *   userId: 1,
 *   startedAt: new Date().toISOString(),
 *   status: 'in_progress',
 *   // ... other fields
 * });
 *
 * // Add speed logs
 * await indexedDBService.addSpeedLogsBulk([
 *   { tripId, speed: 45, recordedAt: '2026-04-03T10:00:00Z', isViolation: false },
 *   { tripId, speed: 52, recordedAt: '2026-04-03T10:00:05Z', isViolation: false }
 * ]);
 *
 * // Queue for sync
 * await indexedDBService.addToSyncQueue({
 *   type: 'trip',
 *   tripId,
 *   data: tripData,
 *   status: 'pending',
 *   retryCount: 0,
 *   lastAttemptAt: null,
 *   errorMessage: null,
 *   createdAt: new Date().toISOString()
 * });
 * ```
 */

import type {
    IDBTrip,
    IDBSpeedLog,
    IDBSyncQueueItem,
    IDBTripStatus,
    IDBStatistics,
    StorageQuota
} from '@/types/indexeddb';
import { IDB_CONFIG, IDBError, IDBErrorType } from '@/types/indexeddb';

/**
 * IndexedDB Service Class
 *
 * Singleton service for managing offline data storage using IndexedDB.
 * Handles database initialization, CRUD operations, and storage management.
 */
class IndexedDBService {
    /** Singleton database connection instance */
    private db: IDBDatabase | null = null;

    /** Database name constant */
    private readonly DB_NAME = IDB_CONFIG.DB_NAME;

    /** Database version (increment when schema changes) */
    private readonly DB_VERSION = IDB_CONFIG.DB_VERSION;

    /** Object store names */
    private readonly STORES = IDB_CONFIG.STORES;

    /**
     * Open IndexedDB database connection.
     *
     * Uses singleton pattern to reuse existing connection. Creates database
     * schema with object stores and indexes on first run or version upgrade.
     *
     * @returns Promise resolving to IDBDatabase instance
     * @throws {IDBError} If database connection fails or not supported
     *
     * @example
     * ```ts
     * const db = await indexedDBService.openDatabase();
     * ```
     */
    async openDatabase(): Promise<IDBDatabase> {
        // Return existing connection if available
        if (this.db) {
            return this.db;
        }

        // Check browser support
        if (!window.indexedDB) {
            throw new IDBError(
                IDBErrorType.NOT_SUPPORTED,
                'Browser tidak mendukung penyimpanan offline (IndexedDB tidak tersedia)'
            );
        }

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onerror = () => {
                const error = new IDBError(
                    IDBErrorType.CONNECTION_FAILED,
                    'Gagal membuka database offline',
                    request.error || undefined
                );
                reject(error);
            };

            request.onsuccess = () => {
                this.db = request.result;

                // Handle unexpected close
                this.db.onversionchange = () => {
                    this.db?.close();
                    this.db = null;
                };

                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;

                // Create trips object store
                if (!db.objectStoreNames.contains(this.STORES.TRIPS)) {
                    const tripsStore = db.createObjectStore(this.STORES.TRIPS, {
                        keyPath: 'id',
                        autoIncrement: true
                    });

                    // Create indexes for efficient querying
                    tripsStore.createIndex('userId', 'userId', { unique: false });
                    tripsStore.createIndex('status', 'status', { unique: false });
                    tripsStore.createIndex('startedAt', 'startedAt', { unique: false });
                    tripsStore.createIndex('syncedAt', 'syncedAt', { unique: false });
                }

                // Create speedLogs object store
                if (!db.objectStoreNames.contains(this.STORES.SPEED_LOGS)) {
                    const speedLogsStore = db.createObjectStore(this.STORES.SPEED_LOGS, {
                        keyPath: 'id',
                        autoIncrement: true
                    });

                    // Create indexes
                    speedLogsStore.createIndex('tripId', 'tripId', { unique: false });
                    speedLogsStore.createIndex('recordedAt', 'recordedAt', { unique: false });
                    speedLogsStore.createIndex('isViolation', 'isViolation', { unique: false });
                }

                // Create syncQueue object store
                if (!db.objectStoreNames.contains(this.STORES.SYNC_QUEUE)) {
                    const syncQueueStore = db.createObjectStore(this.STORES.SYNC_QUEUE, {
                        keyPath: 'id',
                        autoIncrement: true
                    });

                    // Create indexes
                    syncQueueStore.createIndex('status', 'status', { unique: false });
                    syncQueueStore.createIndex('type', 'type', { unique: false });
                    syncQueueStore.createIndex('tripId', 'tripId', { unique: false });
                    syncQueueStore.createIndex('createdAt', 'createdAt', { unique: false });
                }
            };
        });
    }

    /**
     * Close database connection.
     *
     * Cleanly closes the database connection. Should be called when
     * application is shutting down or before deleting the database.
     *
     * @example
     * ```ts
     * await indexedDBService.closeDatabase();
     * ```
     */
    async closeDatabase(): Promise<void> {
        if (this.db) {
            this.db.close();
            this.db = null;
        }
    }

    /**
     * Delete entire database.
     *
     * WARNING: This permanently deletes all offline data.
     * Use only for testing or admin cleanup operations.
     *
     * @returns Promise resolving when database is deleted
     * @throws {IDBError} If deletion fails
     *
     * @example
     * ```ts
     * await indexedDBService.deleteDatabase();
     * ```
     */
    async deleteDatabase(): Promise<void> {
        await this.closeDatabase();

        return new Promise((resolve, reject) => {
            const request = indexedDB.deleteDatabase(this.DB_NAME);

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.UNKNOWN,
                    'Gagal menghapus database offline',
                    request.error || undefined
                ));
            };

            request.onsuccess = () => {
                resolve();
            };
        });
    }

    // ========================================================================
    // TRIPS CRUD OPERATIONS
    // ========================================================================

    /**
     * Add a new trip to IndexedDB.
     *
     * @param trip - Trip data (id will be auto-generated)
     * @returns Promise resolving to the new trip's ID
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const tripId = await indexedDBService.addTrip({
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
     * });
     * ```
     */
    async addTrip(trip: Omit<IDBTrip, 'id'>): Promise<number> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.TRIPS], 'readwrite');
            const store = transaction.objectStore(this.STORES.TRIPS);
            const request = store.add(trip);

            request.onsuccess = () => {
                resolve(request.result as number);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menyimpan perjalanan ke penyimpanan lokal',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Get a trip by its ID.
     *
     * @param id - Trip ID
     * @returns Promise resolving to trip or null if not found
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const trip = await indexedDBService.getTrip(1);
     * if (trip) {
     *   console.log('Found trip:', trip);
     * }
     * ```
     */
    async getTrip(id: number): Promise<IDBTrip | null> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.TRIPS], 'readonly');
            const store = transaction.objectStore(this.STORES.TRIPS);
            const request = store.get(id);

            request.onsuccess = () => {
                resolve(request.result || null);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil perjalanan dari penyimpanan lokal',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Get all trips from IndexedDB.
     *
     * @returns Promise resolving to array of all trips
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const allTrips = await indexedDBService.getAllTrips();
     * console.log(`Found ${allTrips.length} trips`);
     * ```
     */
    async getAllTrips(): Promise<IDBTrip[]> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.TRIPS], 'readonly');
            const store = transaction.objectStore(this.STORES.TRIPS);
            const request = store.getAll();

            request.onsuccess = () => {
                resolve(request.result);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil daftar perjalanan dari penyimpanan lokal',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Get trips filtered by status.
     *
     * @param status - Trip status to filter by
     * @returns Promise resolving to array of trips with matching status
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const activeTrips = await indexedDBService.getTripsByStatus('in_progress');
     * console.log(`Found ${activeTrips.length} active trips`);
     * ```
     */
    async getTripsByStatus(status: IDBTripStatus): Promise<IDBTrip[]> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.TRIPS], 'readonly');
            const store = transaction.objectStore(this.STORES.TRIPS);
            const index = store.index('status');
            const request = index.getAll(status);

            request.onsuccess = () => {
                resolve(request.result);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil perjalanan berdasarkan status',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Update an existing trip.
     *
     * @param id - Trip ID to update
     * @param updates - Partial trip data to update
     * @returns Promise resolving when update completes
     * @throws {IDBError} If operation fails or trip not found
     *
     * @example
     * ```ts
     * await indexedDBService.updateTrip(1, {
     *   status: 'completed',
     *   endedAt: new Date().toISOString(),
     *   durationSeconds: 1800
     * });
     * ```
     */
    async updateTrip(id: number, updates: Partial<IDBTrip>): Promise<void> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.TRIPS], 'readwrite');
            const store = transaction.objectStore(this.STORES.TRIPS);
            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const trip = getRequest.result;

                if (!trip) {
                    reject(new IDBError(
                        IDBErrorType.NOT_FOUND,
                        `Perjalanan dengan ID ${id} tidak ditemukan`
                    ));

                    return;
                }

                // Merge updates with existing trip
                const updatedTrip = { ...trip, ...updates, id };
                const putRequest = store.put(updatedTrip);

                putRequest.onsuccess = () => {
                    resolve();
                };

                putRequest.onerror = () => {
                    reject(new IDBError(
                        IDBErrorType.TRANSACTION_FAILED,
                        'Gagal memperbarui perjalanan',
                        putRequest.error || undefined
                    ));
                };
            };

            getRequest.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil perjalanan untuk diperbarui',
                    getRequest.error || undefined
                ));
            };
        });
    }

    /**
     * Delete a trip from IndexedDB.
     *
     * WARNING: This also deletes all associated speed logs.
     *
     * @param id - Trip ID to delete
     * @returns Promise resolving when deletion completes
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * await indexedDBService.deleteTrip(1);
     * ```
     */
    async deleteTrip(id: number): Promise<void> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction(
                [this.STORES.TRIPS, this.STORES.SPEED_LOGS],
                'readwrite'
            );

            // Delete the trip
            const tripsStore = transaction.objectStore(this.STORES.TRIPS);

            tripsStore.delete(id);

            // Delete associated speed logs
            const speedLogsStore = transaction.objectStore(this.STORES.SPEED_LOGS);
            const index = speedLogsStore.index('tripId');
            const getLogsRequest = index.getAllKeys(id);

            getLogsRequest.onsuccess = () => {
                const keys = getLogsRequest.result;
                keys.forEach((key) => {
                    speedLogsStore.delete(key);
                });
            };

            transaction.oncomplete = () => {
                resolve();
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menghapus perjalanan',
                    transaction.error || undefined
                ));
            };
        });
    }

    // ========================================================================
    // SPEED LOGS CRUD OPERATIONS
    // ========================================================================

    /**
     * Add a single speed log to IndexedDB.
     *
     * For better performance, use addSpeedLogsBulk() for multiple logs.
     *
     * @param log - Speed log data (id will be auto-generated)
     * @returns Promise resolving to the new speed log's ID
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const logId = await indexedDBService.addSpeedLog({
     *   tripId: 1,
     *   speed: 45,
     *   recordedAt: new Date().toISOString(),
     *   isViolation: false
     * });
     * ```
     */
    async addSpeedLog(log: Omit<IDBSpeedLog, 'id'>): Promise<number> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SPEED_LOGS], 'readwrite');
            const store = transaction.objectStore(this.STORES.SPEED_LOGS);
            const request = store.add(log);

            request.onsuccess = () => {
                resolve(request.result as number);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menyimpan log kecepatan',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Add multiple speed logs in a single transaction (BULK INSERT).
     *
     * Performance-optimized for inserting 50-100+ speed logs at once.
     * Uses a single transaction to minimize overhead.
     *
     * @param logs - Array of speed log data
     * @returns Promise resolving when all logs are inserted
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * await indexedDBService.addSpeedLogsBulk([
     *   { tripId: 1, speed: 45, recordedAt: '2026-04-03T10:00:00Z', isViolation: false },
     *   { tripId: 1, speed: 52, recordedAt: '2026-04-03T10:00:05Z', isViolation: false },
     *   { tripId: 1, speed: 58, recordedAt: '2026-04-03T10:00:10Z', isViolation: false }
     * ]);
     * ```
     */
    async addSpeedLogsBulk(logs: Array<Omit<IDBSpeedLog, 'id'>>): Promise<void> {
        if (logs.length === 0) {
            return;
        }

        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SPEED_LOGS], 'readwrite');
            const store = transaction.objectStore(this.STORES.SPEED_LOGS);

            // Add all logs in a single transaction
            logs.forEach((log) => {
                store.add(log);
            });

            transaction.oncomplete = () => {
                resolve();
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    `Gagal menyimpan ${logs.length} log kecepatan`,
                    transaction.error || undefined
                ));
            };
        });
    }

    /**
     * Get all speed logs for a specific trip.
     *
     * @param tripId - Trip ID to get logs for
     * @returns Promise resolving to array of speed logs
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const logs = await indexedDBService.getSpeedLogsByTripId(1);
     * console.log(`Found ${logs.length} speed logs for trip`);
     * ```
     */
    async getSpeedLogsByTripId(tripId: number): Promise<IDBSpeedLog[]> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SPEED_LOGS], 'readonly');
            const store = transaction.objectStore(this.STORES.SPEED_LOGS);
            const index = store.index('tripId');
            const request = index.getAll(tripId);

            request.onsuccess = () => {
                resolve(request.result);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil log kecepatan',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Delete all speed logs for a specific trip.
     *
     * @param tripId - Trip ID to delete logs for
     * @returns Promise resolving when deletion completes
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * await indexedDBService.deleteSpeedLogsByTripId(1);
     * ```
     */
    async deleteSpeedLogsByTripId(tripId: number): Promise<void> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SPEED_LOGS], 'readwrite');
            const store = transaction.objectStore(this.STORES.SPEED_LOGS);
            const index = store.index('tripId');
            const request = index.getAllKeys(tripId);

            request.onsuccess = () => {
                const keys = request.result;
                keys.forEach((key) => {
                    store.delete(key);
                });
            };

            transaction.oncomplete = () => {
                resolve();
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menghapus log kecepatan',
                    transaction.error || undefined
                ));
            };
        });
    }

    // ========================================================================
    // SYNC QUEUE CRUD OPERATIONS
    // ========================================================================

    /**
     * Add an item to the sync queue.
     *
     * @param item - Sync queue item data (id will be auto-generated)
     * @returns Promise resolving to the new sync queue item's ID
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const queueId = await indexedDBService.addToSyncQueue({
     *   type: 'trip',
     *   tripId: 1,
     *   data: tripData,
     *   status: 'pending',
     *   retryCount: 0,
     *   lastAttemptAt: null,
     *   errorMessage: null,
     *   createdAt: new Date().toISOString()
     * });
     * ```
     */
    async addToSyncQueue(item: Omit<IDBSyncQueueItem, 'id'>): Promise<number> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SYNC_QUEUE], 'readwrite');
            const store = transaction.objectStore(this.STORES.SYNC_QUEUE);
            const request = store.add(item);

            request.onsuccess = () => {
                resolve(request.result as number);
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menambahkan item ke antrian sinkronisasi',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Get all pending sync queue items.
     *
     * Returns items with status 'pending' or 'failed' (for retry).
     *
     * @returns Promise resolving to array of pending sync items
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const pending = await indexedDBService.getPendingSyncItems();
     * console.log(`${pending.length} items waiting to sync`);
     * ```
     */
    async getPendingSyncItems(): Promise<IDBSyncQueueItem[]> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SYNC_QUEUE], 'readonly');
            const store = transaction.objectStore(this.STORES.SYNC_QUEUE);
            const index = store.index('status');

            const pendingRequest = index.getAll('pending');
            const failedRequest = index.getAll('failed');

            const results: IDBSyncQueueItem[] = [];


            pendingRequest.onsuccess = () => {
                results.push(...pendingRequest.result);
            };

            failedRequest.onsuccess = () => {
                results.push(...failedRequest.result);
            };

            transaction.oncomplete = () => {
                // Sort by createdAt (oldest first)
                results.sort((a, b) =>
                    new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime()
                );
                resolve(results);
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil antrian sinkronisasi',
                    transaction.error || undefined
                ));
            };
        });
    }

    /**
     * Update a sync queue item.
     *
     * @param id - Sync queue item ID to update
     * @param updates - Partial sync queue item data to update
     * @returns Promise resolving when update completes
     * @throws {IDBError} If operation fails or item not found
     *
     * @example
     * ```ts
     * await indexedDBService.updateSyncQueueItem(1, {
     *   status: 'syncing',
     *   lastAttemptAt: new Date().toISOString()
     * });
     * ```
     */
    async updateSyncQueueItem(id: number, updates: Partial<IDBSyncQueueItem>): Promise<void> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SYNC_QUEUE], 'readwrite');
            const store = transaction.objectStore(this.STORES.SYNC_QUEUE);
            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const item = getRequest.result;

                if (!item) {
                    reject(new IDBError(
                        IDBErrorType.NOT_FOUND,
                        `Item antrian sinkronisasi dengan ID ${id} tidak ditemukan`
                    ));

                    return;
                }

                // Merge updates with existing item
                const updatedItem = { ...item, ...updates, id };
                const putRequest = store.put(updatedItem);

                putRequest.onsuccess = () => {
                    resolve();
                };

                putRequest.onerror = () => {
                    reject(new IDBError(
                        IDBErrorType.TRANSACTION_FAILED,
                        'Gagal memperbarui item antrian sinkronisasi',
                        putRequest.error || undefined
                    ));
                };
            };

            getRequest.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil item antrian sinkronisasi',
                    getRequest.error || undefined
                ));
            };
        });
    }

    /**
     * Delete a sync queue item.
     *
     * @param id - Sync queue item ID to delete
     * @returns Promise resolving when deletion completes
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * await indexedDBService.deleteSyncQueueItem(1);
     * ```
     */
    async deleteSyncQueueItem(id: number): Promise<void> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SYNC_QUEUE], 'readwrite');
            const store = transaction.objectStore(this.STORES.SYNC_QUEUE);
            const request = store.delete(id);

            request.onsuccess = () => {
                resolve();
            };

            request.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal menghapus item antrian sinkronisasi',
                    request.error || undefined
                ));
            };
        });
    }

    /**
     * Clear all completed sync queue items.
     *
     * Removes items with status 'completed' to free up storage space.
     * Should be called periodically to prevent queue buildup.
     *
     * @returns Promise resolving to number of items deleted
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const deleted = await indexedDBService.clearCompletedSyncItems();
     * console.log(`Cleared ${deleted} completed sync items`);
     * ```
     */
    async clearCompletedSyncItems(): Promise<number> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.STORES.SYNC_QUEUE], 'readwrite');
            const store = transaction.objectStore(this.STORES.SYNC_QUEUE);
            const index = store.index('status');
            const request = index.getAllKeys('completed');

            let deletedCount = 0;

            request.onsuccess = () => {
                const keys = request.result;
                deletedCount = keys.length;

                keys.forEach((key) => {
                    store.delete(key);
                });
            };

            transaction.oncomplete = () => {
                resolve(deletedCount);
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal membersihkan item sinkronisasi yang selesai',
                    transaction.error || undefined
                ));
            };
        });
    }

    // ========================================================================
    // UTILITY OPERATIONS
    // ========================================================================

    /**
     * Get storage quota information.
     *
     * Uses navigator.storage.estimate() to check available storage.
     *
     * @returns Promise resolving to storage quota information
     * @throws {IDBError} If operation fails or not supported
     *
     * @example
     * ```ts
     * const quota = await indexedDBService.getStorageQuota();
     * console.log(`Using ${quota.percentUsed}% of storage`);
     * if (quota.percentUsed > 80) {
     *   console.warn('Storage almost full!');
     * }
     * ```
     */
    async getStorageQuota(): Promise<StorageQuota> {
        if (!navigator.storage || !navigator.storage.estimate) {
            throw new IDBError(
                IDBErrorType.NOT_SUPPORTED,
                'Browser tidak mendukung pemeriksaan kuota penyimpanan'
            );
        }

        const estimate = await navigator.storage.estimate();
        const quota = estimate.quota || 0;
        const usage = estimate.usage || 0;
        const remaining = quota - usage;
        const percentUsed = quota > 0 ? (usage / quota) * 100 : 0;

        return {
            quota,
            usage,
            remaining,
            percentUsed
        };
    }

    /**
     * Get IndexedDB statistics.
     *
     * Provides insights into storage usage and record counts.
     *
     * @returns Promise resolving to database statistics
     * @throws {IDBError} If operation fails
     *
     * @example
     * ```ts
     * const stats = await indexedDBService.getStatistics();
     * console.log(`Trips: ${stats.tripCount}`);
     * console.log(`Speed Logs: ${stats.speedLogCount}`);
     * console.log(`Pending Sync: ${stats.syncQueueCount}`);
     * console.log(`Storage: ${stats.quota.percentUsed.toFixed(1)}%`);
     * ```
     */
    async getStatistics(): Promise<IDBStatistics> {
        const db = await this.openDatabase();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction(
                [this.STORES.TRIPS, this.STORES.SPEED_LOGS, this.STORES.SYNC_QUEUE],
                'readonly'
            );

            const tripsStore = transaction.objectStore(this.STORES.TRIPS);
            const speedLogsStore = transaction.objectStore(this.STORES.SPEED_LOGS);
            const syncQueueStore = transaction.objectStore(this.STORES.SYNC_QUEUE);

            const tripCountRequest = tripsStore.count();
            const speedLogCountRequest = speedLogsStore.count();
            const syncQueueCountRequest = syncQueueStore.count();

            let tripCount = 0;
            let speedLogCount = 0;
            let syncQueueCount = 0;

            tripCountRequest.onsuccess = () => {
                tripCount = tripCountRequest.result;
            };

            speedLogCountRequest.onsuccess = () => {
                speedLogCount = speedLogCountRequest.result;
            };

            syncQueueCountRequest.onsuccess = () => {
                syncQueueCount = syncQueueCountRequest.result;
            };

            transaction.oncomplete = async () => {
                try {
                    const quota = await this.getStorageQuota();

                    resolve({
                        tripCount,
                        speedLogCount,
                        syncQueueCount,
                        quota
                    });
                } catch {
                    // If quota check fails, return stats without quota info
                    resolve({
                        tripCount,
                        speedLogCount,
                        syncQueueCount,
                        quota: {
                            quota: 0,
                            usage: 0,
                            remaining: 0,
                            percentUsed: 0
                        }
                    });
                }
            };

            transaction.onerror = () => {
                reject(new IDBError(
                    IDBErrorType.TRANSACTION_FAILED,
                    'Gagal mengambil statistik database',
                    transaction.error || undefined
                ));
            };
        });
    }
}

/**
 * Singleton instance of IndexedDBService.
 *
 * Import this instance throughout the application for consistent
 * database access and connection reuse.
 *
 * @example
 * ```ts
 * import { indexedDBService } from '@/services/indexeddb';
 *
 * // Use in components, stores, or composables
 * const trip = await indexedDBService.getTrip(1);
 * ```
 */
export const indexedDBService = new IndexedDBService();
