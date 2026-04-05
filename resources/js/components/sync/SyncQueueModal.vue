<script setup lang="ts">
/**
 * Sync Queue Modal Component
 *
 * Full-screen modal showing sync queue details with manual sync controls.
 * Bottom sheet on mobile, centered dialog on desktop.
 *
 * Features:
 * - Full-screen mobile (bottom sheet), centered 600px desktop
 * - Header with title, close button, last sync time
 * - Stats row with pending count and status
 * - Scrollable list of pending items (max 10 visible)
 * - Manual "Sync Now" button
 * - Empty state when no pending items
 * - Real-time updates during sync
 * - Backdrop dismiss + ESC key support
 *
 * UX Principles:
 * - Miller's Law: Max 10 items initially, scroll for more
 * - Fitts's Law: Large sync button (56px), close button (44px)
 * - Jakob's Law: Familiar modal patterns
 * - Feedback: Loading states, progress indicators
 */

import {
    CheckCircle,
    Clock,
    CloudUpload,
    Loader2,
    WifiOff,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

import SyncProgressIndicator from '@/components/sync/SyncProgressIndicator.vue';
import SyncQueueItem from '@/components/sync/SyncQueueItem.vue';
import { useSyncQueue } from '@/composables/useSyncQueue';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Whether modal is visible */
    show?: boolean;
}

interface Emits {
    /** Modal should close */
    (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
});

const emit = defineEmits<Emits>();

// ========================================================================
// Dependencies
// ========================================================================

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

// ========================================================================
// State
// ========================================================================

/** ID of item currently being retried. */
const retryingItemId = ref<number | null>(null);

/**
 * WHY: Shows during manual and auto-sync operations.
 */
const showProgress = computed(() => {
    return isSyncing.value && currentProgress.value !== undefined;
});

// ========================================================================
// Computed
// ========================================================================

const hasPendingItems = computed(() => pendingCount.value > 0);

/**
 * WHY: Prevents duplicate sync operations and shows when offline.
 */
const isSyncDisabled = computed(() => {
    return isSyncing.value || !isOnline.value || pendingCount.value === 0;
});

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

const lastSyncResultText = computed(() => {
    if (!lastSyncResult.value) {
return null;
}

    const { successCount, failureCount } = lastSyncResult.value;

    if (failureCount === 0) {
return `${successCount} berhasil`;
}

    if (successCount === 0) {
return `${failureCount} gagal`;
}

    return `${successCount} berhasil, ${failureCount} gagal`;
});

// ========================================================================
// Methods
// ========================================================================

function handleClose(): void {
    emit('close');
}

function handleBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget) {
        handleClose();
    }
}

async function handleSyncNow(): Promise<void> {
    await syncNow();
}

async function handleRetryItem(itemId: number): Promise<void> {
    retryingItemId.value = itemId;

    try {
        await retryFailedItem(itemId);
    } finally {
        retryingItemId.value = null;
    }
}

// ========================================================================
// Watchers
// ========================================================================

/**
 * WHY: Prevents background scrolling when modal is open.
 */
watch(
    () => props.show,
    (newValue) => {
        document.body.style.overflow = newValue ? 'hidden' : '';
    },
);
</script>

<template>
    <!-- Modal Teleport to Body -->
    <Teleport to="body">
        <Transition
            :css="false"
            @enter="
                (el, done) => {
                    motion(el, { opacity: [0, 1] }, { duration: 0.2 }).finished.then(done);
                }
            "
            @leave="
                (el, done) => {
                    motion(el, { opacity: 0 }, { duration: 0.15 }).finished.then(done);
                }
            "
        >
            <!-- Backdrop -->
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 md:items-center"
                @click="handleBackdropClick"
            >
                <!-- Modal Container (Slide Up) -->
                <Transition
                    :css="false"
                    @enter="
                        (el, done) => {
                            motion(
                                el,
                                { y: [100, 0], opacity: [0, 1] },
                                { duration: 0.3 }
                            ).finished.then(done);
                        }
                    "
                    @leave="
                        (el, done) => {
                            motion(
                                el,
                                { y: 100, opacity: 0 },
                                { duration: 0.2 }
                            ).finished.then(done);
                        }
                    "
                >
                    <div
                        v-if="show"
                        class="flex max-h-[90vh] w-full flex-col overflow-hidden rounded-t-2xl shadow-2xl md:max-h-[80vh] md:w-[600px] md:rounded-2xl bg-white/95 dark:bg-zinc-900/98 border-t border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 md:border"
                    >
                        <!-- Header Section -->
                        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-white/10 px-6 py-4">
                            <div class="flex-1">
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                                    Antrian Sinkronisasi
                                </h2>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ formattedLastSyncTime }}
                                </p>
                            </div>

                            <!-- Close Button -->
                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full transition-colors duration-200 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-white"
                                aria-label="Tutup"
                                @click="handleClose"
                            >
                                <X :size="20" />
                            </button>
                        </div>

                        <!-- Stats Section -->
                        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-white/10 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-500/10">
                                    <Clock
                                        :size="20"
                                        class="text-cyan-600 dark:text-cyan-400"
                                    />
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ pendingCount }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Item Menunggu
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="lastSyncResultText"
                                class="text-right"
                            >
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Sync Terakhir
                                </div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ lastSyncResultText }}
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            <!-- Empty State -->
                            <div
                                v-if="!hasPendingItems"
                                class="flex flex-col items-center justify-center py-12"
                            >
                                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/10">
                                    <CheckCircle
                                        :size="40"
                                        class="text-emerald-600 dark:text-emerald-400"
                                    />
                                </div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    Semua Data Tersinkronisasi
                                </h3>
                                <p class="mt-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Tidak ada item yang menunggu untuk disinkronkan
                                </p>
                            </div>

                            <!-- Items List -->
                            <div
                                v-else
                                class="flex flex-col gap-3"
                            >
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Menampilkan {{ displayItems.length }} dari
                                    {{ pendingCount }} item
                                </p>

                                <SyncQueueItem
                                    v-for="item in displayItems"
                                    :key="item.id"
                                    :item="item"
                                    :is-retrying="retryingItemId === item.id"
                                    @retry="handleRetryItem"
                                />
                            </div>
                        </div>

                        <!-- Footer Section: Sync Button -->
                        <div class="border-t border-zinc-200 dark:border-white/10 px-6 py-4">
                            <button
                                type="button"
                                :disabled="isSyncDisabled"
                                :class="[
                                    'flex w-full items-center justify-center gap-3 rounded-lg px-6 py-4 text-base font-semibold transition-colors duration-200',
                                    isSyncDisabled
                                        ? 'cursor-not-allowed bg-zinc-200 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500'
                                        : 'bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 hover:shadow-xl active:scale-[0.98]',
                                ]"
                                @click="handleSyncNow"
                            >
                                <Loader2
                                    v-if="isSyncing"
                                    :size="20"
                                    class="animate-spin"
                                />
                                <WifiOff
                                    v-else-if="!isOnline"
                                    :size="20"
                                />
                                <CloudUpload
                                    v-else
                                    :size="20"
                                />
                                <span>{{ syncButtonText }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Sync Progress Indicator (Floating) -->
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
