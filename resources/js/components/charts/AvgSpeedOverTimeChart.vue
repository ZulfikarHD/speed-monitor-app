<script setup lang="ts">
/**
 * AvgSpeedOverTimeChart Component
 *
 * Line chart displaying average speed over time with speed limit reference line.
 * Built with Chart.js for high-performance rendering with SafeTrack dark theme.
 *
 * Features:
 * - Line chart with date (X-axis) and speed (Y-axis)
 * - Cyan line for average speed
 * - Red dashed line for speed limit reference
 * - Gradient fill under avg speed line
 * - Responsive sizing with proper aspect ratio
 * - Tooltips showing exact speed on hover
 * - Loading skeleton state
 * - Empty state when no data available
 */

import { Zap } from '@lucide/vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import type { ChartOptions, ChartData } from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

import type { AvgSpeedChartProps } from '@/types/statistics';

// ========================================================================
// Chart.js Registration
// ========================================================================

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// ========================================================================
// Component Props
// ========================================================================

const props = withDefaults(defineProps<AvgSpeedChartProps>(), {
    isLoading: false,
});

// ========================================================================
// Computed Chart Data
// ========================================================================

/**
 * Transform data into Chart.js format.
 */
const chartData = computed<ChartData<'line'>>(() => {
    const labels = props.data.map((point) => point.date);
    const speeds = props.data.map((point) => point.speed);
    const speedLimits = props.data.map((point) => point.speed_limit);

    return {
        labels,
        datasets: [
            {
                label: 'Kecepatan Rata-Rata',
                data: speeds,
                borderColor: '#22d3ee', // Cyan
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;

                    if (!chartArea) {
                        return 'rgba(34, 211, 238, 0.1)';
                    }

                    const gradient = ctx.createLinearGradient(
                        0,
                        chartArea.top,
                        0,
                        chartArea.bottom,
                    );
                    gradient.addColorStop(0, 'rgba(34, 211, 238, 0.3)');
                    gradient.addColorStop(1, 'rgba(34, 211, 238, 0)');

                    return gradient;
                },
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#22d3ee',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            },
            {
                label: 'Batas Kecepatan',
                data: speedLimits,
                borderColor: '#ef4444', // Red
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0,
                fill: false,
                pointRadius: 0,
                pointHoverRadius: 0,
            },
        ],
    };
});

/**
 * Chart.js configuration options.
 */
const chartOptions = computed<ChartOptions<'line'>>(() => ({
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                color: 'rgba(161, 161, 170, 0.8)',
                font: {
                    family: "'Barlow', sans-serif",
                    size: 11,
                },
                usePointStyle: true,
                padding: 15,
            },
        },
        tooltip: {
            backgroundColor: '#18181b',
            titleColor: '#fafafa',
            bodyColor: '#a1a1aa',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
                label: function (context) {
                    const value = context.parsed.y ?? 0;
                    return `${context.dataset.label}: ${value.toFixed(1)} km/h`;
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
                callback: function (value) {
                    return `${value} km/h`;
                },
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
        Average Speed Line Chart
        Chart.js line chart with avg speed and speed limit data
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
                Kecepatan Rata-Rata vs Standar Kec
            </h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Perbandingan kecepatan rata-rata dengan batas kecepatan
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
            <Zap
                :size="40"
                class="mb-3 text-zinc-400"
                aria-hidden="true"
            />
            <h4
                class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white"
            >
                No Speed Data
            </h4>
            <p
                class="text-center text-sm text-zinc-600 dark:text-zinc-400"
            >
                No speed data recorded for the selected period.
            </p>
        </div>

        <!-- Chart Canvas -->
        <div v-else class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
