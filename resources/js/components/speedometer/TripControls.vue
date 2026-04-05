<!--
TripControls Component

Provides UI controls for starting and stopping trip tracking sessions.
Integrates with trip store for session management and geolocation composable
for GPS speed tracking.

Features:
- Large start/stop buttons with loading states
- GPS permission request before trip start
- Real-time trip duration display (HH:MM:SS)
- Confirmation dialog before stopping trip
- Automatic speed log syncing every 10 logs
- Error display for permission/API failures
- Mobile-first responsive design

Usage:
  <TripControls />

Integration:
- Trip Store: startTrip(), endTrip(), addSpeedLog()
- Geolocation: requestPermission(), watchSpeed(), stopTracking()
- Settings Store: speed_limit for violation detection
-->

<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

import { useGeolocation } from '@/composables/useGeolocation';
import { useTripStore } from '@/stores/trip';

// ========================================================================
// Store and Composable Integration
// ========================================================================

const tripStore = useTripStore();
const { watchSpeed, stopTracking, requestPermission, error: geoError } = useGeolocation();

// ========================================================================
// Local State
// ========================================================================

/** Whether the stop confirmation dialog is visible */
const showStopConfirmation = ref<boolean>(false);

/** Local error message for geolocation permission issues */
const localError = ref<string | null>(null);


// ========================================================================
// Computed Properties
// ========================================================================


/**
 * Whether any operation is in progress.
 *
 * WHY: Disable buttons during any loading state to prevent duplicate actions.
 */
const isLoading = computed<boolean>(
    () => tripStore.isStarting || tripStore.isEnding || tripStore.isSyncing
);

/**
 * Combined error message from trip store or geolocation.
 *
 * WHY: Show most relevant error to user (local errors take precedence).
 */
const errorMessage = computed<string | null>(() => {
    if (localError.value) {
        return localError.value;
    }

    if (geoError.value) {
        return geoError.value.message;
    }

    if (tripStore.error) {
        return tripStore.error;
    }

    return null;
});

// ========================================================================
// Trip Lifecycle Methods
// ========================================================================

/**
 * Handle start trip button click.
 *
 * Requests GPS permission, starts trip via API, and begins speed tracking.
 * Shows error if permission denied or API fails.
 *
 * WHY: Explicit permission request provides better UX than automatic prompt.
 * WHY: GPS tracking only starts after trip successfully created in backend.
 *
 * @example
 * ```ts
 * // User clicks "Mulai Perjalanan" button
 * await handleStartTrip();
 * // → GPS permission requested
 * // → Trip created in database
 * // → Speed tracking starts
 * ```
 */
async function handleStartTrip(): Promise<void> {
    // Clear any previous errors
    localError.value = null;

    /**
     * Step 1: Request GPS permission.
     *
     * WHY: Must have GPS access before starting trip.
     * WHY: Show permission UI before making API call.
     */
    const permission = await requestPermission();

    if (!permission.granted) {
        localError.value = 'Izin lokasi diperlukan untuk melacak kecepatan. Mohon aktifkan izin lokasi di pengaturan browser Anda.';

        return;
    }

    try {
        /**
         * Step 2: Create trip in backend.
         *
         * WHY: Trip must exist before logging speed data.
         * WHY: Backend generates trip ID needed for speed log API calls.
         */
        await tripStore.startTrip();

        /**
         * Step 3: Start GPS speed tracking.
         *
         * WHY: watchSpeed() continuously monitors GPS and calls callback.
         * WHY: Callback logs speed to trip store every GPS update (~1 second).
         * WHY: Trip store batches logs and syncs every 10 logs (50 seconds).
         */
        watchSpeed((speed, state) => {
            if (tripStore.hasActiveTrip) {
                tripStore.addSpeedLog(speed, state.timestamp);

                /**
                 * Auto-sync speed logs when threshold reached.
                 *
                 * WHY: needsSync is true when 10 or more logs buffered.
                 * WHY: Reduces API calls by ~90% vs syncing every log.
                 * WHY: Background sync doesn't block UI.
                 */
                if (tripStore.needsSync && !tripStore.isSyncing) {
                    tripStore.syncSpeedLogs().catch((error) => {
                        console.error('Auto-sync failed:', error);
                    });
                }
            }
        });

    } catch (error: any) {
        localError.value = error.message || 'Gagal memulai perjalanan. Silakan coba lagi.';
    }
}

/**
 * Open stop trip confirmation dialog.
 *
 * WHY: Prevent accidental trip end by requiring confirmation.
 */
function openStopConfirmation(): void {
    showStopConfirmation.value = true;
}

/**
 * Close stop trip confirmation dialog.
 */
function closeStopConfirmation(): void {
    showStopConfirmation.value = false;
}

/**
 * Handle stop trip confirmation (Yes button clicked).
 *
 * Closes dialog and proceeds with stopping trip.
 */
async function confirmStopTrip(): Promise<void> {
    closeStopConfirmation();
    await handleStopTrip();
}

/**
 * Handle stop trip action.
 *
 * Ends trip via API (syncs remaining logs), stops GPS tracking,
 * and cleans up resources.
 *
 * WHY: Ensures all speed data is synced before ending trip.
 * WHY: Stops GPS to save battery after trip ends.
 *
 * @example
 * ```ts
 * // User confirms stop trip
 * await handleStopTrip();
 * // → Remaining logs synced to backend
 * // → Trip marked as completed
 * // → GPS tracking stopped
 * ```
 */
