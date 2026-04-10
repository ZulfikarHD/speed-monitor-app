<script setup lang="ts">
/**
 * Superuser Dashboard Page
 *
 * Central monitoring hub for superusers with real-time employee tracking,
 * trend analysis, quick actions, and violation alerts.
 *
 * Features:
 * - Quick action buttons for common tasks
 * - Trend stat cards with day-over-day comparison
 * - Employee summary widget with top performer
 * - Recent alerts for high-violation trips
 * - Active trips table with live updates
 * - Recent violations leaderboard
 * - Auto-refresh every 30 seconds
 * - Date range filter (UI-only in v1)
 * - Responsive two-column layout
 * - Lightweight opacity/y animations
 * - Full light/dark theme support
 */

import { Link } from '@inertiajs/vue3';
import { AlertTriangle, RefreshCw } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import { index as tripsIndex } from '@/actions/App/Http/Controllers/Superuser/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Superuser/DashboardController';
import ActiveTripsTable from '@/components/dashboard/ActiveTripsTable.vue';
import AlertsWidget from '@/components/dashboard/AlertsWidget.vue';
import DateRangeFilter from '@/components/dashboard/DateRangeFilter.vue';
import EmployeeSummaryWidget from '@/components/dashboard/EmployeeSummaryWidget.vue';
import QuickActionsBar from '@/components/dashboard/QuickActionsBar.vue';
import RecentViolationsList from '@/components/dashboard/RecentViolationsList.vue';
import TrendStatCard from '@/components/dashboard/TrendStatCard.vue';
import SuperuserLayout from '@/layouts/SuperuserLayout.vue';
import type { DashboardOverview } from '@/types/dashboard';

// ========================================================================
// Local State
// ========================================================================

/** Dashboard data from API */
const dashboardData = ref<DashboardOverview | null>(null);

/** Loading state */
const isLoading = ref(true);

/** Error state */
const error = ref<string | null>(null);

/** Countdown for next refresh (in seconds) */
const countdown = ref(30);

/** Last updated timestamp */
const lastUpdated = ref<Date | null>(null);

/** Selected date range (UI-only for v1) */
const selectedDateRange = ref<'today' | 'week' | 'month'>('today');

// ========================================================================
// Constants
// ========================================================================

/** Refresh interval in milliseconds (30 seconds) */
const REFRESH_INTERVAL = 30_000;

/** Interval IDs for cleanup */
let refreshIntervalId: number | null = null;
let countdownIntervalId: number | null = null;

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(async () => {
    await fetchDashboardData();

    refreshIntervalId = window.setInterval(async () => {
        await fetchDashboardData();
        countdown.value = 30;
    }, REFRESH_INTERVAL);

    countdownIntervalId = window.setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        }
    }, 1000);
});

onBeforeUnmount(() => {
    if (refreshIntervalId !== null) {
        clearInterval(refreshIntervalId);
    }

    if (countdownIntervalId !== null) {
        clearInterval(countdownIntervalId);
    }
});

watch(selectedDateRange, async () => {
    isLoading.value = true;
    await fetchDashboardData();
    countdown.value = 30;
});

// ========================================================================
// Computed
// ========================================================================

/** Get total trips today. */
const totalTripsToday = computed(() => {
    return dashboardData.value?.today_summary.total_trips ?? 0;
});

/** Get violations count today. */
const violationsCount = computed(() => {
    return dashboardData.value?.today_summary.violations_count ?? 0;
});

/** Get active trips count. */
const activeTripsCount = computed(() => {
    return dashboardData.value?.active_trips.length ?? 0;
});

/** Get average speed. */
const averageSpeed = computed(() => {
    return dashboardData.value?.average_speed ?? 0;
});

/** Get average speed for mobil. */
const averageSpeedMobil = computed(() => {
    return dashboardData.value?.average_speed_mobil ?? 0;
});

/** Get average speed for motor. */
const averageSpeedMotor = computed(() => {
    return dashboardData.value?.average_speed_motor ?? 0;
});

/** Get trend indicators. */
const trends = computed(() => {
    return dashboardData.value?.trends ?? { trips_change: 0, violations_change: 0 };
});

/** Get employee summary. */
const employeeSummary = computed(() => {
    return (
        dashboardData.value?.employee_summary ?? {
            total_employees: 0,
            active_today: 0,
            top_performer: null,
        }
    );
});

/** Get recent alerts. */
const recentAlerts = computed(() => {
    return dashboardData.value?.recent_alerts ?? [];
});

