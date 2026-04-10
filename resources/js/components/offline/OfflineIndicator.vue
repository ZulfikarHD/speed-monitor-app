<!--
==============================================================================
OFFLINE INDICATOR COMPONENT
==============================================================================

Visual indicator showing offline mode status with animated badge.
Uses Vue CSS transitions for entrance/exit following UX laws.

Features:
- Animated slide-in from top when offline
- Sync queue count badge with spring animation
- Touch-friendly "Sync Now" button (Fitts's Law: 44px min)
- Indonesian language messages
- Dark theme consistent with SafeTrack branding

UX Laws Applied:
- Jakob's Law: Familiar cloud-off icon and badge pattern
- Fitts's Law: Large touch targets (≥44px)
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
import { Cloud, CloudOff, RefreshCw } from '@lucide/vue';
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
    <Transition name="slide">
        <div
            v-if="shouldShow"
            class="fixed left-4 right-4 top-4 z-50 overflow-hidden rounded-lg border border-zinc-200/80 bg-white/95 shadow-lg ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-900/98 dark:ring-white/5 md:left-auto md:right-4 md:w-auto md:min-w-[320px]"
        >
            <div :class="bgColorClass" class="pointer-events-none absolute inset-0" aria-hidden="true" />
            <div class="relative z-10 px-4 py-3">
            <!-- ================================================================ -->
            <!-- HEADER: Icon + Status + Badge -->
            <!-- ================================================================ -->
            <div class="flex items-start justify-between gap-3">
                <!-- Icon + Status Text -->
                <div class="flex min-h-[44px] items-center gap-3">
                    <!-- Offline -->
                    <div v-if="isOffline && !isSyncing" :class="textColorClass" class="flex shrink-0 items-center">
                        <CloudOff :size="24" :stroke-width="2" />
                    </div>

                    <!-- Syncing -->
                    <div v-else-if="isSyncing" :class="textColorClass" class="flex shrink-0 items-center">
                        <RefreshCw :size="24" :stroke-width="2" class="animate-spin" />
                    </div>

                    <!-- Online / pending -->
                    <div
                        v-else
                        :class="[textColorClass, isAutoSyncEnabled && pendingCount > 0 ? 'animate-pulse' : '']"
                        class="flex shrink-0 items-center"
                    >
                        <Cloud :size="24" :stroke-width="2" />
                    </div>

                    <!-- Status Text -->
                    <div class="min-w-0 flex-1">
                        <div :class="textColorClass" class="text-sm font-semibold">
                            {{ statusMessage }}
                        </div>
                        <div v-if="detailMessage" class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ detailMessage }}
                        </div>
                    </div>
                </div>

                <!-- Pending Count Badge -->
                <Transition name="fade-scale">
                    <div
                        v-if="pendingCount > 0"
                        :class="textColorClass"
                        class="flex h-11 min-w-[44px] shrink-0 items-center justify-center rounded-full bg-zinc-100/90 px-2 text-xs font-bold dark:bg-white/10"
                    >
                        {{ pendingCount > 99 ? '99+' : pendingCount }}
                    </div>
                </Transition>
            </div>

            <!-- ================================================================ -->
            <!-- SYNC BUTTON (44px min touch target) -->
            <!-- ================================================================ -->
            <Transition name="fade-scale">
                <button
                    v-if="showSync && !isSyncing"
                    type="button"
                    :class="[textColorClass, borderColorClass]"
                    class="mt-3 min-h-[44px] w-full rounded-md border bg-white/80 px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:bg-zinc-50 active:scale-[0.98] dark:bg-zinc-800/80 dark:hover:bg-zinc-700/80"
                    @click="handleSyncClick"
                >
                    Sinkronkan Sekarang
                </button>
            </Transition>

            <!-- Syncing Progress Indicator -->
            <Transition name="fade">
                <div v-if="isSyncing" class="mt-3">
                    <div class="h-1 w-full overflow-hidden rounded-full bg-zinc-200/80 dark:bg-white/10">
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
        </div>
    </Transition>
</template>

<style scoped>
.slide-enter-active {
    transition: all 0.3s ease-out;
}

.slide-leave-active {
    transition: all 0.2s ease-in;
}

.slide-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-scale-enter-active {
    transition: all 0.25s ease-out;
}

.fade-scale-leave-active {
    transition: all 0.2s ease-in;
}

.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.85);
}

.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.85);
}

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
