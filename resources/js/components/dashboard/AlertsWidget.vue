<script setup lang="ts">
/**
 * AlertsWidget - Displays recent high-violation alerts.
 *
 * Shows trips with high violation counts from the last hour requiring
 * superuser attention. Uses severity-based color coding for quick
 * visual identification of critical incidents.
 *
 * Features:
 * - Severity-based badge styling (yellow/orange/red)
 * - Relative time formatting
 * - Empty state with SVG icon
 * - Skeleton loading state
 * - Full light/dark theme support
 */

import { AlertTriangle, CheckCircle, ShieldAlert } from '@lucide/vue';
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
        return 'bg-red-500/20 dark:bg-red-500/15 text-red-700 dark:text-red-400 border-red-500/30';
    }

    if (count >= 7) {
        return 'bg-orange-500/20 dark:bg-orange-500/15 text-orange-700 dark:text-orange-400 border-orange-500/30';
    }

    return 'bg-yellow-500/20 dark:bg-yellow-500/15 text-yellow-700 dark:text-yellow-400 border-yellow-500/30';
}
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
    >
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-white/5 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/20 dark:bg-amber-500/15">
                    <AlertTriangle
                        :size="18"
                        :stroke-width="2"
                        class="text-amber-600 dark:text-amber-400"
                        aria-hidden="true"
                    />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Peringatan Terbaru</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Pelanggaran tinggi dalam satu jam terakhir</p>
                </div>
            </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="space-y-3 p-6">
            <div v-for="i in 3" :key="i" class="flex items-center justify-between">
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-32 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-3 w-24 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
                <div class="h-6 w-16 animate-pulse rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="alerts.length === 0" class="px-6 py-8 text-center">
            <CheckCircle
                :size="40"
                :stroke-width="1.5"
                class="mx-auto mb-2 text-emerald-500 dark:text-emerald-400"
                aria-hidden="true"
            />
            <p class="text-sm font-medium text-zinc-900 dark:text-white">Tidak Ada Peringatan Terbaru</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Semua perjalanan dalam batas yang dapat diterima</p>
        </div>

        <!-- Alerts List -->
        <div v-else class="divide-y divide-zinc-200 dark:divide-zinc-800">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="flex items-center justify-between px-6 py-4 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-white/5"
            >
                <!-- Alert Info -->
                <div class="flex-1">
                    <p class="font-medium text-zinc-900 dark:text-white">{{ alert.user.name }}</p>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ formatRelativeTime(alert.started_at) }}
                    </p>
                </div>

                <!-- Violation Badge -->
                <div
                    :class="getSeverityBadge(alert.violation_count)"
                    class="flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
                >
                    <ShieldAlert :size="12" :stroke-width="2" aria-hidden="true" />
                    <span>{{ alert.violation_count }} pelanggaran</span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div v-if="alerts.length > 0" class="border-t border-zinc-200 dark:border-white/5 px-6 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-500">
                Menampilkan perjalanan dengan 5+ pelanggaran dari satu jam terakhir
            </p>
        </div>
    </div>
</template>
