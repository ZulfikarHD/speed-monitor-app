/**
 * Authentication composable for login, logout, and user management.
 *
 * Integrates Inertia.js v3 useHttp with Wayfinder-generated routes and
 * Pinia auth store. Handles token-based authentication with localStorage
 * persistence and role-based redirects.
 *
 * @example
 * const { handleLogin, handleLogout, isLoading, error } = useAuth()
 * await handleLogin({ email: 'user@example.com', password: 'password' })
 */

import { router, useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import { login as loginAction, logout as logoutAction, me as meAction } from '@/actions/App/Http/Controllers/Auth/AuthController';

import { useAuthStore } from '@/stores/auth';
import type { LoginCredentials, LoginResponse } from '@/types/api';
import type { User } from '@/types/auth';

/**
 * Authentication composable with Inertia.js v3 and Wayfinder integration.
 *
 * @returns Authentication methods and reactive state
 */
export function useAuth() {
    const authStore = useAuthStore();
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const http = useHttp();

    /**
     * Authenticate user and redirect to role-based dashboard.
     *
     * Uses Wayfinder-generated login route and Inertia's useHttp for API call.
     * On success, stores token in localStorage via Pinia store and navigates
     * user to appropriate dashboard based on their role.
     *
     * @param credentials - User email and password
     * @returns Promise resolving to true on success, false on failure
     */
    const handleLogin = async (credentials: LoginCredentials) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await http.post<LoginResponse>(loginAction.url(), credentials);

            if (response.data) {
                authStore.login(response.data.user, response.data.token);

                // Role-based redirect after successful login
                const role = response.data.user.role;
                const redirectUrl =
                    role === 'admin'
                        ? '/admin/dashboard'
                        : role === 'supervisor'
                          ? '/supervisor/dashboard'
                          : '/employee/dashboard';

                router.visit(redirectUrl);

                return true;
            }

            return false;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'An unexpected error occurred';

            return false;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Logout user and clear authentication state.
     *
     * Calls API logout endpoint to invalidate token, then clears local state
     * and redirects to login page. Always succeeds even if API call fails
     * to ensure user can logout even when offline.
     *
     * @returns Promise resolving to true when logout completes
     */
    const handleLogout = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            await http.post(logoutAction.url(), {}, {
                headers: {
                    Authorization: `Bearer ${authStore.token}`,
                },
            });
            authStore.logout();
            router.visit('/login');

            return true;
        } catch {
            // Even if API call fails, logout locally
            authStore.logout();
            router.visit('/login');

            return true;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Fetch current authenticated user data from API.
     *
     * Used to restore user session on app initialization or refresh token.
     * On failure, clears auth state to force re-authentication.
     *
     * @returns Promise resolving to User object or null on failure
     */
    const fetchCurrentUser = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await http.get<{ user: User }>(meAction.url(), {
                headers: {
                    Authorization: `Bearer ${authStore.token}`,
                },
            });

            if (response.data) {
                authStore.setUser(response.data.user);

                return response.data.user;
            }

            return null;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch user';
            authStore.logout();

            return null;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        isLoading,
        error,
        handleLogin,
        handleLogout,
        fetchCurrentUser,
    };
}
