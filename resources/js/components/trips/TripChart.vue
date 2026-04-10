<script setup lang="ts">
/**
 * TripChart Component
 *
 * Reusable Chart.js line chart with 3 variants:
 * - 'average': speed over time + average speed reference + speed standard
 * - 'max': speed over time + max speed reference + speed standard
 * - 'violations': scatter chart showing only violation events + speed standard
 */

import { Activity } from '@lucide/vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    BarController,
    ScatterController,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

import type { SpeedLog } from '@/types/trip';
import { formatChartTime } from '@/utils/date';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    BarController,
    ScatterController,
    Title,
    Tooltip,
    Legend,
    Filler,
);

interface Props {
    variant: 'average' | 'max' | 'violations';
    speedLogs: SpeedLog[];
    speedLimit: number;
    averageSpeed: number | string | null;
    maxSpeed: number | string | null;
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const titleMap: Record<string, string> = {
    average: 'Grafik Kecepatan Rata-Rata vs Standar Kecepatan',
    max: 'Grafik Kecepatan Maksimal vs Standar Kecepatan',
    violations: 'Grafik Pelanggaran',
};

const subtitleMap: Record<string, string> = {
    average: 'Perbandingan kecepatan aktual dengan rata-rata dan standar kecepatan',
    max: 'Perbandingan kecepatan aktual dengan kecepatan maksimal dan standar',
    violations: 'Titik-titik pelanggaran batas kecepatan selama perjalanan',
};

function parseSpeed(val: number | string | null): number {
    if (val === null) return 0;
    return typeof val === 'string' ? parseFloat(val) : val;
}

const sortedLogs = computed(() =>
    [...props.speedLogs].sort(
        (a, b) => new Date(a.recorded_at).getTime() - new Date(b.recorded_at).getTime(),
    ),
);

const chartData = computed<ChartData<'line'>>(() => {
    const logs = sortedLogs.value;
    const labels = logs.map((log) => formatChartTime(log.recorded_at));
    const speeds = logs.map((log) => parseSpeed(log.speed));
    const avgVal = parseSpeed(props.averageSpeed);
    const maxVal = parseSpeed(props.maxSpeed);

    if (props.variant === 'average') {
        return {
            labels,
            datasets: [
                {
                    label: 'Kecepatan',
                    data: speeds,
                    borderColor: '#22d3ee',
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return 'rgba(34, 211, 238, 0.1)';
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(34, 211, 238, 0.3)');
                        gradient.addColorStop(1, 'rgba(34, 211, 238, 0)');
                        return gradient;
                    },
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                },
                {
                    label: `Rata-Rata (${avgVal.toFixed(1)} km/h)`,
                    data: labels.map(() => avgVal),
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [8, 4],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                },
                {
                    label: `Standar Kecepatan (${props.speedLimit} km/h)`,
                    data: labels.map(() => props.speedLimit),
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                },
            ],
        };
    }

    if (props.variant === 'max') {
        return {
            labels,
            datasets: [
                {
                    label: 'Kecepatan',
                    data: speeds,
                    borderColor: '#22d3ee',
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return 'rgba(34, 211, 238, 0.1)';
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(34, 211, 238, 0.3)');
                        gradient.addColorStop(1, 'rgba(34, 211, 238, 0)');
                        return gradient;
                    },
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                },
                {
                    label: `Kec. Maksimal (${maxVal.toFixed(1)} km/h)`,
                    data: labels.map(() => maxVal),
                    borderColor: '#f97316',
                    borderWidth: 2,
                    borderDash: [8, 4],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                },
                {
                    label: `Standar Kecepatan (${props.speedLimit} km/h)`,
                    data: labels.map(() => props.speedLimit),
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                },
            ],
        };
    }

    // violations variant
    const violationPoints = logs
        .filter((log) => log.is_violation)
        .map((log) => ({
            x: formatChartTime(log.recorded_at),
            y: parseSpeed(log.speed),
        }));

    return {
        labels,
        datasets: [
            {
                label: 'Pelanggaran',
                data: violationPoints,
                type: 'scatter' as const,
                borderColor: '#ef4444',
                backgroundColor: '#ef4444',
                pointRadius: 6,
                pointHoverRadius: 8,
            },
            {
                label: `Standar Kecepatan (${props.speedLimit} km/h)`,
                data: labels.map(() => props.speedLimit),
                borderColor: '#ef4444',
                borderWidth: 2,
                borderDash: [4, 4],
                pointRadius: 0,
                pointHoverRadius: 0,
                fill: false,
            },
        ],
    };
});

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
                font: { family: "'Barlow', sans-serif", size: 12 },
                usePointStyle: true,
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
                label(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.parsed.y !== null) label += context.parsed.y.toFixed(1) + ' km/h';
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
                color: 'rgba(161, 161, 170, 0.8)',
                font: { family: "'Barlow', sans-serif", size: 12 },
            },
            ticks: {
                color: 'rgba(161, 161, 170, 0.8)',
                maxRotation: 45,
                minRotation: 0,
                maxTicksLimit: 10,
            },
            grid: { color: 'rgba(161, 161, 170, 0.15)' },
        },
        y: {
            display: true,
            title: {
                display: true,
                text: 'Kecepatan (km/h)',
                color: 'rgba(161, 161, 170, 0.8)',
                font: { family: "'Barlow', sans-serif", size: 12 },
            },
            ticks: {
                color: 'rgba(161, 161, 170, 0.8)',
                callback(value) {
                    return value + ' km/h';
                },
            },
            grid: { color: 'rgba(161, 161, 170, 0.15)' },
            min: 0,
            max: 120,
        },
    },
}));

const hasData = computed(() => {
    if (props.variant === 'violations') {
        return props.speedLogs.some((log) => log.is_violation);
    }
    return props.speedLogs.length > 0;
});
</script>

<template>
    <div
        class="rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5"
    >
        <div class="mb-4">
            <h3
                class="text-lg font-semibold text-zinc-900 dark:text-white"
                style="font-family: 'Bebas Neue', sans-serif"
            >
                {{ titleMap[variant] }}
            </h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ subtitleMap[variant] }}
            </p>
        </div>

        <!-- Loading -->
        <div
            v-if="isLoading"
            class="flex h-64 items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50"
            role="status"
            aria-label="Memuat grafik"
        >
            <div class="text-center">
                <div class="mb-3 inline-block h-10 w-10 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"></div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Memuat grafik...</p>
            </div>
        </div>

        <!-- Empty -->
        <div
            v-else-if="!hasData"
            class="flex h-64 flex-col items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50"
        >
            <Activity :size="40" class="mb-3 text-zinc-400" aria-hidden="true" />
            <h4 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">
                {{ variant === 'violations' ? 'Tidak Ada Pelanggaran' : 'Tidak Ada Data Kecepatan' }}
            </h4>
            <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                {{ variant === 'violations'
                    ? 'Tidak ada pelanggaran batas kecepatan yang tercatat.'
                    : 'Tidak ada log kecepatan yang tercatat untuk perjalanan ini.'
                }}
            </p>
        </div>

        <!-- Chart -->
        <div v-else class="relative rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
