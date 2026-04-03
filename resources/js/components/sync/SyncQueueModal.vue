<!--
==============================================================================
SYNC QUEUE MODAL COMPONENT
==============================================================================

Full-screen modal showing sync queue details with manual sync controls.
Displays pending items, last sync timestamp, and allows manual synchronization.

Features:
- Full-screen on mobile (bottom sheet style)
- Centered 600px width on desktop
- Header with title + close button + last sync time
- Stats row showing pending count and status
- Scrollable list of pending items (max 10 visible)
- Manual "Sync Now" button (prominent CTA)
- Empty state when no pending items
- Real-time updates during sync
- Integration with SyncProgressIndicator
- Backdrop dismiss + ESC key support

UX Laws Applied:
- Miller's Law: Display max 10 items initially, scroll for more
- Fitts's Law: Large sync button (56px), close button (44px)
- Jakob's Law: Familiar modal patterns (backdrop, ESC key, X button)
- Feedback Principle: Loading states, progress indicators, animations

@example
```vue
<template>
  <SyncQueueModal 
    :show="isModalOpen"
    @close="closeModal"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

import SyncProgressIndicator from '@/components/sync/SyncProgressIndicator.vue';
import SyncQueueItem from '@/components/sync/SyncQueueItem.vue';
import { useSyncQueue } from '@/composables/useSyncQueue';

/**
 * Component props.
 */
interface Props {
    /** Whether modal is visible */
    show?: boolean;
}

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when modal should close */
    (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
});

const emit = defineEmits<Emits>();

// ==============================================================================
// Dependencies
// ==============================================================================

const {
    pendingCount,
    displayItems,
    isSyncing,
    isOnline,
    formattedLastSyncTime,
    lastSyncResult,
    currentProgress,
    syncNow,
    retryFailedItem,
} = useSyncQueue();

// ==============================================================================
// State
// ==============================================================================

/**
 * ID of item currently being retried.
 */
const retryingItemId = ref<number | null>(null);

/**
 * Whether to show sync progress indicator.
 *
 * WHY: Shows during manual and auto-sync operations.
 */
const showProgress = computed(() => {
    return isSyncing.value && currentProgress.value !== undefined;
});

// ==============================================================================
// Computed Properties
// ==============================================================================

/**
 * Whether there are any pending items.
 */
const hasPendingItems = computed(() => {
    return pendingCount.value > 0;
});

/**
 * Whether sync button should be disabled.
 *
 * WHY: Prevents duplicate sync operations and shows when offline.
 */
const isSyncDisabled = computed(() => {
    return isSyncing.value || !isOnline.value || pendingCount.value === 0;
});

/**
 * Sync button text based on state.
 */
const syncButtonText = computed(() => {
    if (isSyncing.value) {
        return 'Sedang Sync...';
    }

    if (!isOnline.value) {
        return 'Offline';
    }

    if (pendingCount.value === 0) {
        return 'Tidak Ada Item';
    }

    return `Sync Sekarang (${pendingCount.value})`;
});

/**
 * Last sync result summary text.
 */
const lastSyncResultText = computed(() => {
    if (!lastSyncResult.value) {
        return null;
    }

    const { successCount, failureCount } = lastSyncResult.value;

    if (failureCount === 0) {
        return `✓ ${successCount} berhasil`;
    }

    if (successCount === 0) {
        return `✗ ${failureCount} gagal`;
    }

    return `${successCount} berhasil, ${failureCount} gagal`;
});

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle close button click.
 */
function handleClose(): void {
    emit('close');
}

/**
 * Handle backdrop click.
 */
function handleBackdropClick(event: MouseEvent): void {
    // Only close if clicking backdrop itself, not modal content
    if (event.target === event.currentTarget) {
        handleClose();
    }
}

/**
 * Handle manual sync button click.
 */
async function handleSyncNow(): Promise<void> {
    await syncNow();
}

/**
 * Handle retry button click for individual item.
 */
async function handleRetryItem(itemId: number): Promise<void> {
    retryingItemId.value = itemId;

    try {
        await retryFailedItem(itemId);
    } finally {
        retryingItemId.value = null;
    }
}

// ==============================================================================
// Watchers
// ==============================================================================

/**
 * Watch show prop to handle body scroll lock.
 *
 * WHY: Prevents background scrolling when modal is open.
 */
watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <!-- ================================================================ -->
    <!-- Modal Teleport to Body (for proper z-index) -->
    <!-- ================================================================ -->
    <Teleport to="body">
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
            <!-- Backdrop -->
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm md:items-center"
                @click="handleBackdropClick"
            >
                <!-- ================================================================ -->
                <!-- Modal Container (Slide Up Animation) -->
                <!-- ================================================================ -->
                <Transition
                    :css="false"
                    @enter="
                        (el, done) => {
                            motion(
                                el,
                                { y: [100, 0], opacity: [0, 1] },
                                { duration: 0.4, easing: [0.32, 0.72, 0, 1] }
                            ).finished.then(done);
                        }
                    "
                    @leave="
                        (el, done) => {
                            motion(
                                el,
                                { y: 100, opacity: 0 },
                                { duration: 0.3 }
                            ).finished.then(done);
                        }
                    "
                >
                    <div
                        v-if="show"
                        class="flex max-h-[90vh] w-full flex-col overflow-hidden rounded-t-2xl border-t border-[#3E3E3A] bg-[#0a0c0f] shadow-2xl md:max-h-[80vh] md:w-[600px] md:rounded-2xl md:border"
                    >
                        <!-- ================================================================ -->
                        <!-- Header Section -->
                        <!-- ================================================================ -->
                        <div
                            class="flex items-center justify-between border-b border-[#3E3E3A] px-6 py-4"
                        >
                            <!-- Title + Last Sync -->
                            <div class="flex-1">
                                <h2 class="text-lg font-bold text-[#e5e7eb]">
                                    Antrian Sinkronisasi
                                </h2>
                                <p class="mt-1 text-xs text-[#9ca3af]">
                                    {{ formattedLastSyncTime }}
                                </p>
                            </div>

                            <!-- Close Button -->
                            <motion.button
                                :whileHover="{ scale: 1.05 }"
                                :whilePress="{ scale: 0.95 }"
                                :transition="{ duration: 0.15 }"
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-[#9ca3af] transition-colors hover:bg-[#1a1d23] hover:text-[#e5e7eb]"
                                aria-label="Tutup"
                                @click="handleClose"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M6 18L18 6M6 6l12 12"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </motion.button>
                        </div>

                        <!-- ================================================================ -->
                        <!-- Stats Section -->
                        <!-- ================================================================ -->
                        <div
                            class="flex items-center justify-between border-b border-[#3E3E3A] px-6 py-4"
                        >
                            <!-- Pending Count -->
                            <div class="flex items-center gap-3">
                                <!-- Icon -->
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-500/10"
                                >
                                    <svg
                                        class="h-5 w-5 text-cyan-400"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <!-- Text -->
                                <div>
                                    <div class="text-2xl font-bold text-[#e5e7eb]">
                                        {{ pendingCount }}
                                    </div>
                                    <div class="text-xs text-[#9ca3af]">
                                        Item Menunggu
                                    </div>
                                </div>
                            </div>

                            <!-- Last Sync Result -->
                            <div
                                v-if="lastSyncResultText"
                                class="text-right"
                            >
                                <div class="text-sm text-[#9ca3af]">
                                    Sync Terakhir
                                </div>
                                <div class="mt-1 text-xs text-[#9ca3af]">
                                    {{ lastSyncResultText }}
                                </div>
                            </div>
                        </div>

                        <!-- ================================================================ -->
                        <!-- Content Section: Items List or Empty State -->
                        <!-- ================================================================ -->
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            <!-- Empty State -->
                            <motion.div
                                v-if="!hasPendingItems"
                                :initial="{ opacity: 0, scale: 0.9 }"
                                :animate="{ opacity: 1, scale: 1 }"
                                :transition="{ duration: 0.4, delay: 0.2 }"
                                class="flex flex-col items-center justify-center py-12"
                            >
                                <!-- Success Icon -->
                                <div
                                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-500/10"
                                >
                                    <svg
                                        class="h-10 w-10 text-green-400"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <!-- Text -->
                                <h3 class="text-lg font-semibold text-[#e5e7eb]">
                                    Semua Data Tersinkronisasi
                                </h3>
                                <p class="mt-2 text-center text-sm text-[#9ca3af]">
                                    Tidak ada item yang menunggu untuk disinkronkan
                                </p>
                            </motion.div>

                            <!-- Items List -->
                            <div
                                v-else
                                class="flex flex-col gap-3"
                            >
                                <!-- Info Text -->
                                <p class="text-xs text-[#9ca3af]">
                                    Menampilkan {{ displayItems.length }} dari
                                    {{ pendingCount }} item
                                </p>

                                <!-- Items -->
                                <SyncQueueItem
                                    v-for="item in displayItems"
                                    :key="item.id"
                                    :item="item"
                                    :is-retrying="retryingItemId === item.id"
                                    @retry="handleRetryItem"
                                />
                            </div>
                        </div>

                        <!-- ================================================================ -->
                        <!-- Footer Section: Sync Button -->
                        <!-- ================================================================ -->
                        <div
                            class="border-t border-[#3E3E3A] px-6 py-4"
                        >
                            <motion.button
                                :whileHover="!isSyncDisabled ? { scale: 1.02 } : {}"
                                :whilePress="!isSyncDisabled ? { scale: 0.98 } : {}"
                                :transition="{ duration: 0.15 }"
                                type="button"
                                :disabled="isSyncDisabled"
                                :class="[
                                    'flex w-full items-center justify-center gap-3 rounded-lg px-6 py-4 text-base font-semibold transition-colors',
                                    isSyncDisabled
                                        ? 'cursor-not-allowed bg-gray-700 text-gray-400'
                                        : 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-600 hover:to-blue-700 active:from-cyan-700 active:to-blue-800',
                                ]"
                                @click="handleSyncNow"
                            >
                                <!-- Icon: Spinning when syncing, static otherwise -->
                                <svg
                                    v-if="isSyncing"
                                    v-motion="{
                                        animate: { rotate: 360 },
                                        transition: { duration: 1, repeat: Infinity, ease: 'linear' },
                                    }"
                                    class="h-5 w-5"
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

                                <svg
                                    v-else-if="!isOnline"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <!-- Button Text -->
                                <span>{{ syncButtonText }}</span>
                            </motion.button>
                        </div>
                    </div>
                </Transition>

                <!-- ================================================================ -->
                <!-- Sync Progress Indicator (Floating) -->
                <!-- ================================================================ -->
                <SyncProgressIndicator
                    v-if="showProgress"
                    :is-syncing="isSyncing"
                    :current="currentProgress?.current || 0"
                    :total="currentProgress?.total || 0"
                    :status="currentProgress?.error ? 'error' : 'syncing'"
                    :error-message="currentProgress?.error"
                    :show="showProgress"
                    @dismiss="() => {}"
                />
            </div>
        </Transition>
    </Teleport>
</template>
