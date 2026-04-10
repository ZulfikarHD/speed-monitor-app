<script setup lang="ts">
/**
 * Violations by Employee Chart
 *
 * Horizontal bar chart showing total violation counts per employee.
 */

import { AlertTriangle } from '@lucide/vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface ViolationEntry {
    name: string;
    violations: number;
}

interface Props {
    data: ViolationEntry[];
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const chartData = computed<ChartData<'bar'>>(() => {
    return {
        labels: props.data.map((d) => d.name),
        datasets: [
            {
                label: 'Jumlah Pelanggaran',
                data: props.data.map((d) => d.violations),
                backgroundColor: props.data.map((_, i) => {
                    const colors = [
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(132, 204, 22, 0.7)',
                        'rgba(34, 211, 238, 0.7)',
                    ];

                    return colors[i % colors.length];
                }),
                borderColor: props.data.map((_, i) => {
                    const colors = [
                        'rgba(239, 68, 68, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(132, 204, 22, 1)',
                        'rgba(34, 211, 238, 1)',
                    ];

                    return colors[i % colors.length];
                }),
                borderWidth: 1,
                borderRadius: 4,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 2,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#18181b',
            titleColor: '#fafafa',
            bodyColor: '#a1a1aa',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 10,
            callbacks: {
                label: (ctx) => `Pelanggaran: ${ctx.parsed.x}`,
            },
        },
    },
    scales: {
        x: {
            ticks: { color: 'rgba(161, 161, 170, 0.8)', stepSize: 1 },
            grid: { color: 'rgba(161, 161, 170, 0.1)' },
            min: 0,
        },
        y: {
            ticks: { color: 'rgba(161, 161, 170, 0.8)' },
            grid: { display: false },
        },
    },
}));

const hasData = computed(() => props.data.length > 0);
</script>

<template>
    <div class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Jumlah Pelanggaran</h3>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Pelanggaran per karyawan berdasarkan filter</p>
        </div>

        <div v-if="isLoading" class="flex h-48 items-center justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"></div>
        </div>

        <div v-else-if="!hasData" class="flex h-48 flex-col items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50">
            <AlertTriangle :size="32" class="mb-2 text-zinc-400" aria-hidden="true" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada pelanggaran</p>
        </div>

        <div v-else>
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
