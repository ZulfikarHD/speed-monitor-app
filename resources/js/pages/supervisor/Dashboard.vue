<script setup lang="ts">
/**
 * Supervisor Dashboard Page (Redesigned)
 *
 * Enhanced dashboard with comprehensive employee monitoring, trend analysis,
 * quick actions, and real-time alerts for supervisors and admins.
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
 * - motion-v animations
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import ActiveTripsTable from '@/components/dashboard/ActiveTripsTable.vue';
import AlertsWidget from '@/components/dashboard/AlertsWidget.vue';
import DateRangeFilter from '@/components/dashboard/DateRangeFilter.vue';
import EmployeeSummaryWidget from '@/components/dashboard/EmployeeSummaryWidget.vue';
import QuickActionsBar from '@/components/dashboard/QuickActionsBar.vue';
import RecentViolationsList from '@/components/dashboard/RecentViolationsList.vue';
import TrendStatCard from '@/components/dashboard/TrendStatCard.vue';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import type { DashboardOverview } from '@/types/dashboard';
import { index as tripsIndex } from '@/actions/App/Http/Controllers/Supervisor/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Supervisor/DashboardController';

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
    // Initial data fetch
    await fetchDashboardData();

    // Setup auto-refresh every 30 seconds
    refreshIntervalId = window.setInterval(async () => {
        await fetchDashboardData();
        countdown.value = 30; // Reset countdown
    }, REFRESH_INTERVAL);

    // Setup countdown timer (updates every second)
    countdownIntervalId = window.setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        }
    }, 1000);
});

onBeforeUnmount(() => {
    // Cleanup intervals to prevent memory leaks
    if (refreshIntervalId !== null) {
        clearInterval(refreshIntervalId);
    }

    if (countdownIntervalId !== null) {
        clearInterval(countdownIntervalId);
    }
});

// Watch for date range changes and refetch data
watch(selectedDateRange, async () => {
    isLoading.value = true;
    await fetchDashboardData();
    countdown.value = 30; // Reset countdown
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Get total trips today.
 */
const totalTripsToday = computed(() => {
    return dashboardData.value?.today_summary.total_trips ?? 0;
});

/**
 * Get violations count today.
 */
const violationsCount = computed(() => {
    return dashboardData.value?.today_summary.violations_count ?? 0;
});

/**
 * Get active trips count.
 */
const activeTripsCount = computed(() => {
    return dashboardData.value?.active_trips.length ?? 0;
});

/**
 * Get average speed.
 */
const averageSpeed = computed(() => {
    return dashboardData.value?.average_speed ?? 0;
});

/**
 * Get trend indicators.
 */
const trends = computed(() => {
    return dashboardData.value?.trends ?? { trips_change: 0, violations_change: 0 };
});

/**
 * Get employee summary.
 */
const employeeSummary = computed(() => {
    return (
        dashboardData.value?.employee_summary ?? {
            total_employees: 0,
            active_today: 0,
            top_performer: null,
        }
    );
});

/**
 * Get recent alerts.
 */
const recentAlerts = computed(() => {
    return dashboardData.value?.recent_alerts ?? [];
});

/**
 * Format last updated time.
 */
