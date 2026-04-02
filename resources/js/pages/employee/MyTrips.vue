<script setup lang="ts">
/**
 * My Trips Page - Employee trip history view.
 *
 * Displays paginated list of user's trips with filtering capabilities.
 * Integrates TripCard, TripListFilters, Pagination, and EmptyState components.
 *
 * Features:
 * - Paginated trip list (20 per page default)
 * - Date range filtering
 * - Status filtering
 * - Loading states
 * - Error handling with retry
 * - Empty states
 * - Responsive design (mobile-first)
 *
 * @example Route: /employee/my-trips
 */

import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import EmptyState from '@/components/trips/EmptyState.vue';
import Pagination from '@/components/trips/Pagination.vue';
import TripCard from '@/components/trips/TripCard.vue';
import TripListFilters from '@/components/trips/TripListFilters.vue';
import { useAuth } from '@/composables/useAuth';
import { useTrips } from '@/composables/useTrips';
import type { TripListParams, TripStatus } from '@/types/trip';

// ========================================================================
// Dependencies
// ========================================================================

const { handleLogout, isLoading: isLoggingOut } = useAuth();
const { trips, meta, isLoading, error, fetchTrips, retry } = useTrips();

// ========================================================================
// State
// ========================================================================

/** Current filter parameters */
const filters = ref<TripListParams>({
    page: 1,
    per_page: 20,
    date_from: '',
    date_to: '',
    status: '' as TripStatus | '',
});

/** Flag to track if initial load has completed */
const hasLoadedOnce = ref(false);

// ========================================================================
// Lifecycle
// ========================================================================

/**
 * Fetch trips on component mount.
 */
onMounted(async () => {
    await loadTrips();
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Load trips with current filters.
 */
async function loadTrips(): Promise<void> {
    const params: TripListParams = {
        page: filters.value.page,
        per_page: filters.value.per_page,
        ...(filters.value.status && { status: filters.value.status }),
        ...(filters.value.date_from && { date_from: filters.value.date_from }),
        ...(filters.value.date_to && { date_to: filters.value.date_to }),
    };

    await fetchTrips(params);
    hasLoadedOnce.value = true;

    // Scroll to top after loading
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Handle filter apply.
 */
async function handleApplyFilters(): Promise<void> {
    filters.value.page = 1; // Reset to first page when filters change
    await loadTrips();
}

/**
 * Handle filter reset.
 */
async function handleResetFilters(): Promise<void> {
    filters.value = {
        page: 1,
        per_page: 20,
        date_from: '',
        date_to: '',
        status: '' as TripStatus | '',
    };
    await loadTrips();
}

/**
 * Handle page change from pagination.
 *
 * @param page - New page number
 */
async function handlePageChange(page: number): Promise<void> {
    filters.value.page = page;
    await loadTrips();
}

// Navigation is now handled internally by TripCard component

/**
 * Handle retry after error.
 */
async function handleRetry(): Promise<void> {
    await retry();
}

/**
 * Check if there are any active filters.
 */
const hasActiveFilters = computed(() => {
    return !!(
        filters.value.status ||
        filters.value.date_from ||
        filters.value.date_to
    );
});

/**
 * Check if showing empty state.
 */
const showEmptyState = computed(() => {
    return hasLoadedOnce.value && !isLoading.value && trips.value?.length === 0;
});
</script>

<template>
    <Head title="Riwayat Perjalanan" />

    <div class="min-h-screen bg-[#0a0c0f]">
        <!-- ====================================================================
            Header Section
            Page title, subtitle, and logout button
        ==================================================================== -->
        <div class="border-b border-[#3E3E3A] bg-[#1a1d23]">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1
                            class="text-3xl font-bold text-[#e5e7eb]"
                            style="font-family: 'Bebas Neue', sans-serif"
                        >
                            Riwayat Perjalanan
                        </h1>
                        <p class="mt-1 text-sm text-[#9ca3af]">
                            Lihat daftar perjalanan Anda dan statistik
                            lengkapnya
                        </p>
                    </div>

                    <!-- Navigation Actions -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/employee/dashboard"
                            class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-4 py-2 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
                        >
                            ← Dashboard
                        </Link>

                        <button
                            @click="handleLogout"
                            :disabled="isLoggingOut"
                            class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-4 py-2 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] disabled:cursor-not-allowed disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
                        >
                            {{ isLoggingOut ? 'Keluar...' : 'Keluar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
            Main Content
            Filters, trip list, pagination
        ==================================================================== -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6">
                <TripListFilters
                    v-model:date-from="filters.date_from"
                    v-model:date-to="filters.date_to"
                    v-model:status="filters.status"
                    @apply="handleApplyFilters"
                    @reset="handleResetFilters"
                />
            </div>

            <!-- Loading State -->
            <div
                v-if="isLoading && !hasLoadedOnce"
                class="flex items-center justify-center py-20"
            >
                <div class="text-center">
                    <div
                        class="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"
                        role="status"
                        aria-label="Loading trips"
                    ></div>
                    <p class="text-sm text-[#9ca3af]">
                        Memuat riwayat perjalanan...
                    </p>
                </div>
            </div>

            <!-- Error State -->
            <div
                v-else-if="error"
                class="rounded-lg border border-red-500/20 bg-red-500/10 p-6 text-center"
            >
                <div class="mb-2 text-4xl" aria-hidden="true">⚠️</div>
                <h3 class="mb-2 text-lg font-semibold text-red-400">
                    Gagal Memuat Data
                </h3>
                <p class="mb-4 text-sm text-red-300">
                    {{ error }}
                </p>
                <button
                    @click="handleRetry"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
                >
                    <svg
                        class="h-4 w-4"
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
                    Coba Lagi
                </button>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-else-if="showEmptyState && !hasActiveFilters"
                icon="📋"
                title="Belum Ada Perjalanan"
                message="Anda belum memiliki riwayat perjalanan. Mulai perjalanan pertama Anda dengan menggunakan speedometer."
                cta-text="Mulai Perjalanan"
                cta-href="/employee/speedometer"
            />

            <!-- Empty State with Filters -->
            <EmptyState
                v-else-if="showEmptyState && hasActiveFilters"
                icon="🔍"
                title="Tidak Ada Hasil"
                message="Tidak ada perjalanan yang cocok dengan filter Anda. Coba ubah filter atau reset untuk melihat semua perjalanan."
            />

            <!-- Trip List -->
            <div v-else>
                <!-- Trip Cards Grid -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
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
                        :per-page="meta.per_page"
                        :total="meta.total"
                        @page-change="handlePageChange"
                    />
                </div>

                <!-- Loading Overlay for Page Changes -->
                <div
                    v-if="isLoading && hasLoadedOnce"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                >
                    <div
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6 text-center shadow-xl"
                    >
                        <div
                            class="mb-3 inline-block h-10 w-10 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"
                            role="status"
                            aria-label="Loading"
                        ></div>
                        <p class="text-sm text-[#e5e7eb]">Memuat...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
