<script setup lang="ts">
/**
 * Violation Leaderboard Page
 *
 * Supervisor-only page showing employees ranked by speed violations with
 * date filtering, violation rate calculations, and navigation to employee trips.
 *
 * Features:
 * - Desktop table view with rank, employee, violations, trips, rate columns
 * - Mobile card view with vertical layout
 * - Date range filtering (from/to)
 * - SVG medal icons for top 3 with color-coded badges
 * - Gradient rank badges matching design system
 * - Click "View Trips" to navigate to filtered All Trips page
 * - Lightweight opacity/y animations
 * - Empty state with SVG icon
 * - Full light/dark theme support
 *
 * @example Route: /supervisor/leaderboard?date_from=2026-03-01&date_to=2026-04-01
 */

import { Link, router } from '@inertiajs/vue3';
import { AlertTriangle, Medal, Trophy } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';

import { index as allTripsIndex } from '@/actions/App/Http/Controllers/Supervisor/AllTripsController';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import type {
    LeaderboardFilters,
    ViolationLeaderboardEntry,
} from '@/types/dashboard';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Leaderboard entries sorted by violations DESC */
    leaderboard: ViolationLeaderboardEntry[];

    /** Current filter values */
    filters: LeaderboardFilters;
}

const props = defineProps<Props>();

// ========================================================================
// Local State
// ========================================================================

/** Local filter state (synced with props) */
const localFilters = ref({
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
});

// ========================================================================
// Computed
// ========================================================================

/** Check if leaderboard has entries. */
const hasEntries = computed(() => props.leaderboard.length > 0);

