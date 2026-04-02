<script setup lang="ts">
/**
 * SpeedChart Component
 *
 * Reusable line chart component displaying speed over time with violation
 * markers and speed limit reference line. Built with Chart.js for
 * high-performance rendering of speed log data.
 *
 * Features:
 * - Line chart with time (X-axis) and speed km/h (Y-axis)
 * - Speed limit horizontal reference line (red dashed)
 * - Violation markers as red scatter points
 * - Gradient fill under speed line (cyan to transparent)
 * - Dark theme styling matching VeloTrack design
 * - Responsive sizing with proper aspect ratio
 * - Tooltips showing exact speed and time on hover
 * - Loading skeleton state
 * - Empty state when no speed logs available
 *
 * @example
 * ```vue
 * <SpeedChart
 *   :speedLogs="trip.speedLogs"
 *   :speedLimit="60"
 *   :isLoading="false"
 * />
 * ```
 */

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
    
    
} from 'chart.js';
import type {ChartOptions, ChartData} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import type { SpeedLog } from '@/types/trip';
import { formatChartTime } from '@/utils/date';

// ========================================================================
// Chart.js Registration
// ========================================================================

// Register Chart.js components needed for line charts
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

interface Props {
    /** Array of speed log measurements to display */
    speedLogs: SpeedLog[];
    /** Speed limit threshold for reference line (km/h) */
    speedLimit: number;
    /** Whether chart is in loading state (shows skeleton) */
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

// ========================================================================
// Computed Chart Data
// ========================================================================

/**
 * Transform speed logs into Chart.js data format.
 *
 * Creates two datasets:
 * 1. Line chart (all speed measurements)
 * 2. Scatter plot (violations only) for visual emphasis
 */
const chartData = computed<ChartData<'line'>>(() => {
    // WHY: Sort by recorded_at to ensure chronological order
    // Chart.js requires data points in order for line drawing
    const sortedLogs = [...props.speedLogs].sort(
        (a, b) =>
            new Date(a.recorded_at).getTime() -
            new Date(b.recorded_at).getTime(),
    );

    // Extract time labels for X-axis
    const labels = sortedLogs.map((log) => formatChartTime(log.recorded_at));

    // Extract speed values for Y-axis (parse to number if string from DB)
    const speeds = sortedLogs.map((log) => 
        typeof log.speed === 'string' ? parseFloat(log.speed) : log.speed
    );

    // Filter violation points for scatter overlay
    // WHY: Separate dataset allows different styling (red dots) for violations
    const violationPoints = sortedLogs
        .filter((log) => log.is_violation)
        .map((log) => ({
            x: formatChartTime(log.recorded_at),
            y: typeof log.speed === 'string' ? parseFloat(log.speed) : log.speed,
        }));

    return {
        labels,
        datasets: [
            {
                label: 'Kecepatan',
                data: speeds,
                borderColor: '#22d3ee', // Cyan (VeloTrack theme)
                backgroundColor: (context) => {
                    // WHY: Gradient fill provides visual depth without overwhelming data
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
                tension: 0.4, // WHY: Smooth curve for better readability
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 4,
            },
            {
                label: 'Pelanggaran',
                data: violationPoints,
                type: 'scatter' as const,
                borderColor: '#ef4444', // Red
                backgroundColor: '#ef4444',
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    };
});

/**
 * Chart.js configuration options.
 *
 * Configures dark theme styling, responsive behavior, and speed limit
 * reference line using Chart.js annotation plugin alternative approach.
 */
const chartOptions = computed<ChartOptions<'line'>>(() => ({
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2, // WHY: 2:1 ratio provides good balance for time-series data
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                color: '#9ca3af', // Gray text
                font: {
                    family: "'Barlow', sans-serif",
                    size: 12,
                },
                usePointStyle: true,
            },
        },
        tooltip: {
            backgroundColor: '#1a1d23',
            titleColor: '#e5e7eb',
            bodyColor: '#9ca3af',
            borderColor: '#3E3E3A',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
                // WHY: Custom label shows "km/h" unit for clarity
                label: function (context) {
                    let label = context.dataset.label || '';

                    if (label) {
                        label += ': ';
                    }

                    if (context.parsed.y !== null) {
                        label += context.parsed.y.toFixed(1) + ' km/h';
                    }

                    return label;
                },
            },
        },
    },
    scales: {
        x: {
            display: true,
            title: {
                display: true,
                text: 'Waktu',
                color: '#9ca3af',
                font: {
                    family: "'Barlow', sans-serif",
                    size: 12,
                },
            },
            ticks: {
                color: '#9ca3af',
                maxRotation: 45,
                minRotation: 0,
                // WHY: Show fewer labels on mobile to prevent overlap
                maxTicksLimit: 10,
            },
            grid: {
                color: '#3E3E3A',
                drawBorder: false,
            },
        },
        y: {
            display: true,
            title: {
                display: true,
                text: 'Kecepatan (km/h)',
                color: '#9ca3af',
                font: {
                    family: "'Barlow', sans-serif",
                    size: 12,
                },
            },
            ticks: {
                color: '#9ca3af',
                callback: function (value) {
                    return value + ' km/h';
                },
            },
            grid: {
                color: '#3E3E3A',
                drawBorder: false,
            },
            // WHY: Fixed range provides consistent reference for speed limit line
            min: 0,
            max: 120,
        },
    },
}));

