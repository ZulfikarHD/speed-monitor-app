<script setup lang="ts">
/**
 * Login Page - User authentication form.
 *
 * Professional login page following SpeedMonitor design system with
 * full dark mode support, tech-inspired aesthetics, and modern UX principles.
 *
 * Authentication Flow:
 * 1. User enters email/password
 * 2. Client-side validation checks format
 * 3. Form submits to backend via Inertia
 * 4. Backend returns user data + token as Inertia props
 * 5. Frontend stores in localStorage via auth store
 * 6. Redirects to role-based dashboard
 *
 * UX Principles Applied:
 * - Fitts's Law: Large touch targets (44px+ buttons)
 * - Law of Proximity: Related elements grouped (label+input)
 * - Law of Common Region: Card groups form elements
 * - Progressive Disclosure: Errors shown only when relevant
 * - Aesthetic-Usability Effect: Professional visual design
 *
 * Design System Features:
 * - Extra dark mode (black/zinc-950 base)
 * - Tech grid backgrounds (64px pattern)
 * - Glassmorphism card with backdrop blur
 * - Cyan/blue gradient accents
 * - Smooth 200-300ms transitions
 * - Proper shadows (light) and glows (dark)
 * - Theme-aware color palette
 *
 * @see {@link https://inertiajs.com/forms Inertia Forms Documentation}
 */

import { Head, useForm } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import { IconCar, IconLoader, IconLock, IconMail } from '@/components/icons';

// ========================================================================
// Form State Management
// ========================================================================

/**
 * Inertia form composable for authentication.
 *
 * WHY: useForm provides form state, validation errors, and loading state.
 * Automatically handles Laravel validation errors from backend.
 */
