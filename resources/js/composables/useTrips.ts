/**
 * Trips List Composable
 *
 * Provides trip list fetching functionality with pagination and filtering.
 * Used for displaying trip history on the My Trips page.
 *
 * @example
 * ```ts
 * import { useTrips } from '@/composables/useTrips';
 *
 * const { trips, meta, isLoading, error, fetchTrips, retry } = useTrips();
 *
 * // Fetch trips with filters
 * await fetchTrips({
 *   page: 1,
 *   per_page: 20,
 *   status: 'completed',
 *   date_from: '2026-04-01',
 *   date_to: '2026-04-30'
 * });
 *
 * // Access results
 * console.log(trips.value); // Array of trips
 * console.log(meta.value); // Pagination metadata
 * ```
 */

import { ref } from 'vue';

import { index as listTripsAction } from '@/actions/App/Http/Controllers/TripController';
import type { Trip, TripListParams, TripListResponse } from '@/types/trip';
import { http } from '@/utils/http';

/**
 * Trips list composable for fetching trip history.
 *
 * Provides reactive state for trip list, pagination metadata, loading state,
 * and error handling. Separates list concerns from active trip tracking.
 *
 * @returns Object containing trips data, loading state, and fetch method
 */
export function useTrips() {

    // ========================================================================
    // State
    // ========================================================================

    /** Array of trips from the current query */
    const trips = ref<Trip[]>([]);

    /** Pagination metadata from the last response */
    const meta = ref<TripListResponse['meta'] | null>(null);

    /** Loading state indicator */
    const isLoading = ref(false);

    /** Error message from the last failed request */
    const error = ref<string | null>(null);

    /** Last used parameters for retry functionality */
    const lastParams = ref<TripListParams>({});

    // ========================================================================
    // Actions
    // ========================================================================

    /**
     * Fetch trips from the backend API with optional filters.
     *
     * WHY: Separated from trip store to maintain single responsibility.
     * WHY: Trip store handles active session, this handles history listing.
     *
     * @param params - Query parameters for filtering and pagination
     * @returns Promise that resolves when trips are loaded
     *
     * @example
     * ```ts
     * // Fetch first page with default pagination
     * await fetchTrips({ page: 1 });
     *
     * // Fetch with date range filter
     * await fetchTrips({
     *   date_from: '2026-04-01',
     *   date_to: '2026-04-30',
     *   page: 1
     * });
     *
     * // Fetch completed trips only
     * await fetchTrips({ status: 'completed' });
     * ```
     */
    async function fetchTrips(params: TripListParams = {}): Promise<void> {
        isLoading.value = true;
        error.value = null;
        lastParams.value = params;

        try {
            const response = await http.get<TripListResponse>(
                listTripsAction.url(),
                {
                    params: {
                        page: params.page ?? 1,
                        per_page: params.per_page ?? 20,
                        ...(params.status && { status: params.status }),
                        ...(params.date_from && { date_from: params.date_from }),
                        ...(params.date_to && { date_to: params.date_to }),
                        ...(params.user_id && { user_id: params.user_id }),
                    },
                },
            );

            if (response.data) {
                trips.value = response.data;
                meta.value = response.meta;
            }
        } catch (err) {
            error.value =
                err instanceof Error
                    ? err.message
                    : 'Gagal memuat daftar perjalanan';
            console.error('Failed to fetch trips:', err);
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Retry the last failed request.
     *
     * Useful for error recovery UI where users can retry after
     * a network failure or server error.
     *
     * @returns Promise that resolves when retry completes
     *
     * @example
     * ```ts
     * // After a failed fetch
     * if (error.value) {
     *     await retry(); // Retries with same parameters
     * }
     * ```
     */
    async function retry(): Promise<void> {
        await fetchTrips(lastParams.value);
    }

    /**
     * Clear current trips data and reset state.
     *
     * Useful when navigating away or when user logs out.
     *
     * @example
     * ```ts
     * // Clear data before fetching with new filters
     * clearTrips();
     * await fetchTrips({ status: 'completed' });
     * ```
     */
    function clearTrips(): void {
        trips.value = [];
        meta.value = null;
        error.value = null;
    }

    // ========================================================================
    // Return Public API
    // ========================================================================

    return {
        // State
        trips,
        meta,
        isLoading,
        error,

        // Actions
        fetchTrips,
        retry,
        clearTrips,
    };
}
