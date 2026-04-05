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
 * - Medal emojis for top 3 (🥇🥈🥉)
 * - Gradient rank badges matching design system
 * - Click "View Trips" to navigate to filtered All Trips page
 * - motion-v animations (stagger, hover, page entry)
 * - Empty state when no violations in period
 * - Responsive design (mobile/tablet/desktop)
 *
 * @example Route: /supervisor/leaderboard?date_from=2026-03-01&date_to=2026-04-01
 */

import { router } from '@inertiajs/vue3';
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

/**
 * Check if leaderboard has entries.
 */
const hasEntries = computed(() => props.leaderboard.length > 0);

/**
 * Check if filters have been modified.
 */
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
 * Get medal emoji for ranking position.
 *
 * Awards gold/silver/bronze medals for top 3 positions.
 *
 * @param rank - Position in leaderboard (1-indexed)
 * @returns Medal emoji or empty string
 */
function getMedal(rank: number): string {
    const medals: Record<number, string> = {
        1: '🥇',
        2: '🥈',
        3: '🥉',
    };

    return medals[rank] || '';
}

/**
 * Get color classes for rank badge.
 *
 * Higher ranks get stronger red gradients to indicate severity.
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

    return colorMap[rank] || 'bg-[#3E3E3A] text-[#EDEDEC]';
}

/**
 * Format violation rate for display.
 *
 * @param rate - Violation rate (violations per trip)
 * @returns Formatted string (e.g., "2.5 / trip")
 */
function formatRate(rate: number): string {
    return `${rate.toFixed(2)} / trip`;
}

/**
 * Apply date filters.
 *
 * Navigates to leaderboard page with new filter query parameters.
 * Uses Inertia visit to preserve SPA behavior.
 */
