<script setup lang="ts">
/**
 * TripsChart Component
 *
 * Bar chart displaying trip count over time for the selected period.
 * Built with Chart.js for high-performance rendering with SpeedoMontor dark theme.
 *
 * Features:
 * - Bar chart with date (X-axis) and trip count (Y-axis)
 * - Cyan bars matching SpeedoMontor theme
 * - Responsive sizing with proper aspect ratio
 * - Tooltips showing exact count on hover
 * - Loading skeleton state
 * - Empty state when no data available
 */

import { BarChart3 } from '@lucide/vue';
import {
    Chart as ChartJS,
    BarElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

import type { TripsChartProps } from '@/types/statistics';

// ========================================================================
// Chart.js Registration
// ========================================================================

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

// ========================================================================
// Component Props
// ========================================================================

const props = withDefaults(defineProps<TripsChartProps>(), {
    isLoading: false,
});

// ========================================================================
// Computed Chart Data
// ========================================================================

/**
 * Transform data into Chart.js format.
 */
const chartData = computed<ChartData<'bar'>>(() => {
    const labels = props.data.map((point) => point.date);
    const counts = props.data.map((point) => point.count);

    return {
        labels,
        datasets: [
            {
                label: 'Trips',
                data: counts,
                backgroundColor: '#22d3ee', // Cyan
                borderColor: '#06b6d4',
                borderWidth: 1,
                borderRadius: 4,
            },
        ],
    };
});

/**
 * Chart.js configuration options.
 */
const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#18181b',
            titleColor: '#fafafa',
            bodyColor: '#a1a1aa',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            callbacks: {
                label: function (context) {
                    return `${context.parsed.y} trip${context.parsed.y !== 1 ? 's' : ''}`;
                },
            },
        },
    },
    scales: {
        x: {
            display: true,
            grid: {
                color: 'rgba(161, 161, 170, 0.15)',
                drawBorder: false,
            },
            ticks: {
                color: 'rgba(161, 161, 170, 0.8)',
                maxRotation: 45,
                minRotation: 0,
                font: {
                    family: "'Barlow', sans-serif",
                    size: 11,
                },
            },
        },
        y: {
            display: true,
            beginAtZero: true,
            grid: {
                color: 'rgba(161, 161, 170, 0.15)',
                drawBorder: false,
            },
            ticks: {
                color: 'rgba(161, 161, 170, 0.8)',
                stepSize: 1,
                precision: 0,
                font: {
                    family: "'Barlow', sans-serif",
                    size: 11,
                },
            },
        },
    },
}));

/**
 * Check if chart has data to display.
 */
const hasData = computed(() => props.data.length > 0);
</script>

<template>
    <!-- ======================================================================
        Trips Bar Chart
        Chart.js bar chart with trip count data
    ======================================================================= -->
    <div
        class="rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5"
    >
        <!-- Chart Header -->
        <div class="mb-4">
            <h3
                class="text-lg font-semibold text-zinc-900 dark:text-white"
                style="font-family: 'Bebas Neue', sans-serif"
            >
                Trips Over Time
            </h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Number of trips completed per day
            </p>
        </div>

        <!-- Loading State -->
        <div
            v-if="isLoading"
            class="flex h-64 items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50"
            role="status"
            aria-label="Loading chart"
        >
            <div class="text-center">
                <div
                    class="mb-3 inline-block h-10 w-10 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"
                ></div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Loading chart...
                </p>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="!hasData"
            class="flex h-64 flex-col items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50"
        >
            <BarChart3
                :size="40"
                class="mb-3 text-zinc-400"
                aria-hidden="true"
            />
            <h4
                class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white"
            >
                No Trip Data
            </h4>
            <p
                class="text-center text-sm text-zinc-600 dark:text-zinc-400"
            >
                No trips recorded for the selected period.
            </p>
        </div>

        <!-- Chart Canvas -->
        <div v-else class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