const form = useForm({
    email: '',
    password: '',
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle form submission.
 *
 * Posts to Laravel backend authentication endpoint.
 * Backend handles validation, authentication, and role-based redirect.
 */
const handleSubmit = (): void => {
    form.post('/login');
};
</script>

<template>
    <!-- Document Head -->
    <Head title="Login - SpeedMonitor" />

    <!-- ======================================================================
        Login Page Container
        Theme-aware professional layout with extra dark mode
    ======================================================================= -->
    <div class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black px-4 py-6 sm:px-6">
        <!-- Tech Grid Background (64px pattern, theme-aware) -->
        <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>

        <!-- Radial Gradient Overlay (theme-aware) -->
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,rgba(6,182,212,0.05),transparent_50%)] dark:bg-[radial-gradient(ellipse_at_top,rgba(6,182,212,0.08),transparent_50%)]"></div>

        <!-- ====================================================================
            Login Card Container
            Animated entrance with motion-v
        ===================================================================== -->
        <motion.div
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.3 }"
            class="relative z-10 w-full max-w-md"
        >
            <!-- ================================================================
                Glassmorphism Card
                Professional card with backdrop blur and theme-aware borders
            ================================================================= -->
            <motion.div
                :initial="{ opacity: 0, scale: 0.98 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 0.3, delay: 0.1 }"
                class="overflow-hidden rounded-lg backdrop-blur-xl bg-white/90 dark:bg-black/98 border border-zinc-200 dark:border-white/5 p-8 shadow-lg shadow-zinc-200 dark:shadow-none"
            >
                <!-- ============================================================
                    Header Section
                    Logo, title, and subtitle
                ============================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.15, duration: 0.3 }"
                    class="mb-8 text-center"
                >
                    <!-- SpeedMonitor Logo -->
                    <div class="mb-6 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-cyan-200 dark:border-cyan-500/20 bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-500/10 dark:to-blue-600/10 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/10">
                            <IconCar
                                :size="32"
                                class="text-cyan-600 dark:text-cyan-400"
                            />
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="mb-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                        Welcome Back
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Sign in to your SpeedMonitor account
                    </p>
                </motion.div>

                <!-- ============================================================
                    Login Form
                    Email and password inputs with validation
                ============================================================= -->
                <motion.form
                    @submit.prevent="handleSubmit"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ delay: 0.2, duration: 0.3 }"
                    class="space-y-5"
                    novalidate
                >
                    <!-- Backend Error Message (Progressive Disclosure) -->
                    <motion.div
                        v-if="form.errors.email"
                        :initial="{ opacity: 0, height: 0 }"
                        :animate="{ opacity: 1, height: 'auto' }"
                        :exit="{ opacity: 0, height: 0 }"
                        :transition="{ duration: 0.2 }"
                        class="rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300"
                        role="alert"
                        aria-live="polite"
                    >
                        {{ form.errors.email }}
                    </motion.div>

                    <!-- Email Input (Law of Proximity: label + input grouped) -->
                    <motion.div
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.3, delay: 0.25 }"
                    >
                        <label
                            for="email"
                            class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            Email Address
                        </label>
                        <div class="relative">
                            <!-- Email Icon -->
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <IconMail
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <!-- Email Input -->
                            <input
                                id="email"
                                v-model="form.email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="your.email@example.com"
                                required
                                class="w-full rounded-lg border pl-10 pr-4 py-2.5 text-sm transition-all duration-200 bg-white dark:bg-zinc-800/50 border-zinc-300 dark:border-white/10 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 focus:border-cyan-500 dark:focus:border-cyan-400/50 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'border-red-300 dark:border-red-500/30 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50':
                                        form.errors.email,
                                }"
                                :disabled="form.processing"
                                :aria-invalid="!!form.errors.email"
                                aria-describedby="email-error"
                            />
                        </div>
                    </motion.div>

                    <!-- Password Input (Law of Proximity: label + input grouped) -->
                    <motion.div
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.3, delay: 0.3 }"
                    >
                        <label
                            for="password"
                            class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            Password
                        </label>
                        <div class="relative">
                            <!-- Lock Icon -->
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <IconLock
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <!-- Password Input -->
                            <input
                                id="password"
                                v-model="form.password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                required
                                class="w-full rounded-lg border pl-10 pr-4 py-2.5 text-sm transition-all duration-200 bg-white dark:bg-zinc-800/50 border-zinc-300 dark:border-white/10 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 focus:border-cyan-500 dark:focus:border-cyan-400/50 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'border-red-300 dark:border-red-500/30 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50':
                                        form.errors.password,
                                }"
                                :disabled="form.processing"
                                :aria-invalid="!!form.errors.password"
                                aria-describedby="password-error"
                            />
                        </div>
                    </motion.div>

                    <!-- Submit Button (Fitts's Law: large touch target) -->
                    <motion.button
                        type="submit"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, delay: 0.35 }"
                        class="w-full rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 hover:shadow-xl hover:shadow-cyan-300 dark:hover:shadow-cyan-500/40 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none"
                        :disabled="form.processing"
                        :aria-busy="form.processing"
                    >
                        <!-- Loading State -->
                        <span
                            v-if="form.processing"
                            class="flex items-center justify-center gap-2"
                        >
                            <IconLoader :size="18" />
                            <span>Signing in...</span>
                        </span>

                        <!-- Default State -->
                        <span v-else>Sign In</span>
                    </motion.button>
                </motion.form>

                <!-- ============================================================
                    Test Accounts Info (Development)
                    Shows available test credentials for development
                ============================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.4, duration: 0.3 }"
                    class="mt-8 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-zinc-800/30 px-4 py-3"
                >
                    <p class="mb-2 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                        Development Test Accounts
                    </p>
                    <div class="space-y-1 text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                        <p>Admin: admin@example.com</p>
                        <p>Supervisor: supervisor@example.com</p>
                        <p>Employee: employee@example.com</p>
                        <p class="mt-2 pt-2 border-t border-zinc-200 dark:border-white/10">
                            Password: <span class="text-zinc-700 dark:text-zinc-300">password</span>
                        </p>
                    </div>
                </motion.div>
            </motion.div>

            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-zinc-500 dark:text-zinc-500 font-mono tracking-wide">
                SPEEDMONITOR © 2026
            </p>
        </motion.div>
    </div>
</template>
