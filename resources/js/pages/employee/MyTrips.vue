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
            <div class="mb-6">
                <h1
                    class="text-3xl font-bold text-[#e5e7eb]"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    My Trips
                </h1>
                <p class="mt-1 text-sm text-[#9ca3af]">
                    View and filter your trip history
                </p>
            </div>

            <!-- Filters Section -->
            <div class="mb-6">
                <TripListFilters
                    v-model:status="localFilters.status"
                    v-model:date-from="localFilters.date_from"
                    v-model:date-to="localFilters.date_to"
                    @apply="handleApplyFilters"
                    @reset="handleResetFilters"
                />
            </div>

            <!-- Trips Grid -->
            <div class="space-y-6">
                <!-- Active Filters Indicator -->
                <div
                    v-if="hasActiveFilters"
                    class="flex items-center justify-between rounded-lg border border-cyan-500/20 bg-cyan-500/5 px-4 py-3"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-cyan-400">
                            Filter aktif: {{ meta.total }} trip ditemukan
                        </span>
                    </div>
                    <button
                        @click="handleResetFilters"
                        class="text-sm text-cyan-400 hover:text-cyan-300"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- Trip Cards -->
                <div
                    v-if="!showEmptyState"
                    class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-1"
                >
                    <TripCard
                        v-for="trip in trips"
                        :key="trip.id"
                        :trip="trip"
                    />
                </div>

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
