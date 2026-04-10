<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Shows quick action cards for navigation and profile summary.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 */

import { Link } from '@inertiajs/vue3';
import { BarChart3, ClipboardList, Mail, Route, Shield, User } from '@lucide/vue';

import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <EmployeeLayout title="Dasbor">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ============================================================
                Page Header
            ============================================================ -->
            <div class="mb-6">
                <h1
                    class="text-3xl font-bold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Dasbor
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Selamat datang, {{ authStore.user?.name }}!
                </p>
            </div>

            <!-- ============================================================
                Quick Actions Grid
            ============================================================ -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Speedometer Card -->
                <Link
                    href="/employee/speedometer"
                    class="group block rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 transition-all duration-200 hover:-translate-y-1 hover:border-blue-500/50 hover:shadow-xl dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5 dark:hover:shadow-blue-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 shadow-lg shadow-zinc-200 dark:from-blue-500 dark:to-indigo-600 dark:shadow-blue-500/25"
                        >
                            <Route :size="28" :stroke-width="2" class="text-white" />
                        </div>
                        <div class="flex-1">
                            <h3 class="mb-1 text-lg font-semibold text-zinc-900 transition-colors duration-200 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                Mulai Speedometer
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Lacak kecepatan dan jarak perjalanan Anda secara real-time
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Trip History Card -->
                <Link
                    href="/employee/my-trips"
                    class="group block rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 transition-all duration-200 hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-xl dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5 dark:hover:shadow-cyan-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-teal-700 shadow-lg shadow-zinc-200 dark:from-cyan-500 dark:to-teal-600 dark:shadow-cyan-500/25"
                        >
                            <ClipboardList :size="28" :stroke-width="2" class="text-white" />
                        </div>
                        <div class="flex-1">
                            <h3 class="mb-1 text-lg font-semibold text-zinc-900 transition-colors duration-200 group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-400">
                                Riwayat Perjalanan
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Lihat perjalanan sebelumnya dan statistik detail
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Statistics Card -->
                <Link
                    href="/employee/statistics"
                    class="group block rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 transition-all duration-200 hover:-translate-y-1 hover:border-purple-500/50 hover:shadow-xl dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5 dark:hover:shadow-purple-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-pink-700 shadow-lg shadow-zinc-200 dark:from-purple-500 dark:to-pink-600 dark:shadow-purple-500/25"
                        >
                            <BarChart3 :size="28" :stroke-width="2" class="text-white" />
                        </div>
                        <div class="flex-1">
                            <h3 class="mb-1 text-lg font-semibold text-zinc-900 transition-colors duration-200 group-hover:text-purple-600 dark:text-white dark:group-hover:text-purple-400">
                                Statistik Saya
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Pantau performa dan metrik berkendara Anda
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- ============================================================
                Profile Info Card
            ============================================================ -->
            <div class="mt-8 rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    Profil Anda
                </h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-lg bg-zinc-100/90 p-4 dark:bg-zinc-900/50">
                        <User :size="18" :stroke-width="2" class="shrink-0 text-zinc-500 dark:text-zinc-400" aria-hidden="true" />
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Nama</p>
                            <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ authStore.user?.name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-zinc-100/90 p-4 dark:bg-zinc-900/50">
                        <Mail :size="18" :stroke-width="2" class="shrink-0 text-zinc-500 dark:text-zinc-400" aria-hidden="true" />
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Email</p>
                            <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ authStore.user?.email }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-zinc-100/90 p-4 dark:bg-zinc-900/50">
                        <Shield :size="18" :stroke-width="2" class="shrink-0 text-zinc-500 dark:text-zinc-400" aria-hidden="true" />
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Peran</p>
                            <p class="mt-0.5">
                                <span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-xs font-medium text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300">
                                    {{ authStore.user?.role }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>
