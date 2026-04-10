<script setup lang="ts">
/**
 * Login Page - User authentication form.
 *
 * Professional login page following SpeedMonitor design system with
 * full dark mode support, tech-inspired aesthetics, and lucide icons.
 * Single entrance animation on the card — no staggered child animations
 * to keep rendering lightweight.
 *
 * UX Principles Applied:
 * - Fitts's Law: Large touch targets (44px+ submit button)
 * - Law of Proximity: Related elements grouped (label+input)
 * - Law of Common Region: Card groups form elements
 * - Progressive Disclosure: Errors shown only when relevant
 * - Aesthetic-Usability Effect: Professional visual design
 */

import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Lock, Mail, Moon, Route, Sun } from '@lucide/vue';
import { motion } from 'motion-v';

import { useTheme } from '@/composables/useTheme';

const { isDark, toggleTheme } = useTheme();

// ========================================================================
// Form State
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
 * Posts to Laravel backend authentication endpoint.
 */
const handleSubmit = (): void => {
    form.post('/login');
};
</script>

<template>
    <Head title="Masuk - SpeedMonitor" />

    <!-- ======================================================================
        Login Page Container
        Theme-aware with extra dark mode (black base)
    ======================================================================= -->
    <div
        class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black px-4 py-6 sm:px-6"
    >
        <!-- Theme Toggle (top-right corner) -->
        <button
            type="button"
            class="fixed top-4 right-4 z-50 flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/90 dark:bg-zinc-800/90 text-zinc-600 dark:text-zinc-400 shadow-lg transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/10 hover:text-zinc-900 dark:hover:text-zinc-200"
            :aria-label="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
            @click="toggleTheme"
        >
            <Moon v-if="!isDark" :size="20" />
            <Sun v-else :size="20" />
        </button>

        <!-- Tech Grid Background (64px pattern, theme-aware) -->
        <div
            class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] bg-[size:64px_64px]"
        ></div>

        <!-- Radial Gradient Overlay -->
        <div
            class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,rgba(6,182,212,0.05),transparent_50%)] dark:bg-[radial-gradient(ellipse_at_top,rgba(6,182,212,0.08),transparent_50%)]"
        ></div>

        <!-- Login Card (single entrance animation) -->
        <motion.div
            :initial="{ opacity: 0, y: 16 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.3 }"
            class="relative z-10 w-full max-w-md"
        >
            <!-- ================================================================
                Fake Glass Card
                Semi-transparent bg + borders + shadow — no backdrop-blur
            ================================================================= -->
            <div
                class="overflow-hidden rounded-lg p-8 bg-white/95 dark:bg-zinc-900/98 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
            >
                <!-- Header Section -->
                <div class="mb-8 text-center">
                    <!-- SpeedMonitor Logo -->
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-xl border border-cyan-200 dark:border-cyan-500/20 bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-500/10 dark:to-blue-600/10 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/10"
                        >
                            <Route
                                :size="32"
                                class="text-cyan-600 dark:text-cyan-400"
                            />
                        </div>
                    </div>

                    <h1 class="mb-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                        Selamat Datang
                    </h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Masuk ke akun SpeedMonitor Anda
                    </p>
                </div>

                <!-- Login Form -->
                <form
                    @submit.prevent="handleSubmit"
                    class="space-y-5"
                    novalidate
                >
                    <!-- Backend Error Message (Progressive Disclosure) -->
                    <div
                        v-if="form.errors.email"
                        class="rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300"
                        role="alert"
                        aria-live="polite"
                    >
                        {{ form.errors.email }}
                    </div>

                    <!-- Email Input (Law of Proximity: label + input grouped) -->
                    <div>
                        <label
                            for="email"
                            class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <Mail
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="your.email@example.com"
                                required
                                class="w-full rounded-lg border pl-10 pr-4 py-2.5 text-sm transition-colors duration-200 bg-white dark:bg-zinc-800/50 border-zinc-300 dark:border-white/10 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 focus:border-cyan-500 dark:focus:border-cyan-400/50 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'border-red-300 dark:border-red-500/30 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50':
                                        form.errors.email,
                                }"
                                :disabled="form.processing"
                                :aria-invalid="!!form.errors.email"
                                aria-describedby="email-error"
                            />
                        </div>
                    </div>

                    <!-- Password Input (Law of Proximity: label + input grouped) -->
                    <div>
                        <label
                            for="password"
                            class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <Lock
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi"
                                required
                                class="w-full rounded-lg border pl-10 pr-4 py-2.5 text-sm transition-colors duration-200 bg-white dark:bg-zinc-800/50 border-zinc-300 dark:border-white/10 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 focus:border-cyan-500 dark:focus:border-cyan-400/50 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'border-red-300 dark:border-red-500/30 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50':
                                        form.errors.password,
                                }"
                                :disabled="form.processing"
                                :aria-invalid="!!form.errors.password"
                                aria-describedby="password-error"
                            />
                        </div>
                    </div>

                    <!-- Submit Button (Fitts's Law: 48px height touch target) -->
                    <button
                        type="submit"
                        class="w-full rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 hover:shadow-xl hover:shadow-cyan-300 dark:hover:shadow-cyan-500/40 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none"
                        :disabled="form.processing"
                        :aria-busy="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center justify-center gap-2"
                        >
                            <Loader2
                                :size="18"
                                class="animate-spin"
                            />
                            <span>Masuk...</span>
                        </span>
                        <span v-else>Masuk</span>
                    </button>
                </form>

                <!-- Test Accounts Info (Development) -->
                <div
                    class="mt-8 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-zinc-800/30 px-4 py-3"
                >
                    <p class="mb-2 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                        Akun Uji Coba
                    </p>
                    <div class="space-y-1 text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                        <p>Admin: admin@example.com</p>
                        <p>Superuser: superuser@example.com</p>
                        <p>Employee: employee@example.com</p>
                        <p class="mt-2 pt-2 border-t border-zinc-200 dark:border-white/10">
                            Kata sandi: <span class="text-zinc-700 dark:text-zinc-300">password</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-zinc-500 dark:text-zinc-500 font-mono tracking-wide">
                SPEEDMONITOR &copy; 2026
            </p>
        </motion.div>
    </div>
</template>
