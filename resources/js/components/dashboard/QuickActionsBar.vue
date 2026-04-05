<script setup lang="ts">
/**
 * QuickActionsBar - Quick navigation buttons for common supervisor actions.
 *
 * Provides prominent buttons for frequently accessed pages: All Trips list,
 * Violation Leaderboard, and CSV Export. Improves dashboard workflow efficiency.
 * Uses SVG icons following design system standards.
 */

import { Link } from '@inertiajs/vue3';

import { index as tripsIndex } from '@/actions/App/Http/Controllers/Supervisor/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Supervisor/DashboardController';
import IconCar from '@/components/icons/IconCar.vue';
import IconDownload from '@/components/icons/IconDownload.vue';
import IconTrophy from '@/components/icons/IconTrophy.vue';
</script>

<template>
    <div
        class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-200 dark:shadow-cyan-500/5"
    >
        <!-- Quick Actions Grid -->
        <div class="grid gap-3 sm:grid-cols-3">
            <!-- View All Trips -->
            <Link
                :href="tripsIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
            >
                <IconCar :size="18" class="transition-transform group-hover:scale-110" />
                <span>View All Trips</span>
            </Link>

            <!-- View Leaderboard -->
            <Link
                :href="leaderboardIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
            >
                <IconTrophy :size="18" class="transition-transform group-hover:scale-110" />
                <span>View Leaderboard</span>
            </Link>

            <!-- Export Report -->
            <button
                type="button"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
                title="CSV export coming soon"
            >
                <IconDownload :size="18" class="transition-transform group-hover:scale-110" />
                <span>Export Report</span>
            </button>
        </div>
    </div>
</template>
