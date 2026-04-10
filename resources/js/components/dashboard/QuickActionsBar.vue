<script setup lang="ts">
/**
 * QuickActionsBar - Quick navigation cards for common superuser actions.
 *
 * Provides prominent navigation cards for All Trips and Violation Leaderboard.
 * Uses lucide SVG icons with full light/dark theme support.
 */

import { Link } from '@inertiajs/vue3';
import { Motorbike, Trophy } from '@lucide/vue';

import { index as tripsIndex } from '@/actions/App/Http/Controllers/Superuser/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Superuser/DashboardController';
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <!-- View All Trips -->
        <Link
            :href="tripsIndex.url()"
            class="group flex items-center gap-4 rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 hover:shadow-xl active:scale-[0.98]"
        >
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-cyan-500/15 dark:bg-cyan-500/10">
                <Motorbike :size="24" :stroke-width="2" class="text-cyan-600 dark:text-cyan-400 transition-transform duration-200 group-hover:scale-110" aria-hidden="true" />
            </div>
            <div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Lihat Semua Perjalanan</div>
                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Pantau seluruh trip karyawan</div>
            </div>
        </Link>

        <!-- View Leaderboard -->
        <Link
            :href="leaderboardIndex.url()"
            class="group flex items-center gap-4 rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-amber-500/50 dark:hover:border-amber-500/50 hover:shadow-xl active:scale-[0.98]"
        >
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 dark:bg-amber-500/10">
                <Trophy :size="24" :stroke-width="2" class="text-amber-600 dark:text-amber-400 transition-transform duration-200 group-hover:scale-110" aria-hidden="true" />
            </div>
            <div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Papan Peringkat</div>
                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Peringkat pelanggaran kecepatan</div>
            </div>
        </Link>
    </div>
</template>
