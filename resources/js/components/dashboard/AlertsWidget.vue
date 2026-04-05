<script setup lang="ts">
/**
 * AlertsWidget - Displays recent high-violation alerts.
 *
 * Shows trips with high violation counts from the last hour requiring
 * supervisor attention. Provides quick access to recent incidents for
 * timely intervention.
 */

import { formatDistanceToNow } from 'date-fns';
import type { RecentAlert } from '@/types/dashboard';

interface Props {
    /** Recent alerts from backend */
    alerts: RecentAlert[];

    /** Loading state for skeleton UI */
    isLoading?: boolean;
}

withDefaults(defineProps<Props>(), {
    isLoading: false,
});

/**
 * Format timestamp to relative time.
 *
 * @param isoString - ISO8601 timestamp string
 * @returns Human-readable relative time (e.g., "5 minutes ago")
 */
function formatRelativeTime(isoString: string): string {
    return formatDistanceToNow(new Date(isoString), { addSuffix: true });
}

/**
 * Get severity badge style based on violation count.
 *
 * @param count - Number of violations
 * @returns CSS classes for badge styling
 */
function getSeverityBadge(count: number): string {
    if (count >= 10) {
return 'bg-red-500/20 text-red-400 border-red-500/30';
}

    if (count >= 7) {
return 'bg-orange-500/20 text-orange-400 border-orange-500/30';
}

    return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
}
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 dark:border-white/5 bg-gradient-to-br from-[#1C1C1A] to-[#2A2A28]"
    >
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-white/5 px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚠️</span>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Alerts</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">High violations in the last hour</p>
                </div>
            </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="space-y-3 p-6">
            <div v-for="i in 3" :key="i" class="flex items-center justify-between">
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-32 animate-pulse rounded bg-[#3E3E3A]"></div>
                    <div class="h-3 w-24 animate-pulse rounded bg-[#3E3E3A]"></div>
                </div>
                <div class="h-6 w-16 animate-pulse rounded-full bg-[#3E3E3A]"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="alerts.length === 0" class="px-6 py-8 text-center">
            <div class="mb-2 text-4xl">✅</div>
            <p class="text-sm font-medium text-zinc-900 dark:text-white">No Recent Alerts</p>
            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">All trips are within acceptable limits</p>
        </div>

        <!-- Alerts List -->
        <div v-else class="divide-y divide-[#3E3E3A]">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-white dark:bg-zinc-800/50/50"
            >
                <!-- Alert Info -->
                <div class="flex-1">
                    <p class="font-medium text-zinc-900 dark:text-white">{{ alert.user.name }}</p>
                    <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                        {{ formatRelativeTime(alert.started_at) }}
                    </p>
                </div>

                <!-- Violation Badge -->
                <div
                    :class="getSeverityBadge(alert.violation_count)"
                    class="flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
                >
                    <span>🚨</span>
                    <span>{{ alert.violation_count }} violations</span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div v-if="alerts.length > 0" class="border-t border-zinc-200 dark:border-white/5 px-6 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-500">
                Showing trips with 5+ violations from the last hour
            </p>
        </div>
    </div>
</template>