function applyFilters(): void {
    // Validate date range
    if (localFilters.value.date_from > localFilters.value.date_to) {
        alert('Start date must be before or equal to end date');

        return;
    }

    // Navigate with new filters
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
 *
 * Sets date range to last 30 days and applies filters immediately.
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
 * Navigate to employee's trips with filters.
 *
 * Opens All Trips page pre-filtered to show only this employee's
 * violation trips within the current date range.
 *
 * @param userId - Employee user ID
 */
function viewEmployeeTrips(userId: number): void {
    router.visit(allTripsIndex.url({
        query: {
            user_id: userId,
            date_from: props.filters.date_from,
            date_to: props.filters.date_to,
            violations_only: true,
        },
    }));
}
</script>

<template>
    <SupervisorLayout title="Violation Leaderboard">
        <div class="min-h-screen bg-[#0a0c0f] p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- ======================================================================
                    Header Section
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
                >
                    <div class="flex flex-col gap-4">
                        <!-- Title -->
                        <div>
                            <h1 class="text-3xl font-bold text-[#EDEDEC] md:text-4xl">
                                Violation Leaderboard
                            </h1>
                            <p class="mt-1 text-sm text-[#A1A09A]">
                                Employees ranked by speed violations
                            </p>
                        </div>

                        <!-- Date Range Filters -->
                        <motion.div
                            :initial="{ opacity: 0, x: 20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ delay: 0.2, duration: 0.4 }"
                            class="flex flex-col gap-3 rounded-lg border border-[#3E3E3A] bg-[#161615] p-4 sm:flex-row sm:items-end"
                        >
                            <!-- From Date -->
                            <div class="flex-1">
                                <label
                                    for="date-from"
                                    class="mb-1 block text-sm font-medium text-[#A1A09A]"
                                >
                                    From Date
                                </label>
                                <input
                                    id="date-from"
                                    v-model="localFilters.date_from"
                                    type="date"
                                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-[#EDEDEC] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                />
                            </div>

                            <!-- To Date -->
                            <div class="flex-1">
                                <label
                                    for="date-to"
                                    class="mb-1 block text-sm font-medium text-[#A1A09A]"
                                >
                                    To Date
                                </label>
                                <input
                                    id="date-to"
                                    v-model="localFilters.date_to"
                                    type="date"
                                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-[#EDEDEC] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <!-- Apply Button -->
                                <motion.button
                                    @click="applyFilters"
                                    :disabled="!filtersModified"
                                    :whileHover="{ scale: filtersModified ? 1.02 : 1 }"
                                    :whilePress="{ scale: filtersModified ? 0.98 : 1 }"
                                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                                    class="rounded-lg bg-cyan-500 px-4 py-2 font-medium text-white transition-colors hover:bg-cyan-600 disabled:cursor-not-allowed disabled:opacity-50"
                                    :aria-label="'Apply filters'"
                                >
                                    Apply
                                </motion.button>

                                <!-- Reset Button -->
                                <motion.button
                                    @click="resetFilters"
                                    :whileHover="{ scale: 1.02 }"
                                    :whilePress="{ scale: 0.98 }"
                                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                                    class="rounded-lg border border-[#3E3E3A] bg-[#1b1b18] px-4 py-2 font-medium text-[#EDEDEC] transition-colors hover:bg-[#3E3E3A]"
                                    :aria-label="'Reset filters'"
                                >
                                    Reset
                                </motion.button>
                            </div>
                        </motion.div>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Empty State
                ======================================================================= -->
                <motion.div
                    v-if="!hasEntries"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ delay: 0.3, duration: 0.4 }"
                    class="rounded-lg border border-[#3E3E3A] bg-[#161615] p-12 text-center"
                >
                    <div class="mx-auto max-w-md">
                        <span class="text-6xl" aria-hidden="true">🏆</span>
                        <h3 class="mt-4 text-xl font-semibold text-[#EDEDEC]">
                            No Violations Recorded
                        </h3>
                        <p class="mt-2 text-sm text-[#A1A09A]">
                            No speed violations were recorded in the selected period.
                            All employees are driving safely!
                        </p>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Desktop Table View
                ======================================================================= -->
                <motion.div
                    v-if="hasEntries"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3, duration: 0.5 }"
                    class="hidden overflow-hidden rounded-lg border border-[#3E3E3A] bg-[#161615] md:block"
                >
                    <table class="w-full">
                        <!-- Table Header -->
                        <thead class="border-b border-[#3E3E3A] bg-[#1b1b18]">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Employee
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Violations
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Total Trips
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Violation Rate
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wide text-[#A1A09A]"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            <motion.tr
                                v-for="(entry, index) in leaderboard"
                                :key="entry.user.id"
                                :initial="{ opacity: 0, x: -20 }"
                                :animate="{ opacity: 1, x: 0 }"
                                :transition="{
                                    delay: index * 0.05,
                                    duration: 0.4,
                                    type: 'spring',
                                    bounce: 0.3,
                                }"
                                class="group border-b border-[#3E3E3A] transition-colors hover:bg-[#1b1b18]"
                            >
                                <!-- Rank -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="getRankColorClasses(entry.rank)"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                                        >
                                            {{ entry.rank }}
                                        </span>
                                        <span
                                            v-if="getMedal(entry.rank)"
                                            class="text-2xl"
                                            aria-hidden="true"
                                        >
                                            {{ getMedal(entry.rank) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Employee -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-[#EDEDEC]">
                                            {{ entry.user.name }}
                                        </div>
                                        <div class="text-sm text-[#A1A09A]">
                                            {{ entry.user.email }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Violations -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-3 py-1 text-sm font-semibold text-red-400"
                                    >
                                        <span aria-hidden="true">⚠️</span>
                                        {{ entry.violation_count }}
                                    </span>
                                </td>

                                <!-- Total Trips -->
                                <td class="px-6 py-4">
                                    <span class="text-[#EDEDEC]">
                                        {{ entry.total_trips }}
                                    </span>
                                </td>

                                <!-- Violation Rate -->
                                <td class="px-6 py-4">
                                    <span class="text-[#A1A09A]">
                                        {{ formatRate(entry.violation_rate) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-center">
                                    <motion.button
                                        @click="viewEmployeeTrips(entry.user.id)"
                                        :whileHover="{ scale: 1.05 }"
                                        :whilePress="{ scale: 0.95 }"
                                        :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                                        class="rounded-lg bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-400 transition-colors hover:bg-cyan-500/20"
                                        :aria-label="`View trips for ${entry.user.name}`"
                                    >
                                        View Trips
                                    </motion.button>
                                </td>
                            </motion.tr>
                        </tbody>
                    </table>
                </motion.div>

                <!-- ======================================================================
                    Mobile Card View
                ======================================================================= -->
                <div
                    v-if="hasEntries"
                    class="space-y-4 md:hidden"
                >
                    <motion.div
                        v-for="(entry, index) in leaderboard"
                        :key="entry.user.id"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{
                            delay: index * 0.05,
                            duration: 0.4,
                            type: 'spring',
                            bounce: 0.3,
                        }"
                        class="overflow-hidden rounded-lg border border-[#3E3E3A] bg-[#161615]"
                    >
                        <!-- Rank Badge -->
                        <div
                            :class="getRankColorClasses(entry.rank)"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold">
                                    Rank #{{ entry.rank }}
                                </span>
                                <span
                                    v-if="getMedal(entry.rank)"
                                    class="text-2xl"
                                    aria-hidden="true"
                                >
                                    {{ getMedal(entry.rank) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-4 space-y-4">
                            <!-- Employee Info -->
                            <div>
                                <div class="text-lg font-semibold text-[#EDEDEC]">
                                    {{ entry.user.name }}
                                </div>
                                <div class="text-sm text-[#A1A09A]">
                                    {{ entry.user.email }}
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Violations -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-400">
                                        {{ entry.violation_count }}
                                    </div>
                                    <div class="text-xs text-[#A1A09A]">
                                        Violations
                                    </div>
                                </div>

                                <!-- Trips -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-[#EDEDEC]">
                                        {{ entry.total_trips }}
                                    </div>
                                    <div class="text-xs text-[#A1A09A]">
                                        Trips
                                    </div>
                                </div>

                                <!-- Rate -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-400">
                                        {{ entry.violation_rate.toFixed(2) }}
                                    </div>
                                    <div class="text-xs text-[#A1A09A]">
                                        Rate
                                    </div>
                                </div>
                            </div>

                            <!-- View Trips Button -->
                            <motion.button
                                @click="viewEmployeeTrips(entry.user.id)"
                                :whilePress="{ scale: 0.98 }"
                                :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                                class="w-full rounded-lg bg-cyan-500 py-3 font-medium text-white transition-colors hover:bg-cyan-600"
                                :aria-label="`View trips for ${entry.user.name}`"
                            >
                                View Trips
                            </motion.button>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
