<script setup lang="ts">
/**
 * My Statistics Page - Employee personal performance metrics.
 *
 * Displays summary statistics and charts for trips and speed performance over different time periods.
 * Data is passed from StatisticsController via Inertia props for optimal performance.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Period selector (week/month/year/custom)
 * - Summary statistics cards (avg distance, speed, violations, vehicle counts)
 * - Average speed vs standard chart
 * - Max speed vs standard chart
 * - Violations over time chart
 * - Server-side data fetching with Inertia
 * - Responsive design (mobile-first)
 *
 * @example Route: /employee/statistics
 */


import { Link, router } from '@inertiajs/vue3';
import { ArrowRight, BarChart3 } from '@lucide/vue';
import { ref } from 'vue';

import { index as speedometerRoutes } from '@/actions/App/Http/Controllers/Employee/SpeedometerController';
import { index } from '@/actions/App/Http/Controllers/Employee/StatisticsController';

const speedometerIndex = speedometerRoutes['/employee/speedometer'];
import AvgSpeedOverTimeChart from '@/components/charts/AvgSpeedOverTimeChart.vue';
import MaxSpeedChart from '@/components/charts/MaxSpeedChart.vue';
import PeriodSelector from '@/components/statistics/PeriodSelector.vue';
import StatCard from '@/components/statistics/StatCard.vue';
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
    /** Custom date range start (YYYY-MM-DD) */
    dateFrom?: string;
    /** Custom date range end (YYYY-MM-DD) */
    dateTo?: string;
}

const props = withDefaults(defineProps<Props>(), {
    dateFrom: '',
    dateTo: '',
});

// ========================================================================
// Local State
// ========================================================================

const selectedPeriod = ref<Period>(props.currentPeriod);
const localDateFrom = ref(props.dateFrom);
const localDateTo = ref(props.dateTo);

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle period preset change.
 */
function handlePeriodChange(period: Period): void {
    selectedPeriod.value = period;
}

/**
 * Handle custom date range from/to changes.
 */
function handleDateFromChange(value: string): void {
    localDateFrom.value = value;
}

function handleDateToChange(value: string): void {
    localDateTo.value = value;
}

/**
 * Apply filters - trigger server-side refetch.
 */
function handleApply(): void {
    const query: Record<string, string> = { period: selectedPeriod.value };

    if (selectedPeriod.value === 'custom') {
        query.date_from = localDateFrom.value;
        query.date_to = localDateTo.value;
    }

    const url = index.url({ query });

    router.visit(url, {
        only: ['statistics', 'currentPeriod', 'dateFrom', 'dateTo'],
        preserveScroll: true,
    });
}
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
                    :model-value="selectedPeriod"
                    :date-from="localDateFrom"
                    :date-to="localDateTo"
                    @update:model-value="handlePeriodChange"
                    @update:date-from="handleDateFromChange"
                    @update:date-to="handleDateToChange"
                    @apply="handleApply"
                />
            </div>

            <!-- ================================================================
                Summary Statistics Cards Grid
            ================================================================ -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Average Distance Per Trip -->
                <StatCard
                    :key="`avg-distance-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.average_distance}`"
                    title="Rata-Rata Jarak Tempuh"
                    :value="statistics.summary.average_distance"
                    unit="km per perjalanan"
                    icon="route"
                    color="blue"
                />

                <!-- Average Speed -->
                <StatCard
                    :key="`speed-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.average_speed}`"
                    title="Kecepatan Rata-Rata"
                    :value="statistics.summary.average_speed"
                    unit="km/h"
                    icon="zap"
                    color="purple"
                />

                <!-- Violations -->
                <StatCard
                    :key="`violations-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.violation_count}`"
                    title="Total Pelanggaran"
                    :value="statistics.summary.violation_count"
                    unit="melampaui batas"
                    icon="shield-alert"
                    :color="statistics.summary.violation_count > 0 ? 'red' : 'green'"
                />

                <!-- Motor Count -->
                <StatCard
                    :key="`motor-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.motor_count}`"
                    title="Motor"
                    :value="statistics.summary.motor_count"
                    unit="perjalanan"
                    icon="bike"
                    color="green"
                />

                <!-- Mobil Count -->
                <StatCard
                    :key="`mobil-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.mobil_count}`"
                    title="Mobil"
                    :value="statistics.summary.mobil_count"
                    unit="perjalanan"
                    icon="car"
                    color="orange"
                />

                <!-- Speed Limit Reference -->
                <StatCard
                    :key="`speed-limit-${currentPeriod}-${dateFrom}-${dateTo}-${statistics.summary.speed_limit}`"
                    title="Batas Kecepatan"
                    :value="statistics.summary.speed_limit"
                    unit="km/h"
                    icon="octagon-alert"
                    color="red"
                />
            </div>

            <!-- ================================================================
                Charts Section
            ================================================================ -->
            <div class="space-y-6">
                <!-- Average Speed vs Standard Chart -->
                <AvgSpeedOverTimeChart
                    :key="`avg-speed-chart-${currentPeriod}-${dateFrom}-${dateTo}`"
                    :data="statistics.charts.avg_speed_over_time"
                />

                <!-- Max Speed vs Standard Chart -->
                <MaxSpeedChart
                    :key="`max-speed-chart-${currentPeriod}-${dateFrom}-${dateTo}`"
                    :data="statistics.charts.max_speed_over_time"
                />

                <!-- Violations Over Time Chart -->
                <ViolationsChart
                    :key="`violations-chart-${currentPeriod}-${dateFrom}-${dateTo}`"
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
