<script setup lang="ts">
/**
 * Admin Dashboard - Landing page for admin role.
 *
 * Modern redesigned dashboard with theme-aware styling following SpeedMonitor
 * design system conventions.
 *
 * Features:
 * - Full light/dark mode support with zinc color palette
 * - SVG icons (no emojis)
 * - Smooth 200-300ms transitions
 * - Professional glassmorphism effects
 * - Tech-inspired grid backgrounds
 */

import { Head } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import UpdateNotification from '@/components/common/UpdateNotification.vue';
import IconLogout from '@/components/icons/IconLogout.vue';
import { useAuth } from '@/composables/useAuth';
import { useServiceWorker } from '@/composables/useServiceWorker';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const { handleLogout, isLoading } = useAuth();

/**
 * Handle Service Worker update.
 *
 * Applies pending SW update and reloads the page.
 */
const { hasUpdate, applyUpdate } = useServiceWorker();

const handleUpdate = async (): Promise<void> => {
    try {
        await applyUpdate();
    } catch (error) {
        console.error('[Admin Dashboard] Failed to apply update:', error);
    }
};

const handleDismiss = (): void => {
    // Update notification dismissed
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <!-- Page Container -->
    <div class="relative min-h-screen bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black">
        <!-- Tech Grid Background -->
        <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>

        <!-- Radial Gradient Overlay -->
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(6,182,212,0.05),transparent_40%)] dark:bg-[radial-gradient(ellipse_at_top_right,rgba(6,182,212,0.08),transparent_50%)]"></div>

        <!-- Content -->
        <div class="relative z-10 p-6">
            <div class="mx-auto max-w-6xl lg:max-w-7xl">
                <!-- Main Card -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3 }"
                    class="overflow-hidden rounded-lg bg-white/90 dark:bg-zinc-900/98 backdrop-blur-xl border border-zinc-200 dark:border-white/5 p-6 sm:p-8 lg:p-10 shadow-lg shadow-zinc-200 dark:shadow-none"
                >
                    <!-- Header Section -->
                    <motion.div
                        :initial="{ opacity: 0, y: -10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1, duration: 0.2 }"
                        class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h1 class="text-3xl font-semibold text-zinc-900 dark:text-white sm:text-4xl">
                                Admin Dashboard
                            </h1>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Welcome back, {{ authStore.user?.name }}!
                            </p>
                        </div>

                        <!-- Logout Button -->
                        <motion.button
                            @click="handleLogout"
                            :disabled="isLoading"
                            :whileHover="{ scale: 1.02, y: -1 }"
                            :whilePress="{ scale: 0.98 }"
                            :transition="{ duration: 0.2 }"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-6 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                        >
                            <IconLogout :size="18" />
                            <span>{{ isLoading ? 'Logging out...' : 'Logout' }}</span>
                        </motion.button>
                    </motion.div>

                    <!-- Profile Section -->
                    <motion.div
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.2, delay: 0.2 }"
                        class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-800/50 p-6"
                    >
                        <div class="mb-4">
                            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                                Your Profile
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Name:</span>
                                    <span class="ml-2 text-zinc-600 dark:text-zinc-400">{{ authStore.user?.name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Email:</span>
                                    <span class="ml-2 text-zinc-600 dark:text-zinc-400">{{ authStore.user?.email }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Role:</span>
                                    <span class="ml-2 inline-flex items-center rounded-full bg-purple-500/20 dark:bg-purple-500/15 px-3 py-1 text-xs font-medium text-purple-700 dark:text-purple-300 border border-purple-500/30">
                                        {{ authStore.user?.role }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Banner -->
                        <motion.div
                            :initial="{ opacity: 0, scale: 0.95 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :transition="{ delay: 0.3, duration: 0.2 }"
                            class="rounded-lg border border-amber-500/30 dark:border-amber-500/30 bg-amber-100 dark:bg-amber-500/10 p-4"
                        >
                            <p class="text-sm text-amber-800 dark:text-amber-300">
                                This is a placeholder dashboard. The user management and settings features will be implemented in Sprint 6.
                            </p>
                        </motion.div>
                    </motion.div>
                </motion.div>
            </div>
        </div>

        <!-- Service Worker Update Notification -->
        <UpdateNotification
            :show="hasUpdate"
            @update="handleUpdate"
            @dismiss="handleDismiss"
        />
    </div>
</template>
