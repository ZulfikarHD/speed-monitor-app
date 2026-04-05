<script setup lang="ts">
/**
 * EmployeeSummaryWidget - Displays employee activity summary.
 *
 * Shows a compact overview of employee statistics including total employee count,
 * employees active today, and the current top performer by trip count.
 */

import type { EmployeeSummary } from '@/types/dashboard';

interface Props {
    /** Employee summary data from backend */
    summary: EmployeeSummary;

    /** Loading state for skeleton UI */
    isLoading?: boolean;
}

withDefaults(defineProps<Props>(), {
    isLoading: false,
});
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 dark:border-white/5 bg-gradient-to-br from-[#1C1C1A] to-[#2A2A28]"
    >
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-white/5 px-6 py-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Employee Summary</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Activity overview for today</p>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <div class="h-4 w-24 animate-pulse rounded bg-[#3E3E3A]"></div>
                <div class="h-6 w-12 animate-pulse rounded bg-[#3E3E3A]"></div>
            </div>
            <div class="flex items-center justify-between">
                <div class="h-4 w-24 animate-pulse rounded bg-[#3E3E3A]"></div>
                <div class="h-6 w-12 animate-pulse rounded bg-[#3E3E3A]"></div>
            </div>
            <div class="flex items-center justify-between">
                <div class="h-4 w-32 animate-pulse rounded bg-[#3E3E3A]"></div>
                <div class="h-6 w-20 animate-pulse rounded bg-[#3E3E3A]"></div>
            </div>
        </div>

        <!-- Content -->
        <div v-else class="space-y-4 p-6">
            <!-- Total Employees -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Total Employees</span>
                <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ summary.total_employees }}</span>
            </div>

            <!-- Active Today -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Active Today</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-emerald-400">{{ summary.active_today }}</span>
                    <span
                        v-if="summary.total_employees > 0"
                        class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400"
                    >
                        {{ Math.round((summary.active_today / summary.total_employees) * 100) }}%
                    </span>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-zinc-200 dark:border-white/5"></div>

            <!-- Top Performer -->
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Top Performer</span>
                    <p v-if="summary.top_performer" class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ summary.top_performer.name }}
                    </p>
                    <p v-else class="mt-1 text-sm italic text-zinc-500 dark:text-zinc-500">No trips yet</p>
                </div>
                <div
                    v-if="summary.top_performer"
                    class="flex items-center gap-1 rounded-lg bg-amber-500/10 px-3 py-1.5"
                >
                    <span class="text-lg">🏆</span>
                    <span class="text-sm font-semibold text-amber-400">
                        {{ summary.top_performer.trip_count }} trips
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