/**
 * Check if chart has data to display.
 */
const hasData = computed(() => props.speedLogs.length > 0);
</script>

<template>
    <!-- ======================================================================
        SpeedChart Component
        Chart.js line chart with speed data and violation markers
    ======================================================================= -->
    <div class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6">
        <!-- Chart Header -->
        <div class="mb-4">
            <h3
                class="text-lg font-semibold text-[#e5e7eb]"
                style="font-family: 'Bebas Neue', sans-serif"
            >
                Grafik Kecepatan
            </h3>
            <p class="mt-1 text-sm text-[#9ca3af]">
                Visualisasi kecepatan selama perjalanan dengan penanda
                pelanggaran
            </p>
        </div>

        <!-- Loading State -->
        <div
            v-if="isLoading"
            class="flex h-64 items-center justify-center bg-[#0a0c0f] rounded-lg"
            role="status"
            aria-label="Loading chart"
        >
            <div class="text-center">
                <div
                    class="mb-3 inline-block h-10 w-10 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"
                ></div>
                <p class="text-sm text-[#9ca3af]">Memuat grafik...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="!hasData"
            class="flex h-64 flex-col items-center justify-center bg-[#0a0c0f] rounded-lg"
        >
            <div class="mb-3 text-4xl" aria-hidden="true">📊</div>
            <h4 class="mb-2 text-lg font-semibold text-[#e5e7eb]">
                Tidak Ada Data Kecepatan
            </h4>
            <p class="text-center text-sm text-[#9ca3af]">
                Tidak ada log kecepatan yang tercatat untuk perjalanan ini.
            </p>
        </div>

        <!-- Chart Canvas -->
        <div v-else class="relative">
            <!-- Speed Limit Reference Line Indicator -->
            <div
                class="mb-3 flex items-center justify-end gap-2 text-xs text-[#9ca3af]"
            >
                <span>Batas Kecepatan:</span>
                <span
                    class="font-semibold text-red-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ speedLimit }} km/h
                </span>
                <div
                    class="h-0.5 w-8 border-t-2 border-dashed border-red-400"
                    aria-hidden="true"
                ></div>
            </div>

            <!-- Chart.js Line Chart -->
            <div class="relative bg-[#0a0c0f] rounded-lg p-4">
                <Line :data="chartData" :options="chartOptions" />

                <!-- Speed Limit Reference Line (Visual Overlay) -->
                <!-- WHY: Manual overlay for speed limit line since Chart.js annotation plugin
                     is not included. This provides visual reference for limit threshold. -->
                <div
                    class="pointer-events-none absolute inset-0 flex items-center px-4"
                    :style="{
                        paddingTop: `${(1 - speedLimit / 120) * 100}%`,
                        paddingBottom: '20%',
                    }"
                >
                    <div
                        class="h-0.5 w-full border-t-2 border-dashed border-red-400 opacity-50"
                        :title="`Speed limit: ${speedLimit} km/h`"
                    ></div>
                </div>
            </div>

            <!-- Chart Legend Footer -->
            <div
                class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs text-[#9ca3af]"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="h-3 w-3 rounded-full bg-cyan-500"
                        aria-hidden="true"
                    ></div>
                    <span>Kecepatan Normal</span>
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="h-3 w-3 rounded-full bg-red-500"
                        aria-hidden="true"
                    ></div>
                    <span>Pelanggaran Batas</span>
                </div>
            </div>
        </div>
    </div>
</template>
