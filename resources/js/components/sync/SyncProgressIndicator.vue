<!--
==============================================================================
SYNC PROGRESS INDICATOR COMPONENT
==============================================================================

Floating progress indicator for background sync operations with motion-v animations.
Shows real-time sync progress, success/error states, and allows user interaction.

Features:
- Slide-in from bottom with spring animation
- Circular progress ring animation
- Success checkmark with scale bounce
- Error shake animation
- Auto-dismiss on success (3 seconds)
- Manual dismiss and retry buttons
- SpeedoMontor dark theme styling
- Touch-friendly (≥44px targets)

UX Laws Applied:
- Jakob's Law: Familiar circular progress pattern (like iOS/Android)
- Fitts's Law: Large touch targets (≥44px dismiss/retry buttons)
- Miller's Law: Shows only essential info (X/Y trips, percentage)
- Feedback Principle: Visual, textual, and animated feedback

@example
```vue
<template>
  <SyncProgressIndicator
    :is-syncing="true"
    :current="3"
    :total="10"
    :status="'syncing'"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

/**
 * Sync status type.
 */
type SyncStatus = 'syncing' | 'success' | 'error';

/**
 * Component props.
 */
interface Props {
    /** Whether sync is currently in progress */
    isSyncing?: boolean;

    /** Current trip being synced (1-indexed) */
    current?: number;

    /** Total number of trips to sync */
    total?: number;

    /** Current sync status */
    status?: SyncStatus;

    /** Error message (if status is 'error') */
    errorMessage?: string;

    /** Whether to show the indicator */
    show?: boolean;
}

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when user clicks dismiss button */
    (e: 'dismiss'): void;

    /** Emitted when user clicks retry button (on error) */
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

// ==============================================================================
// State
// ==============================================================================

/**
 * Whether to trigger shake animation on error.
 */
const shouldShake = ref(false);

/**
 * Auto-dismiss timeout ID.
 */
let autoDismissTimeout: ReturnType<typeof setTimeout> | null = null;

// ==============================================================================
// Computed Properties
// ==============================================================================

/**
 * Sync progress percentage (0-100).
 */
const progressPercentage = computed(() => {
    if (props.total === 0) {
return 0;
}

    return Math.round((props.current / props.total) * 100);
});

/**
 * SVG circle properties for progress ring.
 *
 * WHY: Circular progress ring requires calculated SVG stroke-dashoffset.
 */
const circleRadius = 40;
const circleCircumference = 2 * Math.PI * circleRadius;

const strokeDashoffset = computed(() => {
    const progress = progressPercentage.value / 100;

    return circleCircumference * (1 - progress);
});

/**
 * Status text message.
 */
const statusText = computed(() => {
    if (props.status === 'success') {
        return 'Sinkronisasi Berhasil!';
    }

    if (props.status === 'error') {
        return 'Sinkronisasi Gagal';
    }

    return 'Menyinkronkan...';
});

/**
 * Progress text (X/Y trips).
 */
const progressText = computed(() => {
    if (props.status === 'success') {
        return `${props.total} perjalanan tersinkronisasi`;
    }

    if (props.status === 'error') {
        return props.errorMessage || 'Terjadi kesalahan';
    }

    return `${props.current} dari ${props.total} perjalanan`;
});

/**
 * Icon color classes based on status.
 */
const iconColorClass = computed(() => {
    switch (props.status) {
        case 'success':
            return 'text-green-400';
        case 'error':
            return 'text-red-400';
        default:
            return 'text-cyan-400';
    }
});

/**
 * Background color classes based on status.
 */
const bgColorClass = computed(() => {
    switch (props.status) {
        case 'success':
            return 'bg-green-500/10 border-green-500/30';
        case 'error':
            return 'bg-red-500/10 border-red-500/30';
        default:
            return 'bg-cyan-500/10 border-cyan-500/30';
    }
});

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle dismiss button click.
 */
function handleDismiss(): void {
    emit('dismiss');
}

/**
 * Handle retry button click.
 */
function handleRetry(): void {
    emit('retry');
}

/**
 * Trigger shake animation on error.
 */
function triggerShake(): void {
    shouldShake.value = true;
    setTimeout(() => {
        shouldShake.value = false;
    }, 500);
}

// ==============================================================================
// Watchers
// ==============================================================================

/**
 * Watch status for auto-dismiss and animations.
 */
watch(
    () => props.status,
    (newStatus, oldStatus) => {
        // Trigger shake on error
        if (newStatus === 'error' && oldStatus !== 'error') {
            triggerShake();
        }

        // Auto-dismiss on success after 3 seconds
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
    <!-- ================================================================ -->
    <!-- Main Container with motion-v Transition -->
    <!-- ================================================================ -->
    <Transition
        :css="false"
        @enter="
            (el, done) => {
                motion(
                    el,
                    { y: [100, 0], opacity: [0, 1], scale: [0.8, 1] },
                    { duration: 0.4, easing: [0.34, 1.56, 0.64, 1] }
                ).finished.then(done);
            }
        "
        @leave="
            (el, done) => {
                motion(el, { y: 100, opacity: 0, scale: 0.8 }, { duration: 0.3 }).finished.then(done);
            }
        "
    >
        <div
            v-if="show"
            :class="[bgColorClass]"
            class="fixed bottom-4 right-4 z-50 w-80 rounded-lg border p-4 shadow-lg backdrop-blur-sm"
        >
            <!-- ================================================================ -->
            <!-- Header: Status Icon + Text + Dismiss Button -->
            <!-- ================================================================ -->
            <div class="flex items-start justify-between gap-3">
                <!-- Status Icon and Text -->
                <div class="flex items-center gap-3">
                    <!-- Progress Ring (Syncing) -->
                    <div v-if="status === 'syncing'" class="relative flex-shrink-0">
                        <svg class="h-12 w-12 -rotate-90 transform">
                            <!-- Background circle -->
                            <circle
                                :r="circleRadius"
                                class="stroke-current text-gray-700"
                                cx="24"
                                cy="24"
                                fill="none"
                                stroke-width="4"
                            />
                            <!-- Progress circle with motion-v animation -->
                            <motion.circle
                                :r="circleRadius"
                                :animate="{ strokeDashoffset }"
                                :style="{
                                    strokeDasharray: circleCircumference,
                                    strokeDashoffset: circleCircumference,
                                }"
                                :transition="{ duration: 0.5, ease: 'easeInOut' }"
                                :class="iconColorClass"
                                class="stroke-current"
                                cx="24"
                                cy="24"
                                fill="none"
                                stroke-width="4"
                            />
                        </svg>

                        <!-- Percentage text in center -->
                        <div
                            :class="iconColorClass"
                            class="absolute inset-0 flex items-center justify-center text-xs font-bold"
                        >
                            {{ progressPercentage }}%
                        </div>
                    </div>

                    <!-- Success Checkmark with bounce animation -->
                    <Transition
                        :css="false"
                        @enter="
                            (el, done) => {
                                motion(
                                    el,
                                    { scale: [0, 1.2, 1], rotate: [-45, 0] },
                                    { duration: 0.5, easing: [0.34, 1.56, 0.64, 1], delay: 0.2 }
                                ).finished.then(done);
                            }
                        "
                    >
                        <div v-if="status === 'success'" :class="iconColorClass" class="flex-shrink-0">
                            <!-- Checkmark Circle Icon -->
                            <svg
                                class="h-12 w-12"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </Transition>

                    <!-- Error Icon with shake animation -->
                    <motion.div
                        v-if="status === 'error'"
                        :animate="shouldShake ? { x: [-10, 10, -10, 10, 0] } : {}"
                        :class="iconColorClass"
                        :transition="{ duration: 0.5 }"
                        class="flex-shrink-0"
                    >
                        <!-- Error X Circle Icon -->
                        <svg
                            class="h-12 w-12"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle cx="12" cy="12" r="10" />
                            <path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </motion.div>

                    <!-- Status Text -->
                    <div class="min-w-0 flex-1">
                        <div :class="iconColorClass" class="text-sm font-semibold">
                            {{ statusText }}
                        </div>
                        <div class="mt-0.5 text-xs text-gray-400">
                            {{ progressText }}
                        </div>
                    </div>
                </div>

                <!-- Dismiss Button (X) -->
                <button
                    type="button"
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-white/10 hover:text-gray-300 active:bg-white/15"
                    @click="handleDismiss"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <!-- ================================================================ -->
            <!-- Retry Button (Error State Only) -->
            <!-- ================================================================ -->
            <Transition
                :css="false"
                @enter="
                    (el, done) => {
                        motion(el, { opacity: [0, 1], scaleY: [0.5, 1] }, { duration: 0.3, delay: 0.1 }).finished.then(
                            done
                        );
                    }
                "
                @leave="
                    (el, done) => {
                        motion(el, { opacity: 0, scaleY: 0.5 }, { duration: 0.2 }).finished.then(done);
                    }
                "
            >
                <button
                    v-if="status === 'error'"
                    type="button"
                    class="mt-3 w-full rounded-md border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 transition-colors hover:bg-red-500/20 active:bg-red-500/30"
                    @click="handleRetry"
                >
                    Coba Lagi
                </button>
            </Transition>
        </div>
    </Transition>
</template>
