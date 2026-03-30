<script setup lang="ts">
/**
 * Login Page - User authentication with form validation.
 *
 * Features:
 * - Email and password inputs with client-side validation
 * - Loading state during API call
 * - Error message display for invalid credentials
 * - Role-based redirect after successful login
 * - Test account hints for development
 */

import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import { useAuth } from '@/composables/useAuth';

const email = ref('');
const password = ref('');
const validationErrors = ref<Record<string, string>>({});

const { handleLogin, isLoading, error } = useAuth();

const validateForm = (): boolean => {
    validationErrors.value = {};

    if (!email.value) {
        validationErrors.value.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        validationErrors.value.email = 'Please enter a valid email address';
    }

    if (!password.value) {
        validationErrors.value.password = 'Password is required';
    } else if (password.value.length < 8) {
        validationErrors.value.password = 'Password must be at least 8 characters';
    }

    return Object.keys(validationErrors.value).length === 0;
};

const onSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    await handleLogin({
        email: email.value,
        password: password.value,
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

                <form @submit.prevent="onSubmit" class="space-y-4">
                    <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200">
                        {{ error }}
                    </div>

                    <div>
                        <label
                            for="email"
                            class="mb-1.5 block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            autocomplete="email"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:outline-none focus:ring-2 focus:ring-[#1b1b18]/10 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    validationErrors.email,
                            }"
                            :disabled="isLoading"
                        />
                        <p
                            v-if="validationErrors.email"
                            class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ validationErrors.email }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="mb-1.5 block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:outline-none focus:ring-2 focus:ring-[#1b1b18]/10 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    validationErrors.password,
                            }"
                            :disabled="isLoading"
                        />
                        <p
                            v-if="validationErrors.password"
                            class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ validationErrors.password }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg border border-black bg-[#1b1b18] px-5 py-2.5 text-sm font-medium leading-normal text-white transition-colors hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        :disabled="isLoading"
                    >
                        <span v-if="isLoading" class="flex items-center justify-center">
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
