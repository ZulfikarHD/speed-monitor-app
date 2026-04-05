<script setup lang="ts">
/**
 * Sync Progress Indicator Component
 *
 * Floating progress indicator for background sync operations.
 * Shows real-time sync progress with circular ring and status feedback.
 *
 * Features:
 * - Slide-in entrance animation
 * - Circular SVG progress ring
 * - Success/error state indicators with lucide icons
 * - Auto-dismiss on success (3 seconds)
 * - Manual dismiss and retry buttons
 * - Theme-aware design with fake glass
 * - Touch-friendly targets (>=44px)
 *
 * UX Principles:
 * - Jakob's Law: Familiar circular progress (iOS/Android pattern)
 * - Fitts's Law: Large touch targets for dismiss/retry
 * - Miller's Law: Only essential info (X/Y trips, percentage)
 * - Feedback: Visual and textual feedback for all states
 */

import { CheckCircle, X, XCircle } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

// ========================================================================
// Types & Props
// ========================================================================

type SyncStatus = 'syncing' | 'success' | 'error';

interface Props {
    /** Whether sync is in progress */
    isSyncing?: boolean;
    /** Current item being synced (1-indexed) */
    current?: number;
    /** Total items to sync */
    total?: number;
    /** Current sync status */
    status?: SyncStatus;
    /** Error message (if status is 'error') */
    errorMessage?: string;
    /** Whether to show the indicator */
    show?: boolean;
}

interface Emits {
    /** User clicks dismiss */
    (e: 'dismiss'): void;
    /** User clicks retry (on error) */
    (e: 'retry'): void;
}

const props = withDefaults(defineProps<Props>(), {
    isSyncing: false,
    current: 0,
    total: 0,
    status: 'syncing',
    errorMessage: '',
    show: true,
});

const emit = defineEmits<Emits>();

// ========================================================================
// State
// ========================================================================

const shouldShake = ref(false);
let autoDismissTimeout: ReturnType<typeof setTimeout> | null = null;

// ========================================================================
// Computed
// ========================================================================

const progressPercentage = computed(() => {
    if (props.total === 0) {
return 0;
}

    return Math.round((props.current / props.total) * 100);
});

/**
 * WHY: Circular progress ring requires calculated SVG stroke-dashoffset.
 */
const circleRadius = 40;
const circleCircumference = 2 * Math.PI * circleRadius;

const strokeDashoffset = computed(() => {
    const progress = progressPercentage.value / 100;

    return circleCircumference * (1 - progress);
});

const statusText = computed(() => {
    if (props.status === 'success') {
return 'Sinkronisasi Berhasil!';
}

    if (props.status === 'error') {
return 'Sinkronisasi Gagal';
}

    return 'Menyinkronkan...';
});

const progressText = computed(() => {
    if (props.status === 'success') {
return `${props.total} perjalanan tersinkronisasi`;
}

    if (props.status === 'error') {
return props.errorMessage || 'Terjadi kesalahan';
}

    return `${props.current} dari ${props.total} perjalanan`;
});

const iconColorClass = computed(() => {
    switch (props.status) {
        case 'success': return 'text-emerald-600 dark:text-emerald-400';
        case 'error': return 'text-red-600 dark:text-red-400';
        default: return 'text-cyan-600 dark:text-cyan-400';
    }
});

const bgColorClass = computed(() => {
    switch (props.status) {
        case 'success': return 'border-emerald-200 dark:border-emerald-500/30';
        case 'error': return 'border-red-200 dark:border-red-500/30';
        default: return 'border-cyan-200 dark:border-cyan-500/30';
    }
});

// ========================================================================
// Methods
// ========================================================================

function handleDismiss(): void {
    emit('dismiss');
}

function handleRetry(): void {
    emit('retry');
}

function triggerShake(): void {
    shouldShake.value = true;
    setTimeout(() => {
 shouldShake.value = false; 
}, 500);
}

// ========================================================================
// Watchers
// ========================================================================

