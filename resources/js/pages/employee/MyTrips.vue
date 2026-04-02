<script setup lang="ts">
/**
 * My Trips Page - Employee trip history view (Server-Side Rendered).
 *
 * Displays paginated list of user's trips with filtering capabilities.
 * Data is passed from MyTripsController via Inertia props for optimal
 * performance and SEO. Filter changes trigger new server requests.
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

import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import EmptyState from '@/components/trips/EmptyState.vue';
import Pagination from '@/components/trips/Pagination.vue';
import TripCard from '@/components/trips/TripCard.vue';
import TripListFilters from '@/components/trips/TripListFilters.vue';
import { useAuth } from '@/composables/useAuth';
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

const { handleLogout, isLoading: isLoggingOut } = useAuth();

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
    <Head title="Riwayat Perjalanan" />

    <div class="min-h-screen bg-[#0a0c0f]">
        <!-- ====================================================================
            Header Section
            Page title with logout button
        ==================================================================== -->
        <header
            class="border-b border-[#3E3E3A] bg-[#0a0c0f] px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div>
                    <h1
                        class="font-display text-3xl font-bold tracking-tight text-[#EDEDEC]"
                    >
                        Riwayat Perjalanan
                    </h1>
                    <p class="mt-1 text-sm text-[#A1A09A]">
                        Lihat dan kelola history perjalanan Anda
                    </p>
                </div>

                <button
                    @click="handleLogout"
                    :disabled="isLoggingOut"
                    class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-4 py-2 text-sm font-medium text-[#EDEDEC] transition-colors hover:bg-[#2a2d33] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
                </button>
            </div>
        </header>

        <!-- ====================================================================
            Main Content
            Filters, trip list, and pagination
        ==================================================================== -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Back to Dashboard -->
            <div class="mb-6">
                <Link
                    href="/employee/dashboard"
                    class="inline-flex items-center gap-2 text-sm text-cyan-400 transition-colors hover:text-cyan-300"
                >
                    <span>←</span>
                    <span>Kembali ke Dashboard</span>
                </Link>
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
        </main>
    </div>
</template>
