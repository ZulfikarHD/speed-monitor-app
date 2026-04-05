/**
 * Service Worker Manager
 *
 * Manages Service Worker registration, updates, and lifecycle events.
 * Provides event-based communication for update notifications and error handling.
 *
 * Features:
 * - Automatic registration on app load
 * - Update detection and notification
 * - Manual update triggering (skipWaiting)
 * - Manual update checking
 * - Event-based communication with UI components
 *
 * Event Types:
 * - 'sw:registered' - Initial SW registration complete
 * - 'sw:update-available' - New SW version detected (waiting to activate)
 * - 'sw:update-applied' - New SW activated (reload needed)
 * - 'sw:error' - Registration or update error
 *
 * @example
 * ```typescript
 * import { serviceWorkerManager } from '@/services/serviceWorkerManager'
 *
 * // Register SW on app load
 * serviceWorkerManager.register()
 *
 * // Listen for updates
 * window.addEventListener('sw:update-available', (e) => {
 *   console.log('Update available:', e.detail)
 * })
 *
 * // Apply update
 * await serviceWorkerManager.applyUpdate()
 * ```
 */

/**
 * Service Worker registration state.
 */
export interface ServiceWorkerState {
    /** Whether Service Workers are supported in this browser */
    isSupported: boolean;

    /** Whether a Service Worker is currently registered */
    isRegistered: boolean;

    /** Service Worker registration object (null if not registered) */
    registration: ServiceWorkerRegistration | null;

    /** Whether a new version is waiting to activate */
    hasUpdate: boolean;

    /** Whether an update is currently being applied */
    isUpdating: boolean;
}

/**
 * Service Worker update event detail.
 */
export interface ServiceWorkerUpdateEvent {
    /** Service Worker registration object */
    registration: ServiceWorkerRegistration;

    /** Waiting Service Worker (new version) */
    waiting: ServiceWorker;
}

/**
 * Service Worker Manager class.
 * Singleton instance managing SW registration and updates.
 */
class ServiceWorkerManager {
    private state: ServiceWorkerState = {
        isSupported: false,
        isRegistered: false,
        registration: null,
        hasUpdate: false,
        isUpdating: false,
    };

    /**
     * Initialize Service Worker Manager.
     * Checks browser support on instantiation.
     */
    constructor() {
        this.state.isSupported = 'serviceWorker' in navigator;
    }

    /**
     * Get current Service Worker state.
     *
     * @returns Current state
     */
    getState(): Readonly<ServiceWorkerState> {
        return { ...this.state };
    }

    /**
     * Register Service Worker.
     * Should be called once on app load (deferred to window.load event).
     *
     * @returns Promise resolving to registration or null if not supported
     */
    async register(): Promise<ServiceWorkerRegistration | null> {
        if (!this.state.isSupported) {
            console.warn('[SW Manager] Service Workers not supported');

            return null;
        }

        try {
            // Register SW at root scope
            const registration = await navigator.serviceWorker.register(
                '/service-worker.js',
                { scope: '/' }
            );

            this.state.isRegistered = true;
            this.state.registration = registration;

            // Set up update listeners
            this.setupUpdateListeners(registration);

            // Check for updates immediately (browser checks every 24h by default)
            registration.update().catch(console.error);

            // Emit registration event
            this.emitEvent('sw:registered', { registration });

            // Listen for controller change (new SW activated)
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                this.emitEvent('sw:update-applied', { registration });
            });

            return registration;
        } catch (error) {
            console.error('[SW Manager] Registration failed:', error);
            this.emitEvent('sw:error', {
                error,
                message: 'Service Worker registration failed',
            });

            return null;
        }
    }

    /**
     * Set up update detection listeners.
     * Monitors for new Service Worker versions.
     *
     * @param registration - Service Worker registration
     */
    private setupUpdateListeners(
        registration: ServiceWorkerRegistration
    ): void {
        // Listen for new SW found
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;

            if (!newWorker) {
                return;
            }

            // Listen for state changes on new SW
            newWorker.addEventListener('statechange', () => {
                // New SW installed and waiting to activate
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {

                    this.state.hasUpdate = true;

                    // Emit update available event
                    this.emitEvent('sw:update-available', {
                        registration,
                        waiting: newWorker,
                    });
                }
            });
        });

        // Check if there's already a waiting SW
        if (registration.waiting) {
            this.state.hasUpdate = true;

            this.emitEvent('sw:update-available', {
                registration,
                waiting: registration.waiting,
            });
        }
    }

    /**
     * Apply pending Service Worker update.
     * Sends SKIP_WAITING message to new SW to activate immediately.
     *
     * @returns Promise resolving when update is triggered
     */
    async applyUpdate(): Promise<void> {
        if (!this.state.registration || !this.state.registration.waiting) {
            console.warn('[SW Manager] No update available to apply');

            return;
        }

        try {
            this.state.isUpdating = true;

            // Send SKIP_WAITING message to new SW
            this.state.registration.waiting.postMessage({
                type: 'SKIP_WAITING',
            });

            // Update will trigger controllerchange event -> page reload
        } catch (error) {
            console.error('[SW Manager] Failed to apply update:', error);
            this.state.isUpdating = false;

            throw error;
        }
    }

    /**
     * Manually check for Service Worker updates.
     * Useful for "Check for updates" button in settings.
     *
     * @returns Promise resolving to true if update found, false otherwise
     */
    async checkForUpdates(): Promise<boolean> {
        if (!this.state.registration) {
            console.warn('[SW Manager] No registration to check for updates');

            return false;
        }

        try {
            await this.state.registration.update();

            // Update detection happens via updatefound event
            // Return current update state
            return this.state.hasUpdate;
        } catch (error) {
            console.error('[SW Manager] Update check failed:', error);
            this.emitEvent('sw:error', {
                error,
                message: 'Failed to check for updates',
            });

            return false;
        }
    }

    /**
     * Unregister Service Worker.
     * Used for cleanup or emergency rollback.
     *
     * @returns Promise resolving to true if unregistered successfully
     */
    async unregister(): Promise<boolean> {
        if (!this.state.registration) {
            console.warn('[SW Manager] No registration to unregister');

            return false;
        }

        try {
            const result = await this.state.registration.unregister();

            if (result) {
                this.state.isRegistered = false;
                this.state.registration = null;
                this.state.hasUpdate = false;
            }

            return result;
        } catch (error) {
            console.error('[SW Manager] Unregister failed:', error);

            return false;
        }
    }

    /**
     * Emit custom event on window.
     * Used for communication with UI components.
     *
     * @param eventName - Event name (e.g., 'sw:update-available')
     * @param detail - Event detail object
     */
    private emitEvent(eventName: string, detail: unknown): void {
        const event = new CustomEvent(eventName, { detail });
        window.dispatchEvent(event);
    }
}

// ==============================================================================
// SINGLETON EXPORT
// ==============================================================================

/**
 * Singleton Service Worker Manager instance.
 * Import and use this instance throughout the application.
 */
export const serviceWorkerManager = new ServiceWorkerManager();

/**
 * Default export for convenience.
 */
export default serviceWorkerManager;
