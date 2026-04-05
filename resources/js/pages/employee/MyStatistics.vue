<script setup lang="ts">
/**
 * My Statistics Page - Employee personal performance metrics.
 *
 * Displays summary statistics and charts for trips and violations over different time periods.
 * Data is passed from StatisticsController via Inertia props for optimal performance.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Period selector (week/month/year) with theme support
 * - Summary statistics cards with SVG icons
 * - Trips over time bar chart
 * - Violations over time line chart
 * - Server-side data fetching with Inertia
 * - Responsive design (mobile-first)
 * - Full theme support (light/dark)
 * - Motion animations for smooth UX
 * - Empty state for no data
 *
 * @example Route: /employee/statistics
 */

import { router } from '@inertiajs/vue3';

import { IconGauge } from '@/components/icons';
import PeriodSelector from '@/components/statistics/PeriodSelector.vue';
import StatCard from '@/components/statistics/StatCard.vue';
import TripsChart from '@/components/statistics/TripsChart.vue';
import ViolationsChart from '@/components/statistics/ViolationsChart.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import type { Period, UserStatistics } from '@/types/statistics';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Statistics data from backend */
    statistics: UserStatistics;
    /** Currently selected period */
    currentPeriod: Period;
}

const props = defineProps<Props>();

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle period change - trigger server-side refetch.
 *
 * Uses Inertia router to fetch new data with selected period.
 */
function handlePeriodChange(period: Period): void {
    router.visit('/employee/statistics', {
        data: { period },
        only: ['statistics', 'currentPeriod'],
        preserveScroll: true,
    });
}

// Use props to avoid unused warning
const { statistics, currentPeriod } = props;
</script>

<template>
    <EmployeeLayout title="My Statistics">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header with Period Selector -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <!-- Page Title -->
                <div>
                    <h1
                        class="text-3xl font-bold text-zinc-900 dark:text-white"
                        style="font-family: 'Bebas Neue', sans-serif"
                    >
                        My Statistics
                    </h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Your driving performance for {{ statistics.period.label }}
                    </p>
                </div>

                <!-- Period Selector -->
                <PeriodSelector
                    :model-value="currentPeriod"
                    @update:model-value="handlePeriodChange"
                />
            </div>

            <!-- Summary Statistics Cards Grid -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Trips -->
                <StatCard
                    title="Total Trips"
                    :value="statistics.summary.total_trips"
                    unit="trips completed"
                    icon="car"
                    color="blue"
                />

                <!-- Total Distance -->
                <StatCard
                    title="Total Distance"
                    :value="statistics.summary.total_distance"
                    unit="kilometers"
                    icon="map"
                    color="green"
                />

                <!-- Average Speed -->
                <StatCard
                    title="Average Speed"
                    :value="statistics.summary.average_speed"
                    unit="km/h"
                    icon="zap"
                    color="purple"
                />

                <!-- Violations -->
                <StatCard
                    title="Violations"
                    :value="statistics.summary.violation_count"
                    unit="speed limit exceeded"
                    icon="alert"
                    :color="statistics.summary.violation_count > 0 ? 'red' : 'green'"
                />
            </div>

            <!-- ================================================================
                Charts Section
            ================================================================ -->
            <div class="space-y-6">
                <!-- Trips Over Time Chart -->
                <TripsChart
                    :data="statistics.charts.trips_over_time"
                    :period="currentPeriod"
                />

                <!-- Violations Over Time Chart -->
                <ViolationsChart
                    :data="statistics.charts.violations_over_time"
                    :period="currentPeriod"
                />
            </div>

            <!-- Empty State (No Trips) -->
            <div
                v-if="statistics.summary.total_trips === 0"
                class="mt-6 rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-white/10 dark:bg-zinc-800"
            >
                <div class="mb-6 flex justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-900 dark:text-zinc-600">
                        <IconGauge :size="40" />
                    </div>
                </div>
                <h3
                    class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    No Trip Data Yet
                </h3>
                <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                    Start tracking your trips with the speedometer to see your statistics here.
                </p>
                <a
                    href="/employee/speedometer"
                    class="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition-all hover:shadow-xl hover:shadow-cyan-200 active:scale-95 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25 dark:hover:shadow-cyan-500/40"
                >
                    <span>Start Speedometer</span>
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </a>
            </div>
        </div>
    </EmployeeLayout>
</template>
