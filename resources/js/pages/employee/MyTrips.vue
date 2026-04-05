<script setup lang="ts">
/**
 * My Trips Page - Employee trip history view (Server-Side Rendered).
 *
 * Displays paginated list of user's trips with filtering capabilities.
 * Data is passed from MyTripsController via Inertia props for optimal
 * performance and SEO. Filter changes trigger new server requests.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Server-side pagination (20 per page default)
 * - Date range filtering
 * - Status filtering
 * - Empty states
 * - Responsive design (mobile-first)
 *
 * @example Route: /employee/my-trips
 */

import { router } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, ref } from 'vue';

import Toast from '@/components/common/Toast.vue';
import OfflineIndicator from '@/components/offline/OfflineIndicator.vue';
import SyncProgressIndicator from '@/components/sync/SyncProgressIndicator.vue';
import EmptyState from '@/components/trips/EmptyState.vue';
import Pagination from '@/components/trips/Pagination.vue';
import TripCard from '@/components/trips/TripCard.vue';
import TripListFilters from '@/components/trips/TripListFilters.vue';
import { useBackgroundSync } from '@/composables/useBackgroundSync';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useTripStore } from '@/stores/trip';
import type { SyncProgress } from '@/types/sync';
import type { Trip, TripStatus } from '@/types/trip';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Trips for current page */
    trips: Trip[];
    /** Pagination metadata */
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    /** Current filter values */
    filters: {
        status: TripStatus | '';
        date_from: string;
        date_to: string;
    };
}

const props = defineProps<Props>();

// ========================================================================
// Dependencies
// ========================================================================

const tripStore = useTripStore();

/**
 * Background sync composable for automatic synchronization.
 *
 * Provides auto-sync functionality when app comes online and
 * comprehensive sync state tracking for UI feedback.
 */
const {
    isSyncing: isBackgroundSyncing,
    currentProgress: backgroundSyncProgress,
    isAutoSyncEnabled,
    startManualSync: triggerBackgroundSync,
} = useBackgroundSync();

// ========================================================================
// Local State
// ========================================================================

/** Local filter state (synced with props) */
const localFilters = ref({
    status: props.filters.status,
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
});

/** Pending sync count for offline indicator */
const pendingSyncCount = ref<number>(0);

/** Syncing state for loading indicator (manual sync) */
const isSyncing = ref<boolean>(false);

/** Current sync progress for UI display (manual sync) */
const syncProgress = ref<SyncProgress | null>(null);

/** Combined syncing state (manual or background) */
const isAnySyncing = computed(() => isSyncing.value || isBackgroundSyncing.value);

/** Combined sync progress (manual or background) */
const currentSyncProgress = computed(() => syncProgress.value || backgroundSyncProgress.value);

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle filter apply - trigger server-side refetch.
 */
