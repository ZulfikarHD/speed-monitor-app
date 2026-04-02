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
import { computed, ref } from 'vue';

import EmptyState from '@/components/trips/EmptyState.vue';
import Pagination from '@/components/trips/Pagination.vue';
import TripCard from '@/components/trips/TripCard.vue';
import TripListFilters from '@/components/trips/TripListFilters.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
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


// ========================================================================
// Local State
// ========================================================================

/** Local filter state (synced with props) */
const localFilters = ref({
    status: props.filters.status,
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
});

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
</script>

<template>
    <EmployeeLayout title="My Trips">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-6"
            >
                <h1
                    class="text-3xl font-bold text-[#e5e7eb]"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    My Trips
                </h1>
                <p class="mt-1 text-sm text-[#9ca3af]">
                    View and filter your trip history
                </p>
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
                            <span class="text-sm text-cyan-400">
                                Filter aktif: {{ meta.total }} trip ditemukan
                            </span>
                        </div>
                        <motion.button
                            @click="handleResetFilters"
                            :whileHover="{ scale: 1.05, x: 2 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                            class="inline-flex min-h-[44px] items-center gap-2 px-4 py-3 text-sm text-cyan-400 hover:text-cyan-300"
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
