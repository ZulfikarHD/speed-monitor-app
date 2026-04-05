<script setup lang="ts">
/**
 * RecentViolationsList Component
 *
 * Top violators leaderboard for supervisor dashboard showing employees
 * with highest violation counts for today (max 5 entries).
 *
 * Features:
 * - Ranked list with emoji medals (🥇🥈🥉) for top 3
 * - Gradient badges for visual ranking
 * - Employee name + violation count
 * - Empty state when no violations
 * - motion-v slide-in animations
 * - Color-coded severity (red gradient for high violations)
 */

import { motion } from 'motion-v';
import { computed } from 'vue';

import type { TopViolator } from '@/types/dashboard';

// ========================================================================
// Component Props
// ========================================================================

interface Props {
    /** List of top violators (max 5) */
    violators: TopViolator[];

    /** Loading state for skeleton UI */
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Check if list has data.
 */
const hasViolators = computed(() => props.violators.length > 0);

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
        4: 'bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#1b1b18]',
        5: 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white',
    };

    return colorMap[rank] || 'bg-[#3E3E3A] text-zinc-900 dark:text-white';
}
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50">
        <!-- ======================================================================
            Header
        ======================================================================= -->
        <div class="flex items-start justify-between border-b border-zinc-200 dark:border-white/5 px-6 py-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    Recent Violations
                </h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Top 5 employees by violation count today
                </p>
            </div>
            <div v-if="$slots.actions" class="ml-4">
                <slot name="actions" />
            </div>
        </div>

        <!-- ======================================================================
            Loading State (Skeleton)
        ======================================================================= -->
        <div
            v-if="isLoading"
            class="divide-y divide-[#3E3E3A]"
        >
            <div
                v-for="i in 3"
                :key="`skeleton-${i}`"
                class="px-6 py-4"
            >
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 flex-shrink-0 animate-pulse rounded-full bg-[#3E3E3A]"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-1/2 animate-pulse rounded bg-[#3E3E3A]"></div>
                        <div class="h-3 w-1/3 animate-pulse rounded bg-[#3E3E3A]"></div>
                    </div>
                    <div class="h-8 w-16 animate-pulse rounded-full bg-[#3E3E3A]"></div>
                </div>
            </div>
        </div>

        <!-- ======================================================================
            Empty State
        ======================================================================= -->
        <div
            v-else-if="!hasViolators"
            class="px-6 py-12 text-center"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 0.4 }"
            >
                <div class="mb-4 text-5xl">✅</div>
                <p class="text-lg font-medium text-zinc-900 dark:text-white">
                    No Violations Today
                </p>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    All employees are driving safely within speed limits
                </p>
            </motion.div>
        </div>

        <!-- ======================================================================
            Violations List
        ======================================================================= -->
        <div
            v-else
            class="divide-y divide-[#3E3E3A]"
        >
            <motion.div
                v-for="(violator, index) in violators"
                :key="`${violator.user.email}-${index}`"
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{
                    delay: index * 0.08,
                    duration: 0.4,
                    type: 'spring',
                    bounce: 0.3,
                }"
                class="px-6 py-4 transition-colors hover:bg-zinc-50 dark:hover:bg-white/5"
            >
                <div class="flex items-center gap-4">
                    <!-- Rank Badge with Medal -->
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full font-bold shadow-lg"
                        :class="getRankColorClasses(index + 1)"
                    >
                        <span
                            v-if="getMedal(index + 1)"
                            class="text-xl"
                            :aria-label="`Rank ${index + 1}`"
                        >
                            {{ getMedal(index + 1) }}
                        </span>
                        <span
                            v-else
                            class="text-sm"
                            :aria-label="`Rank ${index + 1}`"
                        >
                            {{ index + 1 }}
                        </span>
                    </div>

                    <!-- Employee Info -->
                    <div class="flex-1 min-w-0">
                        <p class="truncate font-medium text-zinc-900 dark:text-white">
                            {{ violator.user.name }}
                        </p>
                        <p class="mt-1 truncate text-sm text-zinc-600 dark:text-zinc-400">
                            {{ violator.user.email }}
                        </p>
                    </div>

                    <!-- Violation Count Badge -->
                    <div>
                        <span
                            class="inline-flex items-center rounded-full bg-red-500/10 px-3 py-1 text-sm font-semibold text-red-400"
                            :aria-label="`${violator.violation_count} violations`"
                        >
                            {{ violator.violation_count }}
                            <span class="ml-1 text-xs font-normal">
                                {{ violator.violation_count === 1 ? 'violation' : 'violations' }}
                            </span>
                        </span>
                    </div>
                </div>
            </motion.div>
        </div>
    </div>
</template>
