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

import { Head, useForm } from '@inertiajs/vue3';
import { motion } from 'motion-v';

// Inertia useForm - the proper way
const form = useForm({
    email: '',
    password: '',
});

const handleSubmit = (): void => {
    form.post('/login');
};
</script>

<template>
    <Head title="Login">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] px-4 py-6 text-[#1b1b18] sm:px-6 dark:bg-[#0a0a0a]"
    >
        <motion.div
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', bounce: 0.4, duration: 0.8 }"
            class="w-full max-w-md sm:max-w-lg"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                class="overflow-hidden rounded-lg bg-white p-8 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <motion.div
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.2, duration: 0.5 }"
                    class="mb-6 text-center"
                >
                    <h1 class="mb-2 text-3xl font-semibold sm:text-4xl">Welcome Back</h1>
                    <p class="text-base text-[#706f6c] dark:text-[#A1A09A]">
                        Sign in to your account to continue
                    </p>
                </motion.div>

                <motion.form
                    @submit.prevent="handleSubmit"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ delay: 0.3, duration: 0.5 }"
                    class="space-y-4"
                >
                    <!-- Backend Error Message -->
                    <motion.div
                        v-if="form.errors.email"
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :exit="{ opacity: 0, x: 10 }"
                        :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
                        class="rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200"
                    >
                        {{ form.errors.email }}
                    </motion.div>

                    <!-- Email Input -->
                    <motion.div
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.35 }"
                    >
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
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/10 focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    form.errors.email,
                            }"
                            :disabled="form.processing"
                        />
                    </motion.div>

                    <!-- Password Input -->
                    <motion.div
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.4 }"
                    >
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
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm transition-colors focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/10 focus:outline-none dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/10':
                                    form.errors.password,
                            }"
                            :disabled="form.processing"
                        />
                    </motion.div>

                    <!-- Submit Button -->
                    <motion.button
                        type="submit"
                        :whileHover="{ scale: 1.01 }"
                        :whilePress="{ scale: 0.98 }"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.45 }"
                        class="w-full rounded-lg border border-black bg-[#1b1b18] px-5 py-2.5 text-sm leading-normal font-medium text-white transition-colors hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        :disabled="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center justify-center"
                        >
                            <svg
                                class="mr-2 -ml-1 h-4 w-4 animate-spin"
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
                    </motion.button>
                </motion.form>

                <!-- Test Accounts Info -->
                <motion.div
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.5, duration: 0.5 }"
                    class="mt-6 text-center text-base text-[#706f6c] dark:text-[#A1A09A]"
                >
                    <p class="mb-2">Test Accounts:</p>
                    <div class="space-y-1 text-sm">
                        <p>Admin: admin@example.com</p>
                        <p>Supervisor: supervisor@example.com</p>
                        <p>Employee: employee@example.com</p>
                        <p class="mt-1 italic">Password: password</p>
                    </div>
                </motion.div>
            </motion.div>
        </motion.div>
    </div>
</template>
