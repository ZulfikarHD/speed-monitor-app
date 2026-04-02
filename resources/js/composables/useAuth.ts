/**
 * Authentication composable for logout functionality.
 *
 * Provides logout method using Inertia router with Wayfinder integration.
 * Login is handled directly in Login.vue component using useForm + Wayfinder.
 *
 * @example
 * ```ts
 * const { handleLogout } = useAuth();
 *
 * // Logout user and redirect to login page
 * await handleLogout();
 * ```
 */
import { router } from '@inertiajs/vue3';

import { useAuthStore } from '@/stores/auth';

/**
 * Authentication composable with Inertia.js v3 and Wayfinder integration.
 *
 * Provides logout functionality. Login is handled by Login.vue component
 * using Inertia's useForm composable with Wayfinder route objects.
 *
 * @returns Object containing handleLogout method
 */
export function useAuth() {
    const authStore = useAuthStore();

    /**
     * Logout current user and redirect to login page.
     *
     * Sends logout request to backend using Inertia router with Wayfinder
     * route object. Clears user data and token from auth store and localStorage,
     * then redirects to login page. Always succeeds even if backend call fails
     * to ensure user can logout in offline scenarios.
     *
     * @returns Promise that resolves when logout completes and redirect happens
     *
     * @example
     * ```ts
     * const { handleLogout } = useAuth();
     *
     * // In a logout button click handler
     * await handleLogout();
     * // User is now logged out and redirected to /login
     * ```
     */
    const handleLogout = async (): Promise<void> => {
        return new Promise((resolve) => {
            router.post('/logout', {}, {
                onFinish: () => {
                    authStore.logout();
                    resolve();
                },
            });
        });
    };

    return {
        handleLogout,
    };
}