function handleApplyFilters(): void {
    router.get(
        '/employee/my-trips',
        {
            status: localFilters.value.status || undefined,
            date_from: localFilters.value.date_from || undefined,
            date_to: localFilters.value.date_to || undefined,
            page: 1, // Reset to first page
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Handle filter reset - clear all filters.
 */
function handleResetFilters(): void {
    localFilters.value = {
        status: '',
        date_from: '',
        date_to: '',
    };

    router.get(
        '/employee/my-trips',
        { page: 1 },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Handle page change from pagination.
 *
 * @param page - New page number
 */
function handlePageChange(page: number): void {
    router.get(
        '/employee/my-trips',
        {
            page,
            status: props.filters.status || undefined,
            date_from: props.filters.date_from || undefined,
            date_to: props.filters.date_to || undefined,
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Handle manual sync of pending offline trips.
 *
 * Now delegates to background sync composable for consistency.
 * Provides UI feedback and refreshes trip list after sync.
 *
 * WHY: Centralized sync logic through background sync composable.
 * WHY: Manual sync triggers same workflow as auto-sync.
 */
async function handleManualSync(): Promise<void> {
    if (isAnySyncing.value) {
        return; // Prevent double sync
    }

    try {
        // Trigger manual sync via background sync composable
        await triggerBackgroundSync();

        // Refresh trip list from backend after sync
        router.reload({ only: ['trips', 'meta'] });

        // Update pending count
        await updatePendingSyncCount();
    } catch (error: any) {
        console.error('[MyTrips] Manual sync error:', error);
    }
}

// ========================================================================
// Computed
// ========================================================================

/**
 * Check if there are any active filters.
 */
const hasActiveFilters = computed(() => {
    return !!(props.filters.status || props.filters.date_from || props.filters.date_to);
});

/**
 * Check if showing empty state.
 */
const showEmptyState = computed(() => {
    return props.trips.length === 0;
});

// ========================================================================
// Lifecycle Hooks
// ========================================================================

onMounted(async () => {
    // Load pending sync count on mount
    await updatePendingSyncCount();

    // Set up background sync watcher to refresh trip list
    watch(isBackgroundSyncing, (newSyncing, oldSyncing) => {
        // When background sync completes (syncing -> not syncing)
        if (oldSyncing && !newSyncing) {
            // Refresh trip list from backend
            router.reload({ only: ['trips', 'meta'] });

            // Update pending count
            updatePendingSyncCount();
        }
    });
});

// ========================================================================
// Offline Sync Management
// ========================================================================

/**
 * Update pending sync count.
 *
 * Fetches the number of items waiting to be synced from IndexedDB.
 */
const updatePendingSyncCount = async (): Promise<void> => {
    try {
        pendingSyncCount.value = await tripStore.getPendingSyncCount();
    } catch (err) {
        console.error('Failed to update pending sync count:', err);
    }
};

</script>

<template>
    <EmployeeLayout title="My Trips">
        <!-- ============================================================ -->
        <!-- TOAST NOTIFICATIONS (Fixed position, global) -->
        <!-- ============================================================ -->
        <Toast />

        <!-- ============================================================ -->
        <!-- OFFLINE INDICATOR (Fixed position, outside main layout) -->
        <!-- ============================================================ -->
        <OfflineIndicator
            :pending-count="pendingSyncCount"
            :is-syncing="isAnySyncing"
            :is-auto-sync-enabled="isAutoSyncEnabled"
            @sync="handleManualSync"
        />

        <!-- ============================================================ -->
        <!-- SYNC PROGRESS INDICATOR (Floating bottom-right) -->
        <!-- ============================================================ -->
        <SyncProgressIndicator
            v-if="isBackgroundSyncing && currentSyncProgress"
            :show="isBackgroundSyncing"
            :is-syncing="isBackgroundSyncing"
            :current="currentSyncProgress?.current || 0"
            :total="currentSyncProgress?.total || 0"
            :status="'syncing'"
            @dismiss="() => {}"
        />

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header with Sync Button -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-6 flex items-start justify-between gap-4"
            >
                <div class="flex-1">
                    <h1
                        class="text-3xl font-bold text-zinc-900 dark:text-white"
                        style="font-family: 'Bebas Neue', sans-serif"
                    >
                        My Trips
                    </h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        View and filter your trip history
                    </p>
                </div>

                <!-- Manual Sync Button (only shown when pending items exist and not auto-syncing) -->
                <AnimatePresence>
                    <motion.button
                        v-if="pendingSyncCount > 0 && !isAnySyncing"
                        type="button"
                        @click="handleManualSync"
                        :initial="{ opacity: 0, scale: 0.8 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.8 }"
                        :whileHover="{ scale: 1.05 }"
                        :whilePress="{ scale: 0.95 }"
                        :transition="{ type: 'spring', stiffness: 400, damping: 20 }"
                        class="flex h-12 items-center gap-2 rounded-lg border border-cyan-500/50 bg-cyan-500/10 px-4 text-sm font-medium text-cyan-600 dark:text-cyan-400 shadow-lg shadow-cyan-500/20 transition-colors hover:bg-cyan-500/20 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:ring-offset-zinc-900"
                        aria-label="Sinkronkan perjalanan offline sekarang"
                    >
                        <!-- Cloud Upload Icon -->
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                        <span class="hidden sm:inline">Sinkronkan</span>
                        <span
                            class="inline-flex items-center justify-center rounded-full bg-cyan-500/20 px-2 py-0.5 text-xs font-bold"
                        >
                            {{ pendingSyncCount }}
                        </span>
                    </motion.button>

                    <!-- Loading Spinner (shown during any sync) -->
                    <motion.div
                        v-else-if="isAnySyncing"
                        :initial="{ opacity: 0, scale: 0.8 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.8 }"
                        class="flex h-12 items-center gap-3 rounded-lg border border-cyan-500/50 bg-cyan-500/10 px-4 text-sm font-medium text-cyan-600 dark:text-cyan-400"
                    >
                        <!-- Rotating Spinner Icon -->
                        <motion.div
                            :animate="{ rotate: 360 }"
                            :transition="{
                                duration: 1,
                                repeat: Infinity,
                                ease: 'linear',
                            }"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                        </motion.div>
                        <span v-if="currentSyncProgress">
                            Menyinkronkan {{ currentSyncProgress.current }}/{{
                                currentSyncProgress.total
                            }}...
                        </span>
                        <span v-else>Menyinkronkan...</span>
                    </motion.div>
                </AnimatePresence>
            </motion.div>

            <!-- Filters Section -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                class="mb-6"
            >
                <TripListFilters
                    v-model:status="localFilters.status"
                    v-model:date-from="localFilters.date_from"
                    v-model:date-to="localFilters.date_to"
                    @apply="handleApplyFilters"
                    @reset="handleResetFilters"
                />
            </motion.div>

            <!-- Trips Grid -->
            <div class="space-y-6">
                <!-- Active Filters Indicator -->
                <AnimatePresence>
                    <motion.div
                        v-if="hasActiveFilters"
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :exit="{ opacity: 0, x: 20 }"
                        :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                        class="flex items-center justify-between rounded-lg border border-cyan-500/20 bg-cyan-500/5 px-4 py-3"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-cyan-600 dark:text-cyan-400">
                                Filter aktif: {{ meta.total }} trip ditemukan
                            </span>
                        </div>
                        <motion.button
                            @click="handleResetFilters"
                            :whileHover="{ scale: 1.05, x: 2 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                            class="inline-flex min-h-[44px] items-center gap-2 px-4 py-3 text-sm text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:text-cyan-300"
                        >
                            Reset Filter
                        </motion.button>
                    </motion.div>
                </AnimatePresence>

                <!-- Trip Cards -->
                <motion.div
                    v-if="!showEmptyState"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
                    class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-1"
                >
                    <TripCard
                        v-for="trip in trips"
                        :key="trip.id"
                        :trip="trip"
                    />
                </motion.div>

                <!-- Pagination -->
                <div v-if="meta && meta.last_page > 1" class="mt-8">
                    <Pagination
                        :current-page="meta.current_page"
                        :last-page="meta.last_page"
                        :total="meta.total"
                        @page-change="handlePageChange"
                    />
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="showEmptyState"
                    :has-filters="hasActiveFilters"
                    @reset-filters="handleResetFilters"
                />
            </div>
        </div>
    </EmployeeLayout>
</template>
