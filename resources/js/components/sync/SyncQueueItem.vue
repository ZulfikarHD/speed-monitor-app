<script setup lang="ts">
/**
 * Sync Queue Item Component
 *
 * Displays individual sync queue item with status, retry count, and error details.
 * Used in SyncQueueModal to show list of pending sync items.
 *
 * Features:
 * - Trip date/time display
 * - Status badge (pending/failed/syncing/completed)
 * - Retry count indicator
 * - Collapsible error message (Miller's Law: reduce cognitive load)
 * - Retry button for failed items
 * - Touch-friendly targets (>=44px)
 *
 * UX Principles:
 * - Fitts's Law: 44px min-height for list items
 * - Miller's Law: Collapsible errors to reduce overwhelm
 * - Feedback: Visual states for all sync statuses
 */

import { ChevronDown, Loader2, RefreshCw } from '@lucide/vue';
import { motion } from 'motion-v';
import { ref } from 'vue';

import type { SyncQueueItemDisplay } from '@/types/sync';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Sync queue item display data */
    item: SyncQueueItemDisplay;
    /** Whether this item is currently being retried */
    isRetrying?: boolean;
}

interface Emits {
    /** Retry button clicked */
    (e: 'retry', itemId: number): void;
}

const props = withDefaults(defineProps<Props>(), {
    isRetrying: false,
});

const emit = defineEmits<Emits>();

// ========================================================================
// State
// ========================================================================

/**
 * WHY: Collapsible errors reduce cognitive load (Miller's Law).
 * Users expand only the items they're investigating.
 */
const isErrorExpanded = ref(false);

// ========================================================================
// Methods
// ========================================================================

function handleRetry(): void {
    emit('retry', props.item.id);
}

function toggleError(): void {
    isErrorExpanded.value = !isErrorExpanded.value;
}

/**
 * WHY: Long error messages break layout and overwhelm users.
 */
function truncateError(message: string, maxLength: number = 100): string {
    if (message.length <= maxLength) {
return message;
}

    return message.substring(0, maxLength) + '...';
}
</script>

<template>
    <!-- Item Container -->
    <div
        class="flex flex-col gap-3 rounded-lg p-4 bg-white dark:bg-zinc-800/50 border border-zinc-200 dark:border-white/10 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/80"
    >
        <!-- Header Row: Date + Status Badge -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex-1">
                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                    Perjalanan
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ item.tripDate }}
                </div>
            </div>

            <!-- Status Badge -->
            <div
                :class="item.statusColor"
                class="rounded-full border px-3 py-1 text-xs font-semibold"
            >
                {{ item.statusLabel }}
            </div>
        </div>

        <!-- Retry Count (if > 0) -->
        <div
            v-if="item.retryCount > 0"
            class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400"
        >
            <RefreshCw :size="14" />
            <span>Percobaan: {{ item.retryCount }}/3</span>
        </div>

        <!-- Error Message (if failed) -->
        <div
            v-if="item.status === 'failed' && item.errorMessage"
            class="flex flex-col gap-2"
        >
            <!-- Error Toggle -->
            <button
                type="button"
                class="flex items-center gap-2 text-left text-xs text-red-600 dark:text-red-400 transition-colors duration-200 hover:text-red-500 dark:hover:text-red-300"
                @click="toggleError"
            >
                <ChevronDown
                    :size="14"
                    class="flex-shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-180': isErrorExpanded }"
                />
                <span class="flex-1">
                    {{ isErrorExpanded ? 'Sembunyikan Error' : truncateError(item.errorMessage) }}
                </span>
            </button>

            <!-- Full Error (Expanded) -->
            <Transition
                :css="false"
                @enter="
                    (el, done) => {
                        motion(
                            el,
                            { height: [0, 'auto'], opacity: [0, 1] },
                            { duration: 0.2 }
                        ).finished.then(done);
                    }
                "
                @leave="
                    (el, done) => {
                        motion(el, { height: 0, opacity: 0 }, { duration: 0.15 }).finished.then(done);
                    }
                "
            >
                <div
                    v-if="isErrorExpanded"
                    class="overflow-hidden rounded border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 p-2 text-xs text-red-600 dark:text-red-400"
                >
                    {{ item.errorMessage }}
                </div>
            </Transition>
        </div>

        <!-- Retry Button (if failed and can retry) -->
        <button
            v-if="item.canRetry && !isRetrying"
            type="button"
            class="flex items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-medium transition-colors duration-200 border border-cyan-200 dark:border-cyan-500/30 bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 hover:bg-cyan-100 dark:hover:bg-cyan-500/20 active:scale-[0.98]"
            @click="handleRetry"
        >
            <RefreshCw :size="14" />
            <span>Coba Lagi</span>
        </button>

        <!-- Retrying State -->
        <div
            v-if="isRetrying"
            class="flex items-center justify-center gap-2 rounded-md border border-cyan-200 dark:border-cyan-500/30 bg-cyan-50 dark:bg-cyan-500/10 px-4 py-2.5 text-sm text-cyan-700 dark:text-cyan-400"
        >
            <Loader2
                :size="14"
                class="animate-spin"
            />
            <span>Sedang mencoba...</span>
        </div>
    </div>
</template>
