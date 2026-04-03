<!--
==============================================================================
SYNC QUEUE ITEM COMPONENT
==============================================================================

Displays individual sync queue item with status, retry count, and error details.
Used in SyncQueueModal to show list of pending sync items.

Features:
- Trip date/time display
- Status badge (pending/failed/syncing/completed)
- Retry count indicator
- Error message (collapsible)
- Retry button for failed items
- Touch-friendly targets (≥44px)

UX Laws Applied:
- Fitts's Law: 44px minimum height for list items, retry button
- Miller's Law: Collapsible error messages to reduce cognitive load
- Feedback Principle: Visual feedback on hover/press states
- Color Psychology: Status-specific colors for quick scanning

@example
```vue
<template>
  <SyncQueueItem 
    :item="displayItem"
    :is-retrying="false"
    @retry="handleRetry"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { ref } from 'vue';

import type { SyncQueueItemDisplay } from '@/types/sync';

/**
 * Component props.
 */
interface Props {
    /** Sync queue item display data */
    item: SyncQueueItemDisplay;

    /** Whether this item is currently being retried */
    isRetrying?: boolean;
}

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when retry button is clicked */
    (e: 'retry', itemId: number): void;
}

const props = withDefaults(defineProps<Props>(), {
    isRetrying: false,
});

const emit = defineEmits<Emits>();

// ==============================================================================
// State
// ==============================================================================

/**
 * Whether error message is expanded.
 *
 * WHY: Collapsible errors reduce cognitive load (Miller's Law).
 * Users can expand only the items they're investigating.
 */
const isErrorExpanded = ref(false);

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle retry button click.
 */
function handleRetry(): void {
    emit('retry', props.item.id);
}

/**
 * Toggle error message expansion.
 */
function toggleError(): void {
    isErrorExpanded.value = !isErrorExpanded.value;
}

/**
 * Truncate error message if too long.
 *
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
    <!-- ================================================================ -->
    <!-- Item Container -->
    <!-- ================================================================ -->
    <motion.div
        :whileHover="{ backgroundColor: 'rgba(255, 255, 255, 0.03)' }"
        :transition="{ duration: 0.2 }"
        class="flex flex-col gap-3 rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
    >
        <!-- ================================================================ -->
        <!-- Header Row: Date + Status Badge -->
        <!-- ================================================================ -->
        <div class="flex items-center justify-between gap-3">
            <!-- Trip Date -->
            <div class="flex-1">
                <div class="text-sm font-medium text-[#e5e7eb]">
                    Perjalanan
                </div>
                <div class="text-xs text-[#9ca3af]">
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

        <!-- ================================================================ -->
        <!-- Retry Count (if > 0) -->
        <!-- ================================================================ -->
        <div
            v-if="item.retryCount > 0"
            class="flex items-center gap-2 text-xs text-[#9ca3af]"
        >
            <!-- Retry Icon -->
            <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span>Percobaan: {{ item.retryCount }}/3</span>
        </div>

        <!-- ================================================================ -->
        <!-- Error Message (if failed) -->
        <!-- ================================================================ -->
        <div
            v-if="item.status === 'failed' && item.errorMessage"
            class="flex flex-col gap-2"
        >
            <!-- Error Preview/Toggle -->
            <button
                type="button"
                class="flex items-center gap-2 text-left text-xs text-red-400 transition-colors hover:text-red-300"
                @click="toggleError"
            >
                <!-- Chevron Icon -->
                <svg
                    v-motion="{
                        animate: { rotate: isErrorExpanded ? 180 : 0 },
                        transition: { duration: 0.2 },
                    }"
                    class="h-4 w-4 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <span class="flex-1">
                    {{
                        isErrorExpanded
                            ? 'Sembunyikan Error'
                            : truncateError(item.errorMessage)
                    }}
                </span>
            </button>

            <!-- Full Error Message (Expanded) -->
            <Transition
                :css="false"
                @enter="
                    (el, done) => {
                        motion(
                            el,
                            { height: [0, 'auto'], opacity: [0, 1] },
                            { duration: 0.3 }
                        ).finished.then(done);
                    }
                "
                @leave="
                    (el, done) => {
                        motion(el, { height: 0, opacity: 0 }, { duration: 0.2 }).finished.then(
                            done
                        );
                    }
                "
            >
                <div
                    v-if="isErrorExpanded"
                    class="overflow-hidden rounded border border-red-500/30 bg-red-500/10 p-2 text-xs text-red-400"
                >
                    {{ item.errorMessage }}
                </div>
            </Transition>
        </div>

        <!-- ================================================================ -->
        <!-- Retry Button (if failed and can retry) -->
        <!-- ================================================================ -->
        <Transition
            :css="false"
            @enter="
                (el, done) => {
                    motion(
                        el,
                        { opacity: [0, 1], scaleY: [0.5, 1] },
                        { duration: 0.3, delay: 0.1 }
                    ).finished.then(done);
                }
            "
            @leave="
                (el, done) => {
                    motion(el, { opacity: 0, scaleY: 0.5 }, { duration: 0.2 }).finished.then(done);
                }
            "
        >
            <motion.button
                v-if="item.canRetry && !isRetrying"
                :whileHover="{ scale: 1.02 }"
                :whilePress="{ scale: 0.98 }"
                :transition="{ duration: 0.15 }"
                type="button"
                class="flex items-center justify-center gap-2 rounded-md border border-cyan-500/30 bg-cyan-500/10 px-4 py-2.5 text-sm font-medium text-cyan-400 transition-colors hover:bg-cyan-500/20 active:bg-cyan-500/30"
                @click="handleRetry"
            >
                <!-- Retry Icon -->
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <span>Coba Lagi</span>
            </motion.button>
        </Transition>

        <!-- Retrying State -->
        <div
            v-if="isRetrying"
            class="flex items-center justify-center gap-2 rounded-md border border-cyan-500/30 bg-cyan-500/10 px-4 py-2.5 text-sm text-cyan-400"
        >
            <!-- Spinning Icon -->
            <svg
                v-motion="{
                    animate: { rotate: 360 },
                    transition: { duration: 1, repeat: Infinity, ease: 'linear' },
                }"
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span>Sedang mencoba...</span>
        </div>
    </motion.div>
</template>
