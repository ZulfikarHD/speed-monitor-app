/**
 * Service Worker Composable
 *
 * Vue 3 composable providing reactive Service Worker state and actions.
 * Integrates with serviceWorkerManager for registration and update management.
 *
 * Features:
 * - Reactive SW state (isSupported, isRegistered, hasUpdate, isUpdating)
 * - Update checking and application
 * - Event listener lifecycle management
 * - Last update check timestamp tracking
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useServiceWorker } from '@/composables/useServiceWorker'
 *
 * const {
 *   isSupported,
 *   hasUpdate,
 *   applyUpdate,
 *   checkForUpdates
 * } = useServiceWorker()
 * </script>
 *
 * <template>
 *   <div v-if="hasUpdate">
 *     <p>Update available!</p>
 *     <button @click="applyUpdate">Update Now</button>
 *   </div>
 * </template>
 * ```
 */

import { onMounted, onUnmounted, ref } from 'vue';

import { serviceWorkerManager } from '@/services/serviceWorkerManager';
import type { ServiceWorkerState } from '@/services/serviceWorkerManager';

/**
 * Service Worker composable return type.
 */
export interface UseServiceWorkerReturn {
    /** Whether Service Workers are supported in this browser */
    isSupported: Readonly<typeof isSupported>;

    /** Whether a Service Worker is currently registered */
    isRegistered: Readonly<typeof isRegistered>;

    /** Whether a new version is waiting to activate */
    hasUpdate: Readonly<typeof hasUpdate>;

    /** Whether an update is currently being applied */
    isUpdating: Readonly<typeof isUpdating>;

    /** Last time updates were checked (null if never checked) */
    lastUpdateCheck: Readonly<typeof lastUpdateCheck>;

    /** Check for Service Worker updates manually */
    checkForUpdates: () => Promise<boolean>;

    /** Apply pending Service Worker update */
    applyUpdate: () => Promise<void>;
}

/**
 * Service Worker composable.
 * Provides reactive state and actions for Service Worker management.
 *
 * @returns Service Worker state and actions
 */
export function useServiceWorker(): UseServiceWorkerReturn {
    // ==============================================================================
    // Reactive State
    // ==============================================================================

    /**
     * Whether Service Workers are supported in this browser.
     */
    const isSupported = ref<boolean>(false);

    /**
     * Whether a Service Worker is currently registered.
     */
    const isRegistered = ref<boolean>(false);

    /**
     * Whether a new version is waiting to activate.
     */
    const hasUpdate = ref<boolean>(false);

    /**
     * Whether an update is currently being applied.
     */
    const isUpdating = ref<boolean>(false);

    /**
     * Last time updates were checked.
     */
    const lastUpdateCheck = ref<Date | null>(null);

    // ==============================================================================
    // Event Handlers
    // ==============================================================================

    /**
     * Handle SW registration event.
     * Updates reactive state when SW is registered.
     */
    const handleRegistered = (): void => {
        isRegistered.value = true;
    };

    /**
     * Handle SW update available event.
     * Updates reactive state when new SW version detected.
     */
    const handleUpdateAvailable = (): void => {
        hasUpdate.value = true;
    };

    /**
     * Handle SW update applied event.
     * Reloads page when new SW is activated.
     */
    const handleUpdateApplied = (): void => {
        // Reload page to use new service worker
        window.location.reload();
    };

    /**
     * Handle SW error event.
     * Logs errors and updates state.
     *
     * @param event - Custom event with error details
     */
    const handleError = (event: Event): void => {
        const customEvent = event as CustomEvent;
        console.error('[useServiceWorker] SW error:', customEvent.detail);

        isUpdating.value = false;
    };

    // ==============================================================================
    // Actions
    // ==============================================================================

    /**
     * Check for Service Worker updates manually.
     * Useful for "Check for updates" button in settings.
     *
     * @returns Promise resolving to true if update found, false otherwise
     */
    const checkForUpdates = async (): Promise<boolean> => {
        if (!isSupported.value || !isRegistered.value) {
            console.warn('[useServiceWorker] Cannot check for updates: SW not supported or registered');

            return false;
        }

        try {
            lastUpdateCheck.value = new Date();
            const updateFound = await serviceWorkerManager.checkForUpdates();

            return updateFound;
        } catch (error) {
            console.error('[useServiceWorker] Update check failed:', error);

            return false;
        }
    };

    /**
     * Apply pending Service Worker update.
     * Triggers SKIP_WAITING message to activate new SW immediately.
     *
     * @returns Promise resolving when update is applied
     */
    const applyUpdate = async (): Promise<void> => {
        if (!hasUpdate.value) {
            console.warn('[useServiceWorker] No update available to apply');

            return;
        }

        try {
            isUpdating.value = true;

            await serviceWorkerManager.applyUpdate();

            // Update application happens via controllerchange event
            // -> handleUpdateApplied will reload page
        } catch (error) {
            console.error('[useServiceWorker] Failed to apply update:', error);
            isUpdating.value = false;

            throw error;
        }
    };

    // ==============================================================================
    // Lifecycle
    // ==============================================================================

    /**
     * Set up event listeners and initialize state.
     */
    onMounted(() => {
        // Get initial state from manager
        const state: Readonly<ServiceWorkerState> = serviceWorkerManager.getState();
        isSupported.value = state.isSupported;
        isRegistered.value = state.isRegistered;
        hasUpdate.value = state.hasUpdate;
        isUpdating.value = state.isUpdating;

        // Set up event listeners
        window.addEventListener('sw:registered', handleRegistered);
        window.addEventListener('sw:update-available', handleUpdateAvailable);
        window.addEventListener('sw:update-applied', handleUpdateApplied);
        window.addEventListener('sw:error', handleError);
    });

    /**
     * Clean up event listeners.
     */
    onUnmounted(() => {
        window.removeEventListener('sw:registered', handleRegistered);
        window.removeEventListener('sw:update-available', handleUpdateAvailable);
        window.removeEventListener('sw:update-applied', handleUpdateApplied);
        window.removeEventListener('sw:error', handleError);
    });

    // ==============================================================================
    // Return
    // ==============================================================================

    return {
        // State
        isSupported,
        isRegistered,
        hasUpdate,
        isUpdating,
        lastUpdateCheck,

        // Actions
        checkForUpdates,
        applyUpdate,
    };
}
