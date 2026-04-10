<script setup lang="ts">
/**
 * My Statistics Page - Employee personal performance metrics.
 *
 * Displays summary statistics and charts for trips and violations over different time periods.
 * Data is passed from StatisticsController via Inertia props for optimal performance.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Period selector (week/month/year)
 * - Summary statistics cards (trips, distance, speed, violations)
 * - Trips over time bar chart
 * - Violations over time line chart
 * - Server-side data fetching with Inertia
 * - Responsive design (mobile-first)
 *
 * @example Route: /employee/statistics
 */

import { Link, router } from '@inertiajs/vue3';
import { ArrowRight, BarChart3 } from '@lucide/vue';

import { index as speedometerRoutes } from '@/actions/App/Http/Controllers/Employee/SpeedometerController';
import { index } from '@/actions/App/Http/Controllers/Employee/StatisticsController';

const speedometerIndex = speedometerRoutes['/employee/speedometer'];
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
    const url = index.url({ query: { period } });
    console.log('Navigating to:', url, 'with period:', period);
    
    router.visit(url, {
        only: ['statistics', 'currentPeriod'],
        preserveScroll: true,
        onSuccess: () => {
            console.log('Statistics updated successfully for period:', period);
        },
        onError: (errors) => {
            console.error('Failed to load statistics:', errors);
        },
    });
}

// Use props to avoid unused warning
const { statistics, currentPeriod } = props;
</script>

<template>
    <EmployeeLayout title="Statistik Saya">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ================================================================
                Page Header with Period Selector
            ================================================================ -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Page Title -->
                <div>
                    <h1
                        class="text-3xl font-bold text-zinc-900 dark:text-white"
                        style="font-family: 'Bebas Neue', sans-serif"
                    >
                        Statistik Saya
                    </h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Performa berkendara Anda untuk {{ statistics.period.label }}
                    </p>
                </div>

                <!-- Period Selector -->
                <PeriodSelector
                    :model-value="currentPeriod"
                    @update:model-value="handlePeriodChange"
                />
            </div>

            <!-- ================================================================
                Summary Statistics Cards Grid
            ================================================================ -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Trips -->
                <StatCard
                    :key="`trips-${currentPeriod}-${statistics.summary.total_trips}`"
                    title="Total Perjalanan"
                    :value="statistics.summary.total_trips"
                    unit="perjalanan selesai"
                    icon="car"
                    color="blue"
                />

                <!-- Total Distance -->
                <StatCard
                    :key="`distance-${currentPeriod}-${statistics.summary.total_distance}`"
                    title="Total Jarak"
                    :value="statistics.summary.total_distance"
                    unit="kilometer"
                    icon="route"
                    color="green"
                />

                <!-- Average Speed -->
                <StatCard
                    :key="`speed-${currentPeriod}-${statistics.summary.average_speed}`"
                    title="Kecepatan Rata-rata"
                    :value="statistics.summary.average_speed"
                    unit="km/h"
                    icon="zap"
                    color="purple"
                />

                <!-- Violations -->
                <StatCard
                    :key="`violations-${currentPeriod}-${statistics.summary.violation_count}`"
                    title="Pelanggaran"
                    :value="statistics.summary.violation_count"
                    unit="melampaui batas kecepatan"
                    icon="shield-alert"
                    :color="statistics.summary.violation_count > 0 ? 'red' : 'green'"
                />
            </div>

            <!-- ================================================================
                Charts Section
            ================================================================ -->
            <div class="space-y-6">
                <!-- Trips Over Time Chart -->
                <TripsChart
                    :key="`trips-chart-${currentPeriod}`"
                    :data="statistics.charts.trips_over_time"
                    :period="currentPeriod"
                />

                <!-- Violations Over Time Chart -->
                <ViolationsChart
                    :key="`violations-chart-${currentPeriod}`"
                    :data="statistics.charts.violations_over_time"
                    :period="currentPeriod"
                />
            </div>

            <!-- ================================================================
                Empty State (No Trips)
            ================================================================ -->
            <div
                v-if="statistics.summary.total_trips === 0"
                class="mt-8 rounded-lg border border-zinc-200/80 bg-white/95 p-8 text-center shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                    aria-hidden="true"
                >
                    <BarChart3 :size="32" :stroke-width="2" class="text-zinc-400" />
                </div>
                <h3
                    class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Belum Ada Data Perjalanan
                </h3>
                <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                    Mulai lacak perjalanan Anda dengan speedometer untuk melihat statistik di sini.
                </p>
                <Link
                    :href="speedometerIndex.url()"
                    class="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 hover:shadow-lg dark:from-cyan-500 dark:to-blue-600 dark:hover:shadow-cyan-500/25"
                >
                    <span>Mulai Speedometer</span>
                    <ArrowRight :size="16" :stroke-width="2" aria-hidden="true" />
                </Link>
            </div>
        </div>
    </EmployeeLayout>
</template>
