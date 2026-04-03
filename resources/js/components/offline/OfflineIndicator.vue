<!--
==============================================================================
OFFLINE INDICATOR COMPONENT
==============================================================================

Visual indicator showing offline mode status with animated badge.
Uses motion-v for smooth entrance/exit animations following UX laws.

Features:
- Animated slide-in from top when offline
- Sync queue count badge with spring animation
- Touch-friendly "Sync Now" button (Fitts's Law: 48px min)
- Indonesian language messages
- Dark theme consistent with VeloTrack branding

UX Laws Applied:
- Jakob's Law: Familiar cloud-off icon and badge pattern
- Fitts's Law: Large touch targets (≥48px)
- Miller's Law: Limited information (status + count + action)
- Hick's Law: Simple single action (Sync Now)

@example
```vue
<template>
  <OfflineIndicator
    :pending-count="5"
    :is-syncing="false"
    @sync="handleSync"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';

import { useOnlineStatus } from '@/composables/useOnlineStatus';

/**
 * Component props.
 */
interface Props {
    /** Number of items pending sync */
    pendingCount?: number;

    /** Whether sync is currently in progress */
    isSyncing?: boolean;

    /** Show sync button even when online (for manual sync) */
    showSyncButton?: boolean;

    /** Whether auto-sync is enabled */
    isAutoSyncEnabled?: boolean;
}

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when user clicks Sync Now button */
    (e: 'sync'): void;
}

const props = withDefaults(defineProps<Props>(), {
    pendingCount: 0,
    isSyncing: false,
    showSyncButton: false,
    isAutoSyncEnabled: true
});

const emit = defineEmits<Emits>();

// ==============================================================================
// Composables
// ==============================================================================

const { isOffline } = useOnlineStatus();

// ==============================================================================
// Computed Properties
// ==============================================================================

/**
 * Whether to show the offline indicator.
 *
 * Shows when offline OR when there are pending items to sync.
 */
const shouldShow = computed(() => isOffline.value || props.pendingCount > 0);

/**
 * Main status message.
 */
const statusMessage = computed(() => {
    if (props.isSyncing) {
        return 'Menyinkronkan...';
    }

    if (isOffline.value) {
        return 'Mode Offline';
    }

    if (props.pendingCount > 0) {
        return 'Menunggu Sinkronisasi';
    }

    return 'Online';
});

/**
 * Detail message below main status.
 */
const detailMessage = computed(() => {
    if (props.isSyncing) {
        return `Mengirim ${props.pendingCount} item...`;
    }

    if (isOffline.value && props.pendingCount > 0) {
        const autoSyncText = props.isAutoSyncEnabled ? ' (auto-sync aktif)' : '';

        return `${props.pendingCount} item menunggu koneksi${autoSyncText}`;
    }

    if (props.pendingCount > 0) {
        const autoSyncText = props.isAutoSyncEnabled ? ' (auto-sync aktif)' : '';

        return `${props.pendingCount} item siap disinkronkan${autoSyncText}`;
    }

    return '';
});

/**
 * Whether to show the sync button.
 */
const showSync = computed(() => {
    // Show if explicitly requested OR if online with pending items
    return props.showSyncButton || (!isOffline.value && props.pendingCount > 0);
});

/**
 * Background color classes based on status.
 */
const bgColorClass = computed(() => {
    if (props.isSyncing) {
        return 'bg-cyan-500/10';
    }

    if (isOffline.value) {
        return 'bg-orange-500/10';
    }

    return 'bg-blue-500/10';
});

/**
 * Border color classes based on status.
 */
const borderColorClass = computed(() => {
    if (props.isSyncing) {
        return 'border-cyan-500/30';
    }

    if (isOffline.value) {
        return 'border-orange-500/30';
    }

    return 'border-blue-500/30';
});

/**
 * Text color classes based on status.
 */
const textColorClass = computed(() => {
    if (props.isSyncing) {
        return 'text-cyan-400';
    }

    if (isOffline.value) {
        return 'text-orange-400';
    }

    return 'text-blue-400';
});

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle sync button click.
 *
 * Emits sync event for parent to handle actual sync logic.
 */
const handleSyncClick = (): void => {
    if (!props.isSyncing) {
        emit('sync');
    }
};
</script>

<template>
    <!-- Main offline indicator container with motion-v animation -->
    <Transition
        :css="false"
        @enter="
            (el, done) => {
                motion(el, { opacity: [0, 1], y: [-20, 0] }, { duration: 0.3, easing: 'ease-out' }).finished.then(
                    done
                );
            }
        "
        @leave="
            (el, done) => {
                motion(el, { opacity: 0, y: -20 }, { duration: 0.2, easing: 'ease-in' }).finished.then(done);
            }
        "
    >
        <div
            v-if="shouldShow"
            :class="[bgColorClass, borderColorClass]"
            class="fixed left-4 right-4 top-4 z-50 rounded-lg border px-4 py-3 shadow-lg backdrop-blur-sm md:left-auto md:right-4 md:w-auto md:min-w-[320px]"
        >
            <!-- ================================================================ -->
            <!-- HEADER: Icon + Status + Badge -->
            <!-- ================================================================ -->
            <div class="flex items-start justify-between gap-3">
                <!-- Icon + Status Text -->
                <div class="flex items-center gap-3">
                    <!-- Cloud Off Icon (Offline) / Cloud Icon (Syncing/Online) -->
                    <div v-if="isOffline" :class="textColorClass" class="flex-shrink-0">
                        <!-- Cloud Off Icon (Simple SVG) -->
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M3 3l18 18M6.5 6.5A6.5 6.5 0 006 9a6 6 0 006 6h7a4 4 0 001.26-.21m2.1-2.1A4 4 0 0019 9h-1.5a6.5 6.5 0 00-11.06-4.56"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <!-- Cloud Icon (Online/Syncing) with rotation/pulse animation -->
                    <Transition
                        :css="false"
                        @enter="
                            (el, done) => {
                                if (isSyncing) {
                                    motion(el, { rotate: 360 }, { duration: 1, repeat: Infinity, easing: 'linear' }).finished.then(
                                        done
                                    );
                                } else if (isAutoSyncEnabled && pendingCount > 0) {
                                    motion(
                                        el,
                                        { scale: [1, 1.1, 1], opacity: [0.8, 1, 0.8] },
                                        { duration: 2, repeat: Infinity, easing: 'easeInOut' }
                                    ).finished.then(done);
                                } else {
                                    motion(el, { opacity: 1 }, { duration: 0.3 }).finished.then(done);
                                }
                            }
                        "
                    >
                        <motion.div
                            v-if="!isOffline"
                            :animate="
                                isSyncing
                                    ? { rotate: 360 }
                                    : isAutoSyncEnabled && pendingCount > 0
                                      ? { scale: [1, 1.1, 1], opacity: [0.8, 1, 0.8] }
                                      : {}
                            "
                            :class="textColorClass"
                            :transition="
                                isSyncing
                                    ? { duration: 1, repeat: Infinity, ease: 'linear' }
                                    : isAutoSyncEnabled && pendingCount > 0
                                      ? { duration: 2, repeat: Infinity, ease: 'easeInOut' }
                                      : {}
                            "
                            class="flex-shrink-0"
                        >
                            <!-- Cloud Icon (Simple SVG) -->
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </motion.div>
                    </Transition>

                    <!-- Status Text -->
                    <div class="min-w-0 flex-1">
                        <div :class="textColorClass" class="text-sm font-semibold">
                            {{ statusMessage }}
                        </div>
                        <div v-if="detailMessage" class="mt-0.5 text-xs text-gray-400">
                            {{ detailMessage }}
                        </div>
                    </div>
                </div>

                <!-- Pending Count Badge (with spring animation) -->
                <Transition
                    :css="false"
                    @enter="
                        (el, done) => {
                            motion(
                                el,
                                { scale: [0, 1.1, 1] },
                                { duration: 0.4, easing: [0.34, 1.56, 0.64, 1] }
                            ).finished.then(done);
                        }
                    "
                    @leave="
                        (el, done) => {
                            motion(el, { scale: 0 }, { duration: 0.2 }).finished.then(done);
                        }
                    "
                >
                    <div
                        v-if="pendingCount > 0"
                        :class="textColorClass"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-bold"
                    >
                        {{ pendingCount > 99 ? '99+' : pendingCount }}
                    </div>
                </Transition>
            </div>

            <!-- ================================================================ -->
            <!-- SYNC BUTTON (Fitts's Law: 48px min height for touch) -->
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
                    v-if="showSync && !isSyncing"
                    type="button"
                    :class="[textColorClass, borderColorClass]"
                    class="mt-3 w-full rounded-md border bg-white/5 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-white/10 active:bg-white/15"
                    @click="handleSyncClick"
                >
                    Sinkronkan Sekarang
                </button>
            </Transition>

            <!-- Syncing Progress Indicator -->
            <Transition
                :css="false"
                @enter="
                    (el, done) => {
                        motion(el, { opacity: [0, 1] }, { duration: 0.3 }).finished.then(done);
                    }
                "
                @leave="
                    (el, done) => {
                        motion(el, { opacity: 0 }, { duration: 0.2 }).finished.then(done);
                    }
                "
            >
                <div v-if="isSyncing" class="mt-3">
                    <div class="h-1 w-full overflow-hidden rounded-full bg-white/10">
                        <!-- Animated progress bar (indeterminate) -->
                        <div
                            :class="[bgColorClass.replace('/10', '/50')]"
                            class="h-full w-1/3 animate-pulse"
                            style="animation: slide 1.5s ease-in-out infinite"
                        />
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
/**
 * Sliding progress bar animation for syncing state.
 */
@keyframes slide {
    0% {
        transform: translateX(-100%);
    }
    50% {
        transform: translateX(200%);
    }
    100% {
        transform: translateX(-100%);
    }
}
</style>