/** Format last updated time. */
const lastUpdatedText = computed(() => {
    if (!lastUpdated.value) {
        return 'Belum pernah';
    }

    const now = new Date();
    const diff = Math.floor((now.getTime() - lastUpdated.value.getTime()) / 1000);

    if (diff < 60) {
        return `${diff} detik lalu`;
    }

    if (diff < 3600) {
        return `${Math.floor(diff / 60)} menit lalu`;
    }

    return lastUpdated.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Fetch dashboard data from API.
 *
 * WHY: Uses native fetch instead of Inertia to allow background refresh
 * without full page re-render. Data is cached on backend for 5 minutes.
 */
async function fetchDashboardData(): Promise<void> {
    try {
        error.value = null;

        const url = new URL('/api/dashboard/overview', window.location.origin);
        url.searchParams.set('date_range', selectedDateRange.value);

        const response = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        dashboardData.value = data;
        lastUpdated.value = new Date();
        isLoading.value = false;
    } catch (err) {
        console.error('[Dashboard] Failed to fetch data:', err);
        error.value = err instanceof Error ? err.message : 'Failed to load dashboard data';
        isLoading.value = false;
    }
}

/**
 * Manual refresh handler.
 *
 * Allows user to force refresh instead of waiting for auto-refresh.
 */
async function handleManualRefresh(): Promise<void> {
    isLoading.value = true;
    await fetchDashboardData();
    countdown.value = 30;
}
</script>

<template>
    <SuperuserLayout title="Ringkasan Dasbor">
        <div class="min-h-screen p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header Section with Date Filter -->
                <motion.div
                    :initial="{ opacity: 0, y: -12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3 }"
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <!-- Title -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white md:text-4xl">
                            Ringkasan Dasbor
                        </h1>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Pemantauan kepatuhan perjalanan karyawan secara real-time
                        </p>
                    </div>

                    <!-- Right Section: Refresh Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Last Updated -->
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Diperbarui: {{ lastUpdatedText }}</div>

                        <!-- Auto-refresh Badge -->
                        <div
                            class="rounded-full border border-cyan-500/20 bg-cyan-500/10 dark:bg-cyan-500/15 px-3 py-1 text-xs font-medium text-cyan-600 dark:text-cyan-400"
                        >
                            Berikutnya {{ countdown }}s
                        </div>

                        <!-- Manual Refresh Button -->
                        <button
                            :disabled="isLoading"
                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-white transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:cursor-not-allowed disabled:opacity-50"
                            aria-label="Segarkan dasbor"
                            @click="handleManualRefresh"
                        >
                            <RefreshCw :size="18" :stroke-width="2" aria-hidden="true" />
                        </button>
                    </div>
                </motion.div>

                <!-- Error State -->
                <motion.div
                    v-if="error && !isLoading"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ duration: 0.3 }"
                    class="rounded-lg border border-red-500/30 bg-red-100 dark:bg-red-500/10 p-6"
                >
                    <div class="flex items-start gap-3">
                        <AlertTriangle :size="24" :stroke-width="2" class="text-red-600 dark:text-red-400" aria-hidden="true" />
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-800 dark:text-red-400">Gagal Memuat Data Dasbor</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                {{ error }}
                            </p>
                            <button
                                class="mt-3 rounded-lg border border-red-500/30 bg-red-500/20 px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 transition-colors duration-200 hover:bg-red-500/30"
                                @click="handleManualRefresh"
                            >
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </motion.div>

                <!-- Quick Actions Bar -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.05, duration: 0.3 }"
                >
                    <QuickActionsBar />
                </motion.div>

                <!-- Date Range Filter -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.08, duration: 0.3 }"
                >
                    <DateRangeFilter v-model="selectedDateRange" />
                </motion.div>

                <!-- Trend Stats Cards Grid -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Trip Aktif"
                            :value="activeTripsCount"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                            color="blue"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.15, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Total Perjalanan Hari Ini"
                            :value="totalTripsToday"
                            :trend-percentage="trends.trips_change"
                            :is-loading="isLoading"
                            color="green"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.2, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Pelanggaran Hari Ini"
                            :value="violationsCount"
                            :trend-percentage="trends.violations_change"
                            :is-loading="isLoading"
                            color="red"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.25, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Kecepatan Rata-rata"
                            :value="`${averageSpeed} km/h`"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                            color="cyan"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.3, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Rata-Rata Kec. Mobil"
                            :value="`${averageSpeedMobil} km/h`"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                            color="orange"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.35, duration: 0.3 }"
                    >
                        <TrendStatCard
                            title="Rata-Rata Kec. Motor"
                            :value="`${averageSpeedMotor} km/h`"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                            color="purple"
                        />
                    </motion.div>
                </div>

                <!-- Employee Summary Widget -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3, duration: 0.3 }"
                >
                    <EmployeeSummaryWidget :summary="employeeSummary" :is-loading="isLoading" />
                </motion.div>

                <!-- Two Column Layout: Active Trips + (Violations + Alerts) -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Left Column: Active Trips Table -->
                    <motion.div
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.35, duration: 0.3 }"
                    >
                        <ActiveTripsTable
                            :trips="dashboardData?.active_trips ?? []"
                            :is-loading="isLoading"
                        >
                            <template #actions>
                                <Link
                                    :href="tripsIndex.url()"
                                    class="text-sm font-medium text-cyan-600 dark:text-cyan-400 transition-colors duration-200 hover:text-cyan-700 dark:hover:text-cyan-300"
                                >
                                    Lihat Semua →
                                </Link>
                            </template>
                        </ActiveTripsTable>
                    </motion.div>

                    <!-- Right Column: Violations + Alerts -->
                    <div class="space-y-6">
                        <motion.div
                            :initial="{ opacity: 0, y: 12 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.4, duration: 0.3 }"
                        >
                            <RecentViolationsList
                                :violators="dashboardData?.top_violators ?? []"
                                :is-loading="isLoading"
                            >
                                <template #actions>
                                    <Link
                                        :href="leaderboardIndex.url()"
                                        class="text-sm font-medium text-cyan-600 dark:text-cyan-400 transition-colors duration-200 hover:text-cyan-700 dark:hover:text-cyan-300"
                                    >
                                    Lihat Semua →
                                </Link>
                            </template>
                        </RecentViolationsList>
                        </motion.div>

                        <motion.div
                            :initial="{ opacity: 0, y: 12 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.45, duration: 0.3 }"
                        >
                            <AlertsWidget :alerts="recentAlerts" :is-loading="isLoading" />
                        </motion.div>
                    </div>
                </div>
            </div>
        </div>
    </SuperuserLayout>
</template>
