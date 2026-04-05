<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Main dashboard showing quick actions and profile information.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import { IconCar, IconChart, IconClipboard } from '@/components/icons';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <EmployeeLayout title="Dashboard">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ============================================================
                Page Header
            ============================================================ -->
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
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Welcome back, {{ authStore.user?.name }}!
                </p>
            </motion.div>

            <!-- ============================================================
                Quick Actions Grid
            ============================================================ -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Speedometer Card -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                >
                    <Link
                        href="/employee/speedometer"
                        class="group block rounded-lg border border-zinc-200 dark:border-white/5 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 dark:from-blue-500/10 dark:to-indigo-500/10 backdrop-blur-sm p-6 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 hover:border-blue-500/50 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-blue-500/20"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 dark:from-blue-500 dark:to-indigo-600 shadow-lg shadow-zinc-200 dark:shadow-blue-500/25"
                            >
                                <IconCar :size="28" class="text-white" />
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200"
                                >
                                    Start Speedometer
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Track your trip speed and distance in real-time
                                </p>
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
                        class="group block rounded-lg border border-zinc-200 dark:border-white/5 bg-gradient-to-br from-cyan-500/10 to-teal-500/10 dark:from-cyan-500/10 dark:to-teal-500/10 backdrop-blur-sm p-6 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-cyan-500/20"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-teal-700 dark:from-cyan-500 dark:to-teal-600 shadow-lg shadow-zinc-200 dark:shadow-cyan-500/25"
                            >
                                <IconClipboard :size="28" class="text-white" />
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors duration-200"
                                >
                                    Trip History
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    View your past trips and detailed statistics
                                </p>
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
                        class="group block rounded-lg border border-zinc-200 dark:border-white/5 bg-gradient-to-br from-purple-500/10 to-pink-500/10 dark:from-purple-500/10 dark:to-pink-500/10 backdrop-blur-sm p-6 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 hover:border-purple-500/50 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-purple-500/20"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-pink-700 dark:from-purple-500 dark:to-pink-600 shadow-lg shadow-zinc-200 dark:shadow-purple-500/25"
                            >
                                <IconChart :size="28" class="text-white" />
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200"
                                >
                                    My Statistics
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Track your performance and driving metrics
                                </p>
                            </div>
                        </div>
                    </Link>
                </motion.div>
            </div>

            <!-- ============================================================
                Profile Info Card
            ============================================================ -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.4 }"
                class="mt-8 rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-6"
            >
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    Your Profile
                </h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Name</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ authStore.user?.name }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Email</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ authStore.user?.email }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Role</p>
                        <p class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full bg-cyan-100 dark:bg-cyan-500/15 px-3 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
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
