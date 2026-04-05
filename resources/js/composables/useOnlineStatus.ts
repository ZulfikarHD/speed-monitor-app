/**
 * Online Status Composable
 *
 * Provides reactive online/offline status detection using the Network Information API
 * and browser online/offline events. Enables offline-first architecture by allowing
 * components to adapt behavior based on connectivity.
 *
 * Features:
 * - Reactive online/offline state
 * - Automatic event listener management
 * - Network information API integration (connection type, effective type)
 * - Cleanup on component unmount
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useOnlineStatus } from '@/composables/useOnlineStatus';
 *
 * const { isOnline, isOffline, connectionType } = useOnlineStatus();
 * </script>
 *
 * <template>
 *   <div v-if="isOffline" class="offline-banner">
 *     Mode Offline - Data akan disinkronkan saat online
 *   </div>
 * </template>
 * ```
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Network connection type from Network Information API.
 *
 * Provides information about the user's connection type.
 */
export type ConnectionType =
    | 'bluetooth'
    | 'cellular'
    | 'ethernet'
    | 'none'
    | 'wifi'
    | 'wimax'
    | 'other'
    | 'unknown';

/**
 * Network effective connection type.
 *
 * Represents the effective speed classification of the connection.
 */
export type EffectiveConnectionType = 'slow-2g' | '2g' | '3g' | '4g' | 'unknown';

/**
 * Online status composable return type.
 */
export interface UseOnlineStatusReturn {
    /** Reactive boolean indicating if currently online */
    isOnline: Readonly<ReturnType<typeof ref<boolean>>>;

    /** Reactive boolean indicating if currently offline (computed from isOnline) */
    isOffline: Readonly<ReturnType<typeof computed<boolean>>>;

    /** Current connection type (from Network Information API) */
    connectionType: Readonly<ReturnType<typeof ref<ConnectionType>>>;

    /** Effective connection type (speed classification) */
    effectiveType: Readonly<ReturnType<typeof ref<EffectiveConnectionType>>>;

    /** Manually refresh online status (useful after network changes) */
    refresh: () => void;
}

/**
 * Online/Offline status detection composable.
 *
 * Monitors network connectivity and provides reactive state for
 * implementing offline-first features. Automatically sets up and
 * cleans up event listeners for online/offline events.
 *
 * @returns Object with online status state and utilities
 *
 * @example
 * ```ts
 * // Basic usage
 * const { isOnline, isOffline } = useOnlineStatus();
 *
 * if (isOffline.value) {
 *   console.log('Offline mode - saving to IndexedDB');
 * }
 * ```
 *
 * @example
 * ```ts
 * // With connection type
 * const { isOnline, connectionType, effectiveType } = useOnlineStatus();
 *
 * if (isOnline.value && effectiveType.value === 'slow-2g') {
 *   console.log('Online but slow connection - optimize data transfer');
 * }
 * ```
 */
export function useOnlineStatus(): UseOnlineStatusReturn {
    /**
     * Reactive online status.
     *
     * Initialized from navigator.onLine and updated via event listeners.
     * True when browser has network connectivity, false otherwise.
     */
    const isOnline = ref<boolean>(navigator.onLine);

    /**
     * Reactive offline status (computed).
     *
     * Convenience property for checking offline state.
     * Always the inverse of isOnline.
     */
    const isOffline = computed(() => !isOnline.value);

    /**
     * Connection type from Network Information API.
     *
     * Indicates the type of network connection (wifi, cellular, etc).
     * Falls back to 'unknown' if API not available.
     */
    const connectionType = ref<ConnectionType>('unknown');

    /**
     * Effective connection type (speed classification).
     *
     * Represents the effective speed of the connection (slow-2g to 4g).
     * Falls back to 'unknown' if API not available.
     */
    const effectiveType = ref<EffectiveConnectionType>('unknown');

    /**
     * Update connection information from Network Information API.
     *
     * Reads connection type and effective type if the API is supported.
     * Called on mount and when connection changes.
     */
    const updateConnectionInfo = (): void => {
        // Check if Network Information API is available
        const connection =
            (navigator as any).connection ||
            (navigator as any).mozConnection ||
            (navigator as any).webkitConnection;

        if (connection) {
            // Update connection type
            connectionType.value = (connection.type as ConnectionType) || 'unknown';

            // Update effective connection type
            effectiveType.value = (connection.effectiveType as EffectiveConnectionType) || 'unknown';
        } else {
            // API not available - set to unknown
            connectionType.value = 'unknown';
            effectiveType.value = 'unknown';
        }
    };

    /**
     * Handle online event.
     *
     * Called when browser regains network connectivity.
     * Updates state and connection information.
     */
    const handleOnline = (): void => {
        isOnline.value = true;
        updateConnectionInfo();
    };

    /**
     * Handle offline event.
     *
     * Called when browser loses network connectivity.
     * Updates state and connection information.
     */
    const handleOffline = (): void => {
        isOnline.value = false;
        updateConnectionInfo();
    };

    /**
     * Handle connection change event (Network Information API).
     *
     * Called when connection type or effective type changes.
     * Updates connection information without changing online status.
     */
    const handleConnectionChange = (): void => {
        updateConnectionInfo();
    };

    /**
     * Manually refresh online status and connection info.
     *
     * Useful for forcing a check after network changes or
     * when suspecting stale state.
     */
    const refresh = (): void => {
        isOnline.value = navigator.onLine;
        updateConnectionInfo();
    };

    /**
     * Set up event listeners on component mount.
     *
     * Registers listeners for online, offline, and connection change events.
     * Also performs initial connection info update.
     */
    onMounted(() => {
        // Add online/offline event listeners
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Add connection change listener (Network Information API)
        const connection =
            (navigator as any).connection ||
            (navigator as any).mozConnection ||
            (navigator as any).webkitConnection;

        if (connection) {
            connection.addEventListener('change', handleConnectionChange);
        }

        // Initial connection info update
        updateConnectionInfo();
    });

    /**
     * Clean up event listeners on component unmount.
     *
     * Removes all registered event listeners to prevent memory leaks.
     */
    onUnmounted(() => {
        // Remove online/offline event listeners
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);

        // Remove connection change listener (Network Information API)
        const connection =
            (navigator as any).connection ||
            (navigator as any).mozConnection ||
            (navigator as any).webkitConnection;

        if (connection) {
            connection.removeEventListener('change', handleConnectionChange);
        }
    });

    return {
        isOnline,
        isOffline,
        connectionType,
        effectiveType,
        refresh
    };
}
