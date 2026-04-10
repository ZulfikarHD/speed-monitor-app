<script setup lang="ts">
/**
 * ViolationsChart Component
 *
 * Line/Bar chart displaying violation count over time for the selected period.
 * Built with Chart.js for high-performance rendering with SafeTrack dark theme.
 *
 * Features:
 * - Line or bar chart with date (X-axis) and violation count (Y-axis)
 * - Red line/bars for violations
 * - Gradient fill under line/bars
 * - Reference line at 0
 * - Responsive sizing with proper aspect ratio
 * - Tooltips showing exact count on hover
 * - Loading skeleton state
 * - Empty state when no data available
 */

import { TrendingUp } from '@lucide/vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { computed } from 'vue';
import { Line, Bar } from 'vue-chartjs';

import type { ViolationsChartProps } from '@/types/statistics';

// ========================================================================
// Chart.js Registration
// ========================================================================

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// ========================================================================
// Component Props
// ========================================================================

const props = withDefaults(defineProps<ViolationsChartProps>(), {
    isLoading: false,
    chartType: 'line',
});

// ========================================================================
// Computed Chart Data
// ========================================================================

/**
 * Transform data into Chart.js format.
 */
const chartData = computed<ChartData<'line' | 'bar'>>(() => {
    const labels = props.data.map((point) => point.date);
    const counts = props.data.map((point) => point.count);

    const baseDataset = {
        label: 'Violations',
        data: counts,
        borderColor: '#ef4444', // Red
        backgroundColor: (context: any) => {
            const chart = context.chart;
            const { ctx, chartArea } = chart;

            if (!chartArea) {
                return props.chartType === 'bar' ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.1)';
            }

            const gradient = ctx.createLinearGradient(
                0,
                chartArea.top,
                0,
                chartArea.bottom,
            );
            
            if (props.chartType === 'bar') {
                gradient.addColorStop(0, 'rgba(239, 68, 68, 0.9)');
                gradient.addColorStop(1, 'rgba(239, 68, 68, 0.6)');
            } else {
                gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
                gradient.addColorStop(1, 'rgba(239, 68, 68, 0)');
            }

            return gradient;
        },
        borderWidth: 2,
    };

    const lineSpecificProps = props.chartType === 'line' ? {
        tension: 0.4,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#ef4444',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
    } : {
        borderRadius: 4,
        borderSkipped: false,
    };

    return {
        labels,
        datasets: [
            {
                ...baseDataset,
                ...lineSpecificProps,
            } as any,
        ],
    };
});

/**
 * Chart.js configuration options.
 */
const chartOptions = computed<ChartOptions<'line' | 'bar'>>(() => ({
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
                    return `${context.parsed.y} violation${context.parsed.y !== 1 ? 's' : ''}`;
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
        Violations Line Chart
        Chart.js line chart with violation count data
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
                Violations Over Time
            </h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Speed limit violations per day
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
                    class="mb-3 inline-block h-10 w-10 animate-spin rounded-full border-4 border-red-500 border-t-transparent"
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
            <TrendingUp
                :size="40"
                class="mb-3 text-zinc-400"
                aria-hidden="true"
            />
            <h4
                class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white"
            >
                No Violation Data
            </h4>
            <p
                class="text-center text-sm text-zinc-600 dark:text-zinc-400"
            >
                No violations recorded for the selected period.
            </p>
        </div>

        <!-- Chart Canvas -->
        <div v-else class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
            <Line v-if="chartType === 'line'" :data="chartData" :options="chartOptions" />
            <Bar v-else :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
