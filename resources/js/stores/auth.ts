import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export type UserRole = 'employee' | 'supervisor' | 'admin';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(null);

    const isAuthenticated = computed(() => user.value !== null);
    const role = computed(() => user.value?.role ?? null);
    const isEmployee = computed(() => role.value === 'employee');
    const isSupervisor = computed(() => role.value === 'supervisor');
    const isAdmin = computed(() => role.value === 'admin');

    function setUser(userData: User | null) {
        user.value = userData;
    }

    function setToken(tokenValue: string | null) {
        token.value = tokenValue;

        if (tokenValue) {
            localStorage.setItem('auth_token', tokenValue);
        } else {
            localStorage.removeItem('auth_token');
        }
    }

    function login(userData: User, tokenValue: string) {
        setUser(userData);
        setToken(tokenValue);
    }

    function logout() {
        setUser(null);
        setToken(null);
    }

    function initializeAuth() {
        const storedToken = localStorage.getItem('auth_token');

        if (storedToken) {
            setToken(storedToken);
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        role,
        isEmployee,
        isSupervisor,
        isAdmin,
        setUser,
        setToken,
        login,
        logout,
        initializeAuth,
    };
});