watch(
    () => props.status,
    (newStatus, oldStatus) => {
        if (newStatus === 'error' && oldStatus !== 'error') {
            triggerShake();
        }

        if (newStatus === 'success') {
            if (autoDismissTimeout) {
clearTimeout(autoDismissTimeout);
}

            autoDismissTimeout = setTimeout(() => {
 handleDismiss(); 
}, 3000);
        }
    },
);
</script>

<template>
    <!-- Sync Progress Indicator -->
    <Transition
        :css="false"
        @enter="
            (el, done) => {
                motion(
                    el,
                    { y: [80, 0], opacity: [0, 1] },
                    { duration: 0.3 }
                ).finished.then(done);
            }
        "
        @leave="
            (el, done) => {
                motion(el, { y: 80, opacity: 0 }, { duration: 0.2 }).finished.then(done);
            }
        "
    >
        <div
            v-if="show"
            :class="[bgColorClass]"
            class="fixed bottom-4 right-4 z-50 w-80 rounded-lg border p-4 shadow-lg bg-white/95 dark:bg-zinc-900/98 ring-1 ring-white/20 dark:ring-white/5"
        >
            <!-- Header: Status + Dismiss -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <!-- Progress Ring (Syncing) -->
                    <div
                        v-if="status === 'syncing'"
                        class="relative flex-shrink-0"
                    >
                        <svg class="h-12 w-12 -rotate-90 transform">
                            <circle
                                :r="circleRadius"
                                class="stroke-current text-zinc-200 dark:text-zinc-700"
                                cx="24"
                                cy="24"
                                fill="none"
                                stroke-width="4"
                            />
                            <motion.circle
                                :r="circleRadius"
                                :animate="{ strokeDashoffset }"
                                :style="{
                                    strokeDasharray: circleCircumference,
                                    strokeDashoffset: circleCircumference,
                                }"
                                :transition="{ duration: 0.5 }"
                                :class="iconColorClass"
                                class="stroke-current"
                                cx="24"
                                cy="24"
                                fill="none"
                                stroke-width="4"
                            />
                        </svg>
                        <div
                            :class="iconColorClass"
                            class="absolute inset-0 flex items-center justify-center text-xs font-bold"
                        >
                            {{ progressPercentage }}%
                        </div>
                    </div>

                    <!-- Success Icon -->
                    <CheckCircle
                        v-if="status === 'success'"
                        :size="48"
                        :class="iconColorClass"
                        class="flex-shrink-0"
                    />

                    <!-- Error Icon -->
                    <motion.div
                        v-if="status === 'error'"
                        :animate="shouldShake ? { x: [-8, 8, -8, 8, 0] } : {}"
                        :transition="{ duration: 0.4 }"
                        class="flex-shrink-0"
                    >
                        <XCircle
                            :size="48"
                            :class="iconColorClass"
                        />
                    </motion.div>

                    <!-- Status Text -->
                    <div class="min-w-0 flex-1">
                        <div :class="iconColorClass" class="text-sm font-semibold">
                            {{ statusText }}
                        </div>
                        <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ progressText }}
                        </div>
                    </div>
                </div>

                <!-- Dismiss Button -->
                <button
                    type="button"
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full transition-colors duration-200 text-zinc-400 dark:text-zinc-500 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-700 dark:hover:text-zinc-300"
                    @click="handleDismiss"
                >
                    <X :size="16" />
                </button>
            </div>

            <!-- Retry Button (Error Only) -->
            <Transition
                :css="false"
                @enter="
                    (el, done) => {
                        motion(el, { opacity: [0, 1] }, { duration: 0.2, delay: 0.1 }).finished.then(done);
                    }
                "
                @leave="
                    (el, done) => {
                        motion(el, { opacity: 0 }, { duration: 0.15 }).finished.then(done);
                    }
                "
            >
                <button
                    v-if="status === 'error'"
                    type="button"
                    class="mt-3 w-full rounded-md border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 transition-colors duration-200 hover:bg-red-100 dark:hover:bg-red-500/20"
                    @click="handleRetry"
                >
                    Coba Lagi
                </button>
            </Transition>
        </div>
    </Transition>
</template>
