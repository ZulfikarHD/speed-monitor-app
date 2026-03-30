<script setup lang="ts">
/**
 * Login Page - User authentication form.
 *
 * Handles user login using Inertia.js v3 useForm composable with
 * Wayfinder integration for type-safe route handling. Includes
 * client-side validation, loading states, and error display.
 *
 * Authentication Flow:
 * 1. User enters email/password
 * 2. Client-side validation checks format
 * 3. Form submits to backend via Wayfinder route
 * 4. Backend returns user data + token as Inertia props
 * 5. Frontend stores in localStorage via auth store
 * 6. Redirects to role-based dashboard
 *
 * Features:
 * - Email and password inputs with client-side validation
 * - Inertia useForm composable with Wayfinder integration
 * - Backend error display from Laravel validation
 * - Loading state with disabled inputs during submission
 * - Role-based redirect (admin/supervisor/employee dashboards)
 * - Test account hints for development
 *
 * @see {@link https://inertiajs.com/forms Inertia Forms Documentation}
 * @see {@link https://laravel.com/docs/wayfinder Wayfinder Documentation}
 */

import { Head, router, useForm } from '@inertiajs/vue3';

import { login as loginAction } from '@/actions/App/Http/Controllers/Auth/AuthController';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Inertia form state with Wayfinder integration
// useForm provides reactive form data, processing state, and error handling
const form = useForm({
    email: '',
    password: '',
});

// Client-side validation errors (separate from backend errors)
// These are cleared when user types to provide immediate feedback
const clientErrors = {
    email: '',
    password: '',
};

/**
 * Validate form inputs before submission.
 *
 * Checks email format and password length requirements.
 * Updates clientErrors object with validation messages.
 *
 * @returns True if all fields are valid, false otherwise
 */
const validateForm = (): boolean => {
    clientErrors.email = '';
    clientErrors.password = '';

    if (!form.email) {
        clientErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        clientErrors.email = 'Please enter a valid email address';
    }

    if (!form.password) {
        clientErrors.password = 'Password is required';
    } else if (form.password.length < 8) {
        clientErrors.password = 'Password must be at least 8 characters';
    }

    return !clientErrors.email && !clientErrors.password;
};

/**
 * Handle form submission.
 *
 * Validates form inputs, then submits to backend using Wayfinder route object.
 * On success, stores user data and token in auth store (persisted to localStorage),
 * then redirects to appropriate dashboard based on user role.
 *
 * Wayfinder Integration:
 * - loginAction() returns route object: { url: '/api/auth/login', method: 'post' }
 * - form.submit() uses this object for type-safe Inertia form submission
 * - Backend returns user data + token as Inertia props
 *
 * @returns void
 */
const handleSubmit = (): void => {
    // Step 1: Validate client-side before sending request
    if (!validateForm()) {
        return;
    }

    // Step 2: Submit form using Wayfinder route object
    // Wayfinder provides type-safe route generation from Laravel routes
    form.submit(loginAction(), {
        preserveScroll: true,
        onSuccess: (page) => {
            const responseData = page.props as any;

            if (responseData.user && responseData.token) {
                // Step 3: Store user data and token in auth store
                // This persists to localStorage for session recovery
                authStore.login(responseData.user, responseData.token);

                // Step 4: Redirect to role-based dashboard
                const role = responseData.user.role;
                const redirectUrl =
                    role === 'admin'
                        ? '/admin/dashboard'
                        : role === 'supervisor'
                          ? '/supervisor/dashboard'
                          : '/employee/dashboard';

                router.visit(redirectUrl);
            }
        },
    });
};
</script>

<template>
    <Head title="Login">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-md">
            <div
                class="overflow-hidden rounded-lg bg-white p-8 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="mb-6 text-center">
                    <h1 class="mb-2 text-2xl font-semibold">Welcome Back</h1>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Sign in to your account to continue
                    </p>
                </div>

                <!-- Wayfinder + Inertia useForm -->
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- Backend Error Message -->
                    <div
                        v-if="form.errors.email || form.errors.password"
                        class="rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200"
                    >
                        {{ form.errors.email || form.errors.password }}
                    </div>

                    <!-- Email Input -->
                    <div>
                        <label
                            for="email"
                            class="mb-1.5 block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:outline-none focus:ring-2 focus:ring-[#1b1b18]/10 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    clientErrors.email || form.errors.email,
                            }"
                            :disabled="form.processing"
                            @input="clientErrors.email = ''"
                        />
                        <p
                            v-if="clientErrors.email"
                            class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ clientErrors.email }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label
                            for="password"
                            class="mb-1.5 block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:outline-none focus:ring-2 focus:ring-[#1b1b18]/10 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    clientErrors.password || form.errors.password,
                            }"
                            :disabled="form.processing"
                            @input="clientErrors.password = ''"
                        />
                        <p
                            v-if="clientErrors.password"
                            class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ clientErrors.password }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full rounded-lg border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium leading-normal text-white transition-colors hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center">
                            <svg
                                class="-ml-1 mr-2 h-4 w-4 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Signing in...
                        </span>
                        <span v-else>Sign in</span>
                    </button>
                </form>

                <!-- Test Accounts Info -->
                <div class="mt-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    <p class="mb-2">Test Accounts:</p>
                    <div class="space-y-1 text-xs">
                        <p>Admin: admin@example.com</p>
                        <p>Supervisor: supervisor@example.com</p>
                        <p>Employee: employee@example.com</p>
                        <p class="mt-1 italic">Password: password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