const lastUpdatedText = computed(() => {
    if (!lastUpdated.value) {
        return 'Never';
    }

    const now = new Date();
    const diff = Math.floor((now.getTime() - lastUpdated.value.getTime()) / 1000);

    if (diff < 60) {
        return `${diff}s ago`;
    }

    if (diff < 3600) {
        return `${Math.floor(diff / 60)}m ago`;
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
 * Uses Wayfinder for type-safe route access and handles loading/error states.
 * Data is cached on backend for 5 minutes per date range combination.
 */
async function fetchDashboardData(): Promise<void> {
    try {
        error.value = null;

        // Build URL with date_range query parameter
        const url = new URL('/api/dashboard/overview', window.location.origin);
        url.searchParams.set('date_range', selectedDateRange.value);

        // Fetch from API endpoint
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
 * Resets countdown timer after successful refresh.
 */
async function handleManualRefresh(): Promise<void> {
    isLoading.value = true;
    await fetchDashboardData();
    countdown.value = 30; // Reset countdown
}
</script>

<template>
    <SupervisorLayout title="Dashboard Overview">
        <div class="min-h-screen bg-[#0a0c0f] p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header Section with Date Filter -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <!-- Title -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-[#EDEDEC] md:text-4xl">
                            Dashboard Overview
                        </h1>
                        <p class="mt-1 text-sm text-[#A1A09A]">
                            Real-time monitoring of employee trip compliance
                        </p>
                    </div>

                    <!-- Right Section: Date Filter + Refresh Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Date Range Filter -->
                        <DateRangeFilter v-model="selectedDateRange" />

                        <!-- Last Updated -->
                        <div class="text-sm text-[#A1A09A]">Updated: {{ lastUpdatedText }}</div>

                        <!-- Auto-refresh Badge -->
                        <motion.div
                            :animate="{ scale: countdown <= 5 ? [1, 1.05, 1] : 1 }"
                            :transition="{ duration: 0.5, repeat: countdown <= 5 ? Infinity : 0 }"
                            class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-medium text-cyan-400"
                        >
                            Next in {{ countdown }}s
                        </motion.div>

                        <!-- Manual Refresh Button -->
                        <motion.button
                            :disabled="isLoading"
                            :whileHover="{ scale: 1.05, rotate: 180 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#3E3E3A] text-[#EDEDEC] transition-colors hover:bg-[#4a4a46] disabled:cursor-not-allowed disabled:opacity-50"
                            :aria-label="'Refresh dashboard'"
                            @click="handleManualRefresh"
                        >
                            <span class="text-lg" aria-hidden="true">🔄</span>
                        </motion.button>
                    </div>
                </motion.div>

                <!-- Error State -->
                <motion.div
                    v-if="error && !isLoading"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.4 }"
                    class="rounded-lg border border-red-500/30 bg-red-500/10 p-6"
                >
                    <div class="flex items-start gap-3">
                        <span class="text-2xl" aria-hidden="true">⚠️</span>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-400">Failed to Load Dashboard Data</h3>
                            <p class="mt-1 text-sm text-red-300">
                                {{ error }}
                            </p>
                            <button
                                class="mt-3 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-400 transition-colors hover:bg-red-500/30"
                                @click="handleManualRefresh"
                            >
                                Try Again
                            </button>
                        </div>
                    </div>
                </motion.div>

                <!-- Quick Actions Bar -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.4 }"
                >
                    <QuickActionsBar />
                </motion.div>

                <!-- Trend Stats Cards Grid -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Active Trips Trend -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.2, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <TrendStatCard
                            title="Active Trips"
                            :value="activeTripsCount"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                        />
                    </motion.div>

                    <!-- Total Trips Trend -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.3, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <TrendStatCard
                            title="Total Trips Today"
                            :value="totalTripsToday"
                            :trend-percentage="trends.trips_change"
                            :is-loading="isLoading"
                        />
                    </motion.div>

                    <!-- Violations Trend -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.4, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <TrendStatCard
                            title="Violations Today"
                            :value="violationsCount"
                            :trend-percentage="trends.violations_change"
                            :is-loading="isLoading"
                        />
                    </motion.div>

                    <!-- Average Speed -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.5, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <TrendStatCard
                            title="Average Speed"
                            :value="`${averageSpeed} km/h`"
                            :trend-percentage="0"
                            :is-loading="isLoading"
                        />
                    </motion.div>
                </div>

                <!-- Employee Summary Widget -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.6, duration: 0.4 }"
                >
                    <EmployeeSummaryWidget :summary="employeeSummary" :is-loading="isLoading" />
                </motion.div>

                <!-- Two Column Layout: Active Trips + (Violations + Alerts) -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Left Column: Active Trips Table -->
                    <motion.div
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ delay: 0.7, duration: 0.5 }"
                    >
                        <ActiveTripsTable
                            :trips="dashboardData?.active_trips ?? []"
                            :is-loading="isLoading"
                        >
                            <template #actions>
                                <Link
                                    :href="tripsIndex.url()"
                                    class="text-sm font-medium text-cyan-400 transition-colors hover:text-cyan-300"
                                >
                                    View All →
                                </Link>
                            </template>
                        </ActiveTripsTable>
                    </motion.div>

                    <!-- Right Column: Violations + Alerts -->
                    <div class="space-y-6">
                        <!-- Recent Violations List -->
                        <motion.div
                            :initial="{ opacity: 0, x: 20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ delay: 0.8, duration: 0.5 }"
                        >
                            <RecentViolationsList
                                :violators="dashboardData?.top_violators ?? []"
                                :is-loading="isLoading"
                            >
                                <template #actions>
                                    <Link
                                        :href="leaderboardIndex.url()"
                                        class="text-sm font-medium text-cyan-400 transition-colors hover:text-cyan-300"
                                    >
                                        View All →
                                    </Link>
                                </template>
                            </RecentViolationsList>
                        </motion.div>

                        <!-- Recent Alerts Widget -->
                        <motion.div
                            :initial="{ opacity: 0, x: 20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ delay: 0.9, duration: 0.5 }"
                        >
                            <AlertsWidget :alerts="recentAlerts" :is-loading="isLoading" />
                        </motion.div>
                    </div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
