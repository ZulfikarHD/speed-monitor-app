<script setup lang="ts">
/**
 * QuickActionsBar - Quick navigation buttons for common superuser actions.
 *
 * Provides prominent buttons for frequently accessed pages: All Trips list,
 * Violation Leaderboard, and CSV Export. Uses lucide SVG icons following
 * design system standards with full light/dark theme support.
 
 */

import { Link } from '@inertiajs/vue3';
import { Download, Route, Trophy } from '@lucide/vue';

import { index as tripsIndex } from '@/actions/App/Http/Controllers/Superuser/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Superuser/DashboardController';
</script>

<template>
    <div
        class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
    >
        <!-- Quick Actions Grid -->
        <div class="grid gap-3 sm:grid-cols-3">
            <!-- View All Trips -->
            <Link
                :href="tripsIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
            >
                <Route :size="18" :stroke-width="2" class="transition-transform duration-200 group-hover:scale-110" aria-hidden="true" />
                <span>Lihat Semua Perjalanan</span>
            </Link>

            <!-- View Leaderboard -->
            <Link
                :href="leaderboardIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
            >
                <Trophy :size="18" :stroke-width="2" class="transition-transform duration-200 group-hover:scale-110" aria-hidden="true" />
                <span>Lihat Papan Peringkat</span>
            </Link>

            <!-- Export Report -->
            <button
                type="button"
                class="group flex items-center justify-center gap-2 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 active:scale-95"
                title="Ekspor CSV segera hadir"
            >
                <Download :size="18" :stroke-width="2" class="transition-transform duration-200 group-hover:scale-110" aria-hidden="true" />
                <span>Ekspor Laporan</span>
            </button>
        </div>
    </div>
</template>
