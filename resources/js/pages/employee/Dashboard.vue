<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Main dashboard showing quick actions and profile information.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Quick action cards for Speedometer, Trip History, and Statistics
 * - User profile information display
 * - Full theme support (light/dark)
 * - SVG icons following design system
 * - Motion animations for interactivity
 * - Responsive grid layout
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import { IconGauge, IconClipboard, IconChart, IconUser } from '@/components/icons';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <EmployeeLayout title="Dashboard">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ================================================================
                Page Header
            ================================================================ -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-6"
            >
                <h1
                    class="text-3xl font-bold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Welcome back, {{ authStore.user?.name }}!
                </p>
            </motion.div>

            <!-- ================================================================
                Quick Actions Grid
            ================================================================ -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Speedometer Card -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                >
                    <Link
                        href="/employee/speedometer"
                        class="group block rounded-lg border border-zinc-200 bg-white backdrop-blur-sm transition-all hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-lg hover:shadow-zinc-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:border-white/5 dark:bg-zinc-800/50 dark:hover:shadow-cyan-500/10 dark:focus:ring-offset-zinc-900"
                    >
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div
                                    class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-blue-700 text-white shadow-lg shadow-cyan-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25"
                                >
                                    <IconGauge :size="28" />
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <h3
                                        class="mb-1 text-lg font-semibold text-zinc-900 transition-colors group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-400"
                                    >
                                        Start Speedometer
                                    </h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Track your trip speed and distance in real-time
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </motion.div>

                <!-- Trip History Card -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
                >
                    <Link
                        href="/employee/my-trips"
                        class="group block rounded-lg border border-zinc-200 bg-white backdrop-blur-sm transition-all hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-lg hover:shadow-zinc-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:border-white/5 dark:bg-zinc-800/50 dark:hover:shadow-cyan-500/10 dark:focus:ring-offset-zinc-900"
                    >
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div
                                    class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-blue-700 text-white shadow-lg shadow-cyan-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25"
                                >
                                    <IconClipboard :size="28" />
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <h3
                                        class="mb-1 text-lg font-semibold text-zinc-900 transition-colors group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-400"
                                    >
                                        Trip History
                                    </h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        View your past trips and detailed statistics
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </motion.div>

                <!-- Statistics Card -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.3 }"
                >
                    <Link
                        href="/employee/statistics"
                        class="group block rounded-lg border border-zinc-200 bg-white backdrop-blur-sm transition-all hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-lg hover:shadow-zinc-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:border-white/5 dark:bg-zinc-800/50 dark:hover:shadow-cyan-500/10 dark:focus:ring-offset-zinc-900"
                    >
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div
                                    class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-blue-700 text-white shadow-lg shadow-cyan-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25"
                                >
                                    <IconChart :size="28" />
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <h3
                                        class="mb-1 text-lg font-semibold text-zinc-900 transition-colors group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-400"
                                    >
                                        My Statistics
                                    </h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Track your performance and driving metrics
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </motion.div>
            </div>

            <!-- ================================================================
                Profile Info Card
            ================================================================ -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.4 }"
                class="mt-8 rounded-lg border border-zinc-200 bg-white backdrop-blur-sm p-6 dark:border-white/5 dark:bg-zinc-800/50"
            >
                <div class="mb-4 flex items-center gap-2">
                    <IconUser :size="20" class="text-zinc-700 dark:text-zinc-300" />
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        Your Profile
                    </h2>
                </div>
                
                <div class="grid gap-3 sm:grid-cols-2">
                    <!-- Name -->
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-500">
                            Name
                        </p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ authStore.user?.name }}
                        </p>
                    </div>
                    
                    <!-- Email -->
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-500">
                            Email
                        </p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ authStore.user?.email }}
                        </p>
                    </div>
                    
                    <!-- Role -->
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-500">
                            Role
                        </p>
                        <p class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-xs font-medium text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300"
                            >
                                {{ authStore.user?.role }}
                            </span>
                        </p>
                    </div>
                </div>
            </motion.div>
        </div>
    </EmployeeLayout>
</template>