/** Check if filters have been modified. */
const filtersModified = computed(() => {
    return (
        localFilters.value.date_from !== props.filters.date_from ||
        localFilters.value.date_to !== props.filters.date_to
    );
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Check if rank gets a medal icon.
 *
 * @param rank - Position in leaderboard (1-indexed)
 * @returns True if rank is in top 3
 */
function hasMedal(rank: number): boolean {
    return rank >= 1 && rank <= 3;
}

/**
 * Get medal icon color class for ranking position.
 *
 * @param rank - Position in leaderboard (1-indexed)
 * @returns Tailwind color class for medal
 */
function getMedalColor(rank: number): string {
    const colorMap: Record<number, string> = {
        1: 'text-yellow-500 dark:text-yellow-400',
        2: 'text-zinc-400 dark:text-zinc-300',
        3: 'text-amber-700 dark:text-amber-600',
    };

    return colorMap[rank] || '';
}

/**
 * Get color classes for rank badge.
 *
 * @param rank - Position in leaderboard (1-indexed)
 * @returns Tailwind color classes
 */
function getRankColorClasses(rank: number): string {
    const colorMap: Record<number, string> = {
        1: 'bg-gradient-to-r from-red-500 to-rose-600 text-white',
        2: 'bg-gradient-to-r from-orange-500 to-red-500 text-white',
        3: 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white',
    };

    return colorMap[rank] || 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-200';
}

/**
 * Format violation rate for display.
 *
 * @param rate - Violation rate (violations per trip)
 * @returns Formatted string (e.g., "2.50 / trip")
 */
function formatRate(rate: number): string {
    return `${rate.toFixed(2)} / trip`;
}

/**
 * Apply date filters.
 *
 * WHY: Validates date range before navigating to prevent invalid queries.
 */
function applyFilters(): void {
    if (localFilters.value.date_from > localFilters.value.date_to) {
        alert('Start date must be before or equal to end date');

        return;
    }

    router.visit('/supervisor/leaderboard', {
        data: {
            date_from: localFilters.value.date_from,
            date_to: localFilters.value.date_to,
        },
        preserveState: false,
        preserveScroll: false,
    });
}

/**
 * Reset filters to default (last 30 days).
 */
function resetFilters(): void {
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);

    localFilters.value.date_from = thirtyDaysAgo.toISOString().split('T')[0];
    localFilters.value.date_to = today.toISOString().split('T')[0];

    applyFilters();
}

/**
 * Generate Wayfinder URL for viewing employee's trips with filters.
 *
 * WHY: Type-safe route generation with query parameters.
 *
 * @param userId - Employee user ID
 * @returns URL string to All Trips page filtered by employee and violations
 */
function getEmployeeTripsUrl(userId: number): string {
    return allTripsIndex.url({
        query: {
            user_id: userId,
            date_from: props.filters.date_from,
            date_to: props.filters.date_to,
            violations_only: 1,
        },
    });
}
</script>

<template>
    <SupervisorLayout title="Violation Leaderboard">
        <div class="min-h-screen p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header Section -->
                <motion.div
                    :initial="{ opacity: 0, y: -12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3 }"
                >
                    <div class="flex flex-col gap-4">
                        <!-- Title -->
                        <div>
                            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white md:text-4xl">
                                Violation Leaderboard
                            </h1>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Employees ranked by speed violations
                            </p>
                        </div>

                        <!-- Date Range Filters -->
                        <div
                            class="flex flex-col gap-3 rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-4 sm:flex-row sm:items-end shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                        >
                            <!-- From Date -->
                            <div class="flex-1">
                                <label
                                    for="date-from"
                                    class="mb-1 block text-sm font-medium text-zinc-900 dark:text-white"
                                >
                                    From Date
                                </label>
                                <input
                                    id="date-from"
                                    v-model="localFilters.date_from"
                                    type="date"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-zinc-900 dark:text-white transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800"
                                />
                            </div>

                            <!-- To Date -->
                            <div class="flex-1">
                                <label
                                    for="date-to"
                                    class="mb-1 block text-sm font-medium text-zinc-900 dark:text-white"
                                >
                                    To Date
                                </label>
                                <input
                                    id="date-to"
                                    v-model="localFilters.date_to"
                                    type="date"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-zinc-900 dark:text-white transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800"
                                />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button
                                    :disabled="!filtersModified"
                                    class="rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-4 py-2 font-medium text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                    aria-label="Apply filters"
                                    @click="applyFilters"
                                >
                                    Apply
                                </button>

                                <button
                                    class="rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2 font-medium text-zinc-900 dark:text-zinc-200 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                                    aria-label="Reset filters"
                                    @click="resetFilters"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </motion.div>

                <!-- Empty State -->
                <motion.div
                    v-if="!hasEntries"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ delay: 0.1, duration: 0.3 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-12 text-center shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                >
                    <div class="mx-auto max-w-md">
                        <Trophy :size="64" :stroke-width="1.5" class="mx-auto text-zinc-400 dark:text-zinc-600" aria-hidden="true" />
                        <h3 class="mt-4 text-xl font-semibold text-zinc-900 dark:text-white">
                            No Violations Recorded
                        </h3>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            No speed violations were recorded in the selected period.
                            All employees are driving safely!
                        </p>
                    </div>
                </motion.div>

                <!-- Desktop Table View -->
                <motion.div
                    v-if="hasEntries"
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.3 }"
                    class="hidden overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 md:block"
                >
                    <table class="w-full">
                        <!-- Table Header -->
                        <thead class="border-b border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Rank</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Employee</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Violations</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Trips</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Violation Rate</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Actions</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            <motion.tr
                                v-for="(entry, index) in leaderboard"
                                :key="entry.user.id"
                                :initial="{ opacity: 0, y: 8 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: index * 0.03, duration: 0.25 }"
                                class="group border-b border-zinc-200 dark:border-white/5 last:border-b-0 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-white/5"
                            >
                                <!-- Rank -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="getRankColorClasses(entry.rank)"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold shadow-lg"
                                        >
                                            {{ entry.rank }}
                                        </span>
                                        <Medal
                                            v-if="hasMedal(entry.rank)"
                                            :size="20"
                                            :stroke-width="2"
                                            :class="getMedalColor(entry.rank)"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </td>

                                <!-- Employee -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ entry.user.name }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ entry.user.email }}</div>
                                    </div>
                                </td>

                                <!-- Violations -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border border-red-500/30 bg-red-500/20 dark:bg-red-500/15 px-3 py-1 text-sm font-semibold text-red-700 dark:text-red-400"
                                    >
                                        <AlertTriangle :size="14" :stroke-width="2" aria-hidden="true" />
                                        {{ entry.violation_count }}
                                    </span>
                                </td>

                                <!-- Total Trips -->
                                <td class="px-6 py-4">
                                    <span class="text-zinc-900 dark:text-white">{{ entry.total_trips }}</span>
                                </td>

                                <!-- Violation Rate -->
                                <td class="px-6 py-4">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ formatRate(entry.violation_rate) }}</span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-center">
                                    <Link
                                        :href="getEmployeeTripsUrl(entry.user.id)"
                                        class="inline-block rounded-lg border border-cyan-500/30 bg-cyan-500/20 dark:bg-cyan-500/15 px-4 py-2 text-sm font-medium text-cyan-600 dark:text-cyan-400 transition-all duration-200 hover:bg-cyan-500/30 dark:hover:bg-cyan-500/25"
                                        :aria-label="`View trips for ${entry.user.name}`"
                                    >
                                        View Trips
                                    </Link>
                                </td>
                            </motion.tr>
                        </tbody>
                    </table>
                </motion.div>

                <!-- Mobile Card View -->
                <div
                    v-if="hasEntries"
                    class="space-y-4 md:hidden"
                >
                    <motion.div
                        v-for="(entry, index) in leaderboard"
                        :key="entry.user.id"
                        :initial="{ opacity: 0, y: 8 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: index * 0.03, duration: 0.25 }"
                        class="overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                    >
                        <!-- Rank Badge -->
                        <div
                            :class="getRankColorClasses(entry.rank)"
                            class="flex items-center justify-between px-4 py-3 shadow-lg"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold">
                                    Rank #{{ entry.rank }}
                                </span>
                                <Medal
                                    v-if="hasMedal(entry.rank)"
                                    :size="20"
                                    :stroke-width="2"
                                    :class="getMedalColor(entry.rank)"
                                    aria-hidden="true"
                                />
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="space-y-4 p-4">
                            <!-- Employee Info -->
                            <div>
                                <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ entry.user.name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ entry.user.email }}</div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ entry.violation_count }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Violations</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ entry.total_trips }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Trips</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ entry.violation_rate.toFixed(2) }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Rate</div>
                                </div>
                            </div>

                            <!-- View Trips Link -->
                            <Link
                                :href="getEmployeeTripsUrl(entry.user.id)"
                                class="block w-full rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 py-3 text-center font-medium text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl active:scale-95"
                                :aria-label="`View trips for ${entry.user.name}`"
                            >
                                View Trips
                            </Link>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
