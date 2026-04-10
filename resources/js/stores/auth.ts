/**
 * Authentication Store - User session and token management.
 *
 * Manages authenticated user state, Sanctum API token, and role-based
 * access control. Persists data to localStorage for session recovery
 * across page refreshes.
 *
 * @example
 * ```ts
 * const authStore = useAuthStore();
 *
 * // Login user
 * authStore.login(userData, token);
 *
 * // Check authentication
 * if (authStore.isAuthenticated) {
 *   console.log('User:', authStore.user.name);
 * }
 *
 * // Role checks
 * if (authStore.isAdmin) {
 *   // Admin-only features
 * }
 *
 * // Logout user
 * authStore.logout();
 * ```
 */
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

/** User role types for role-based access control */
export type UserRole = 'employee' | 'superuser' | 'admin';

/**
 * User entity - authenticated user data.
 *
 * Represents a user in the system with role-based permissions.
 */
export interface User {
    /** Unique user identifier */
    id: number;
    /** Full name of the user */
    name: string;
    /** Email address (used for login) */
    email: string;
    /** Nomor Pokok Karyawan */
    npk: string | null;
    /** Division name */
    divisi: string | null;
    /** Department name */
    departement: string | null;
    /** Section name */
    section: string | null;
    /** User role determining access permissions */
    role: UserRole;
    /** Account status - inactive users cannot login */
    is_active: boolean;
    /** Account creation timestamp */
    created_at: string;
    /** Last update timestamp */
    updated_at: string;
}

/**
 * Authentication store for user session management.
 *
 * Manages authenticated user state, Sanctum token, and provides
 * role-based computed properties for access control. Automatically
 * persists to localStorage for session recovery.
 */
export const useAuthStore = defineStore('auth', () => {
    /** Current authenticated user, null if not authenticated */
    const user = ref<User | null>(null);

    /** Sanctum API token for backend requests, null if not authenticated */
    const token = ref<string | null>(null);

    /** Whether user is currently authenticated */
    const isAuthenticated = computed(() => user.value !== null);

    /** Current user's role, null if not authenticated */
    const role = computed(() => user.value?.role ?? null);

    /** Whether current user has employee role */
    const isEmployee = computed(() => role.value === 'employee');

    /** Whether current user has superuser role */
    const isSuperuser = computed(() => role.value === 'superuser');

    /** Whether current user has admin role */
    const isAdmin = computed(() => role.value === 'admin');

    /**
     * Set user data and persist to localStorage.
     *
     * Updates the current user state and saves to localStorage for
     * session recovery on page refresh. Pass null to clear user data.
     *
     * @param userData - User object or null to clear
     */
    function setUser(userData: User | null) {
        user.value = userData;

        if (userData) {
            localStorage.setItem('auth_user', JSON.stringify(userData));
        } else {
            localStorage.removeItem('auth_user');
        }
    }

    /**
     * Set authentication token and persist to localStorage.
     *
     * Updates the Sanctum API token and saves to localStorage for
     * session recovery. Pass null to clear the token.
     *
     * @param tokenValue - Sanctum token string or null to clear
     */
    function setToken(tokenValue: string | null) {
        token.value = tokenValue;

        if (tokenValue) {
            localStorage.setItem('auth_token', tokenValue);
        } else {
            localStorage.removeItem('auth_token');
        }
    }

    /**
     * Login user with credentials.
     *
     * Sets user data and token, persisting both to localStorage.
     * Called after successful authentication from backend.
     *
     * @param userData - Authenticated user object from backend
     * @param tokenValue - Sanctum API token from backend
     */
    function login(userData: User, tokenValue: string) {
        setUser(userData);
        setToken(tokenValue);
    }

    /**
     * Logout user and clear all authentication data.
     *
     * Clears user data and token from both state and localStorage.
     * Called when user explicitly logs out or token becomes invalid.
     */
    function logout() {
        setUser(null);
        setToken(null);
    }

    /**
     * Initialize auth store from localStorage.
     *
     * Attempts to restore user session from localStorage on app startup.
     * If data is invalid or missing, clears auth state. Called once during
     * app initialization in app.ts.
     */
    function initializeAuth() {
        const storedToken = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('auth_user');

        if (storedToken && storedUser) {
            try {
                // Parse and validate stored user data
                const userData = JSON.parse(storedUser) as User;
                user.value = userData;
                token.value = storedToken;
            } catch {
                // Invalid data: clear auth state
                logout();
            }
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        role,
        isEmployee,
        isSuperuser,
        isAdmin,
        setUser,
        setToken,
        login,
        logout,
        initializeAuth,
    };
});
