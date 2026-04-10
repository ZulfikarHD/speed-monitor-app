<script setup lang="ts">
/**
 * Admin Dashboard - Central overview page for admin role.
 *
 * Provides user profile overview, quick navigation to admin features,
 * and system development status. Uses SuperuserLayout for consistent
 * navigation across all superuser/admin pages.
 *
 * Features:
 * - Profile card with avatar and role badge
 * - Quick action navigation cards
 * - Development status notice
 * - Full light/dark mode (extra dark base)
 * - Lucide SVG icons (no custom icon components)
 * - Minimal motion-v entry animations
 *
 * UX Principles:
 * - Jakob's Law: Familiar admin dashboard card layout
 * - Hick's Law: Two clear quick action choices
 * - Fitts's Law: Large touch targets (>=44px)
 * - Law of Proximity: Related info grouped in cards
 * - Aesthetic-Usability Effect: Clean, polished design
 *
 * @example Route: /admin/dashboard (admin role only)
 */

import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    Construction,
    LogOut,
    Mail,
    Settings,
    Shield,
    User,
} from '@lucide/vue';
import { motion } from 'motion-v';

import { useAuth } from '@/composables/useAuth';
import SuperuserLayout from '@/layouts/SuperuserLayout.vue';
import { useAuthStore } from '@/stores/auth';

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const { handleLogout, isLoading } = useAuth();
</script>

<template>
    <SuperuserLayout title="Dashboard Admin">
        <div class="p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-5xl space-y-6">
                <!-- Header Section -->
                <motion.div
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25 }"
                >
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white md:text-3xl">
                        Dashboard Admin
                    </h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Kelola sistem dan konfigurasi aplikasi
                    </p>
                </motion.div>

                <!-- Main Grid: Profile + Quick Actions -->
                <div class="grid gap-6 md:grid-cols-5">
                    <!-- Profile Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.25, delay: 0.05 }"
                        class="rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-6 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 md:col-span-2"
                    >
                        <!-- Avatar + Name -->
                        <div class="mb-5 flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-full bg-linear-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25"
                            >
                                <User
                                    :size="22"
                                    class="text-white"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                    {{ authStore.user?.name }}
                                </h2>
                                <span
                                    class="mt-1 inline-flex items-center gap-1.5 rounded-full border border-purple-200 dark:border-purple-500/20 bg-purple-100 dark:bg-purple-500/15 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-300"
                                >
                                    <Shield :size="12" />
                                    {{ authStore.user?.role }}
                                </span>
                            </div>
                        </div>

                        <!-- Profile Detail -->
                        <div class="mb-5 space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <Mail
                                    :size="16"
                                    class="shrink-0 text-zinc-400 dark:text-zinc-500"
                                />
                                <span class="truncate text-zinc-600 dark:text-zinc-400">
                                    {{ authStore.user?.email }}
                                </span>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <button
                            :disabled="isLoading"
                            class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-900"
                            @click="handleLogout"
                        >
                            <LogOut :size="16" />
                            <span>{{ isLoading ? 'Keluar...' : 'Keluar' }}</span>
                        </button>
                    </motion.div>

                    <!-- Quick Actions -->
                    <motion.div
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.25, delay: 0.1 }"
                        class="space-y-4 md:col-span-3"
                    >
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                            Aksi Cepat
                        </h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Settings Action Card -->
                            <Link
                                href="/admin/settings"
                                class="group rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-100 dark:hover:border-cyan-500/30 dark:hover:shadow-cyan-500/10"
                                aria-label="Buka Pengaturan Aplikasi"
                            >
                                <div class="mb-3 flex items-start justify-between">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-lg border border-cyan-200 dark:border-cyan-500/20 bg-cyan-100 dark:bg-cyan-500/15"
                                    >
                                        <Settings
                                            :size="20"
                                            class="text-cyan-600 dark:text-cyan-400"
                                        />
                                    </div>
                                    <ArrowRight
                                        :size="16"
                                        class="text-zinc-400 dark:text-zinc-600 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-cyan-500 dark:group-hover:text-cyan-400"
                                    />
                                </div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Pengaturan Aplikasi
                                </h4>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Konfigurasi batas kecepatan, auto-stop, dan interval logging
                                </p>
                            </Link>

                            <!-- Superuser Dashboard Action Card -->
                            <Link
                                href="/superuser/dashboard"
                                class="group rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-100 dark:hover:border-blue-500/30 dark:hover:shadow-blue-500/10"
                                aria-label="Buka Superuser Dashboard"
                            >
                                <div class="mb-3 flex items-start justify-between">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-lg border border-blue-200 dark:border-blue-500/20 bg-blue-100 dark:bg-blue-500/15"
                                    >
                                        <BarChart3
                                            :size="20"
                                            class="text-blue-600 dark:text-blue-400"
                                        />
                                    </div>
                                    <ArrowRight
                                        :size="16"
                                        class="text-zinc-400 dark:text-zinc-600 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-blue-500 dark:group-hover:text-blue-400"
                                    />
                                </div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Dashboard Superuser
                                </h4>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Monitoring perjalanan, statistik, dan data karyawan
                                </p>
                            </Link>
                        </div>
                    </motion.div>
                </div>

                <!-- Development Notice Banner -->
                <motion.div
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25, delay: 0.15 }"
                    class="rounded-lg border border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-4"
                >
                    <div class="flex gap-3">
                        <Construction
                            :size="20"
                            class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                Dalam Pengembangan
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-300/80">
                                Fitur user management dan dashboard analytics akan tersedia di Sprint 6.
                                Saat ini, gunakan menu Pengaturan untuk konfigurasi sistem.
                            </p>
                        </div>
                    </div>
                </motion.div>
            </div>
        </div>
    </SuperuserLayout>
</template>
