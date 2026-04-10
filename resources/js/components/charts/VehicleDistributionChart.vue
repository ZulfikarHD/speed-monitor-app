<script setup lang="ts">
/**
 * Vehicle Distribution Chart
 *
 * Doughnut chart showing the count of mobil vs motor trips.
 */

import { PieChart } from '@lucide/vue';
import {
    Chart as ChartJS,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Title, Tooltip, Legend);

interface Props {
    data: {
        mobil: number;
        motor: number;
    };
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const chartData = computed<ChartData<'doughnut'>>(() => ({
    labels: ['Mobil', 'Motor'],
    datasets: [
        {
            data: [props.data.mobil, props.data.motor],
            backgroundColor: [
                'rgba(59, 130, 246, 0.7)',
                'rgba(168, 85, 247, 0.7)',
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(168, 85, 247, 1)',
            ],
            borderWidth: 2,
            hoverOffset: 8,
        },
    ],
}));

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 1.5,
    cutout: '60%',
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                color: 'rgba(161, 161, 170, 0.8)',
                font: { size: 12 },
                usePointStyle: true,
                padding: 16,
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
                label: (ctx) => {
                    const total = props.data.mobil + props.data.motor;
                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : '0';

                    return `${ctx.label}: ${ctx.parsed} trip (${pct}%)`;
                },
            },
        },
    },
}));

const hasData = computed(() => props.data.mobil > 0 || props.data.motor > 0);

const totalTrips = computed(() => props.data.mobil + props.data.motor);
</script>

<template>
    <div class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Distribusi Kendaraan</h3>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Jumlah pengendara mobil dan motor</p>
        </div>

        <div v-if="isLoading" class="flex h-48 items-center justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"></div>
        </div>

        <div v-else-if="!hasData" class="flex h-48 flex-col items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/50">
            <PieChart :size="32" class="mb-2 text-zinc-400" aria-hidden="true" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada data kendaraan</p>
        </div>

        <div v-else class="relative">
            <Doughnut :data="chartData" :options="chartOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="margin-bottom: 2rem;">
                <div class="text-center">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ totalTrips }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Total Trip</div>
                </div>
            </div>
        </div>
    </div>
</template>
