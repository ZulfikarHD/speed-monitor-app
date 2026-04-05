<script setup lang="ts">
/**
 * QuickActionsBar - Quick navigation buttons for common supervisor actions.
 *
 * Provides prominent buttons for frequently accessed pages: All Trips list,
 * Violation Leaderboard, and CSV Export. Improves dashboard workflow efficiency.
 */

import { Link } from '@inertiajs/vue3';
import { index as tripsIndex } from '@/actions/App/Http/Controllers/Supervisor/AllTripsController';
import { violations as leaderboardIndex } from '@/actions/App/Http/Controllers/Supervisor/DashboardController';
</script>

<template>
    <div
        class="rounded-xl border border-[#3E3E3A] bg-gradient-to-br from-[#1C1C1A] to-[#2A2A28] p-4"
    >
        <!-- Quick Actions Grid -->
        <div class="grid gap-3 sm:grid-cols-3">
            <!-- View All Trips -->
            <Link
                :href="tripsIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-[#3E3E3A] bg-[#2A2A28] px-4 py-3 text-sm font-medium text-[#EDEDEC] transition-all hover:border-[#4E4E4A] hover:bg-[#3A3A38] active:scale-95"
            >
                <span class="text-xl transition-transform group-hover:scale-110">🚗</span>
                <span>View All Trips</span>
            </Link>

            <!-- View Leaderboard -->
            <Link
                :href="leaderboardIndex.url()"
                class="group flex items-center justify-center gap-2 rounded-lg border border-[#3E3E3A] bg-[#2A2A28] px-4 py-3 text-sm font-medium text-[#EDEDEC] transition-all hover:border-[#4E4E4A] hover:bg-[#3A3A38] active:scale-95"
            >
                <span class="text-xl transition-transform group-hover:scale-110">🏆</span>
                <span>View Leaderboard</span>
            </Link>

            <!-- Export Report -->
            <button
                type="button"
                class="group flex items-center justify-center gap-2 rounded-lg border border-[#3E3E3A] bg-[#2A2A28] px-4 py-3 text-sm font-medium text-[#EDEDEC] transition-all hover:border-[#4E4E4A] hover:bg-[#3A3A38] active:scale-95"
                title="CSV export coming soon"
            >
                <span class="text-xl transition-transform group-hover:scale-110">📊</span>
                <span>Export Report</span>
            </button>
        </div>
    </div>
</template>