async function handleStopTrip(): Promise<void> {
    localError.value = null;

    try {
        /**
         * Step 1: End trip via API.
         *
         * WHY: endTrip() syncs remaining speed logs first.
         * WHY: Backend calculates final trip statistics.
         * WHY: Sets trip status to 'completed' and ended_at timestamp.
         */
        await tripStore.endTrip();

        /**
         * Step 2: Stop GPS tracking.
         *
         * WHY: No longer need GPS after trip ends.
         * WHY: Saves device battery.
         */
        stopTracking();

    } catch (error: any) {
        localError.value = error.message || 'Gagal mengakhiri perjalanan. Silakan coba lagi.';
    }
}

// ========================================================================
// Duration Update Management
// ========================================================================

// ========================================================================
// Watchers
// ========================================================================


// ========================================================================
// Lifecycle Hooks
// ========================================================================

/**
 * Clean up resources on component unmount.
 *
 * WHY: Stop GPS tracking if component unmounted during active trip.
 */
onBeforeUnmount(() => {
    if (tripStore.hasActiveTrip) {
        stopTracking();
    }
});
</script>

<template>
    <div class="w-full max-w-md mx-auto space-y-4">

        <!-- ============================================================
             Control Buttons
             ============================================================ -->
        <div class="space-y-3">
            <!-- Start Trip Button -->
            <AnimatePresence>
                <motion.button
                    v-if="!tripStore.hasActiveTrip"
                    type="button"
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.9 }"
                    :whileHover="{ scale: 1.02, y: -2 }"
                    :whilePress="{ scale: 0.98 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="w-full py-4 px-6 rounded-lg font-semibold text-white transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="[
                        isLoading
                            ? 'bg-green-400 cursor-wait'
                            : 'bg-green-600 hover:bg-green-700 active:bg-green-800'
                    ]"
                    :disabled="isLoading"
                    @click="handleStartTrip"
                >
                    <span v-if="tripStore.isStarting" class="flex items-center justify-center">
                        <svg
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        Memulai...
                    </span>
                    <span v-else>Mulai Perjalanan</span>
                </motion.button>
            </AnimatePresence>

            <!-- Stop Trip Button -->
            <AnimatePresence>
                <motion.button
                    v-if="tripStore.hasActiveTrip"
                    type="button"
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.9 }"
                    :whileHover="{ scale: 1.02, y: -2 }"
                    :whilePress="{ scale: 0.98 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="w-full py-4 px-6 rounded-lg font-semibold text-white transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-red-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="[
                        isLoading
                            ? 'bg-red-400 cursor-wait'
                            : 'bg-red-600 hover:bg-red-700 active:bg-red-800'
                    ]"
                    :disabled="isLoading"
                    @click="openStopConfirmation"
                >
                    <span v-if="tripStore.isEnding" class="flex items-center justify-center">
                        <svg
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        Mengakhiri...
                    </span>
                    <span v-else>Akhiri Perjalanan</span>
                </motion.button>
            </AnimatePresence>
        </div>

        <!-- ============================================================
             Error Message Display
             ============================================================ -->
        <AnimatePresence>
            <motion.div
                v-if="errorMessage"
                :initial="{ opacity: 0, x: -20, scale: 0.95 }"
                :animate="{ opacity: 1, x: 0, scale: 1 }"
                :exit="{ opacity: 0, x: 20, scale: 0.95 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
                <div class="flex items-start">
                    <svg
                        class="h-5 w-5 text-red-600 mt-0.5 mr-3 flex-shrink-0"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-red-800">
                            {{ errorMessage }}
                        </p>
                    </div>
                </div>
            </motion.div>
        </AnimatePresence>

        <!-- ============================================================
             Stop Confirmation Dialog
             ============================================================ -->
        <Teleport to="body">
            <AnimatePresence>
                <motion.div
                    v-if="showStopConfirmation"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="{ duration: 0.2 }"
                    class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                    @click.self="closeStopConfirmation"
                >
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.9, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :exit="{ opacity: 0, scale: 0.9, y: 20 }"
                        :transition="{ type: 'spring', bounce: 0.3, duration: 0.4 }"
                        class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 max-h-[90vh] overflow-y-auto"
                    >
                        <div class="text-center">
                            <motion.div
                                :initial="{ scale: 0, rotate: -180 }"
                                :animate="{ scale: 1, rotate: 0 }"
                                :transition="{ type: 'spring', bounce: 0.5, duration: 0.6, delay: 0.1 }"
                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4"
                            >
                                <svg
                                    class="h-6 w-6 text-yellow-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                            </motion.div>
                            <motion.h3
                                :initial="{ opacity: 0, y: -10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: 0.15, duration: 0.3 }"
                                class="text-lg font-semibold text-gray-900 mb-2"
                            >
                                Akhiri Perjalanan?
                            </motion.h3>
                            <motion.p
                                :initial="{ opacity: 0, y: -10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: 0.2, duration: 0.3 }"
                                class="text-sm text-gray-600 mb-6"
                            >
                                Apakah Anda yakin ingin mengakhiri perjalanan ini?
                                Data kecepatan akan disimpan.
                            </motion.p>
                        </div>
                        <motion.div
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.25, duration: 0.3 }"
                            class="flex space-x-3"
                        >
                            <motion.button
                                type="button"
                                :whileHover="{ scale: 1.02, y: -1 }"
                                :whilePress="{ scale: 0.98 }"
                                :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
                                class="flex-1 min-h-[44px] py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200"
                                @click="closeStopConfirmation"
                            >
                                Batal
                            </motion.button>
                            <motion.button
                                type="button"
                                :whileHover="{ scale: 1.02, y: -1 }"
                                :whilePress="{ scale: 0.98 }"
                                :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
                                class="flex-1 min-h-[44px] py-3 px-6 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200"
                                @click="confirmStopTrip"
                            >
                                Ya, Akhiri
                            </motion.button>
                        </motion.div>
                    </motion.div>
                </motion.div>
            </AnimatePresence>
        </Teleport>
    </div>
</template>
