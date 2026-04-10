<script setup lang="ts">
/**
 * Max Speed vs Standard Speed Chart
 *
 * Bar chart comparing each trip's max speed against the speed limit.
 * Highlights dangerous speed events.
 */

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import { BarChart } from '@lucide/vue';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend);

interface SpeedEntry {
    label: string;
    date: string;
    max_speed: number;
    speed_limit: number;
}

interface Props {
    data: SpeedEntry[];
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const chartData = computed(() => {
    const labels = props.data.map((d) => `${d.label} (${d.date})`);
    const maxSpeeds = props.data.map((d) => d.max_speed);
    const speedLimits = props.data.map((d) => d.speed_limit);

    return {
        labels,
        datasets: [
            {
                label: 'Kec. Maksimal',
                data: maxSpeeds,
                backgroundColor: maxSpeeds.map((s, i) =>
                    s > speedLimits[i] ? 'rgba(239, 68, 68, 0.7)' : 'rgba(34, 211, 238, 0.7)',
                ),
                borderColor: maxSpeeds.map((s, i) =>
                    s > speedLimits[i] ? 'rgba(239, 68, 68, 1)' : 'rgba(34, 211, 238, 1)',
                ),
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Batas Kecepatan',
                data: speedLimits,
                type: 'line' as const,
                borderColor: 'rgba(239, 68, 68, 0.8)',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 0,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                color: 'rgba(161, 161, 170, 0.8)',
                font: { size: 11 },
                usePointStyle: true,
            },
        },
        tooltip: {
            backgroundColor: '#18181b',
            titleColor: '#fafafa',
            bodyColor: '#a1a1aa',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 10,
            callbacks: {
                label: (ctx) => `${ctx.dataset.label}: ${(ctx.parsed.y ?? 0).toFixed(1)} km/h`,
            },
        },
    },
    scales: {
        x: {
            ticks: { color: 'rgba(161, 161, 170, 0.8)', maxRotation: 45 },
            grid: { color: 'rgba(161, 161, 170, 0.1)' },
        },
        y: {
            ticks: { color: 'rgba(161, 161, 170, 0.8)', callback: (v) => `${v} km/h` },
            grid: { color: 'rgba(161, 161, 170, 0.1)' },
            min: 0,
        },
    },
}));

const hasData = computed(() => props.data.length > 0);
</script>

<template>
    <div class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Kec. Maksimal vs Standar</h3>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Perbandingan kecepatan maksimal dengan batas kecepatan</p>
        </div>

        <div v-if="isLoading" class="flex h-48 items-center justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"></div>
        </div>

        <div v-else-if="!hasData" class="flex h-48 flex-col items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50">
            <BarChart :size="32" class="mb-2 text-zinc-400" aria-hidden="true" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada data</p>
        </div>

        <div v-else>
            <Bar :data="(chartData as any)" :options="chartOptions" />
        </div>
    </div>
</template>
