/**
 * Active Route Detection Composable
 *
 * Provides utilities for detecting the current active route in the application.
 * Used by navigation components to highlight the active navigation item.
 *
 * @example
 * ```typescript
 * const { isActive } = useActiveRoute();
 * const isDashboardActive = isActive('/employee/dashboard');
 * ```
 */

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Check if a given path is the currently active route.
 *
 * @returns Object with isActive function
 */
export function useActiveRoute() {
    const page = usePage();

    /**
     * Check if the given path matches the current URL.
     *
     * Uses startsWith matching to handle nested routes.
     * For example, '/employee/dashboard' will match both
     * '/employee/dashboard' and '/employee/dashboard/settings'.
     *
     * @param path - The path to check (e.g., '/employee/dashboard')
     * @returns True if the current URL starts with the given path
     *
     * @example
     * ```typescript
     * isActive('/employee/dashboard'); // true if on /employee/dashboard
     * isActive('/employee/trips'); // true if on /employee/trips or /employee/trips/123
     * ```
     */
    const isActive = (path: string): boolean => {
        return page.url.startsWith(path);
    };

    /**
     * Get the current full URL path.
     */
    const currentPath = computed(() => page.url);

    return {
        isActive,
        currentPath,
    };
}
