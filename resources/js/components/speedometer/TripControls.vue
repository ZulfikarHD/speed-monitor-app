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
import { AlertTriangle, CircleAlert, Loader2, Play, Square } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, ref } from 'vue';

import { useGeolocation } from '@/composables/useGeolocation';
import { useTripStore } from '@/stores/trip';

interface Props {
    shiftType?: string;
    vehicleType?: string;
}

const props = defineProps<Props>();

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
        await tripStore.startTrip({
            shift_type: props.shiftType,
            vehicle_type: props.vehicleType,
        });

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
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="w-full min-h-[44px] py-4 px-6 rounded-lg font-semibold text-white transition-all duration-200 hover:shadow-lg active:scale-[0.98] focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300/50 dark:focus-visible:ring-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 bg-linear-to-r from-emerald-600 to-green-700 dark:from-emerald-500 dark:to-green-600"
                    :disabled="isLoading"
                    @click="handleStartTrip"
                >
                    <span v-if="tripStore.isStarting" class="flex items-center justify-center gap-3">
                        <Loader2 :size="20" class="animate-spin shrink-0" />
                        Memulai...
                    </span>
                    <span v-else class="inline-flex items-center justify-center gap-2">
                        <Play :size="20" class="shrink-0" />
                        Mulai Perjalanan
                    </span>
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
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="w-full min-h-[44px] py-4 px-6 rounded-lg font-semibold text-white transition-all duration-200 hover:shadow-lg active:scale-[0.98] focus:outline-none focus-visible:ring-4 focus-visible:ring-red-300/50 dark:focus-visible:ring-red-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 bg-linear-to-r from-red-600 to-rose-700 dark:from-red-500 dark:to-rose-600"
                    :disabled="isLoading"
                    @click="openStopConfirmation"
                >
                    <span v-if="tripStore.isEnding" class="flex items-center justify-center gap-3">
                        <Loader2 :size="20" class="animate-spin shrink-0" />
                        Mengakhiri...
                    </span>
                    <span v-else class="inline-flex items-center justify-center gap-2">
                        <Square :size="20" class="shrink-0" />
                        Akhiri Perjalanan
                    </span>
                </motion.button>
            </AnimatePresence>
        </div>

        <!-- ============================================================
             Error Message Display
             ============================================================ -->
        <div
            v-if="errorMessage"
            class="rounded-lg border p-4 bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-800 dark:text-red-300"
        >
            <div class="flex items-start gap-3">
                <CircleAlert :size="20" class="mt-0.5 shrink-0 text-red-600 dark:text-red-400" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm">
                        {{ errorMessage }}
                    </p>
                </div>
            </div>
        </div>

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
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="closeStopConfirmation"
                >
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.9, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :exit="{ opacity: 0, scale: 0.9, y: 20 }"
                        :transition="{ type: 'spring', bounce: 0.3, duration: 0.4 }"
                        class="max-h-[90vh] w-full max-w-sm overflow-y-auto rounded-lg border border-zinc-200/80 bg-white p-6 text-zinc-900 shadow-xl ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800 dark:text-white dark:ring-white/5"
                    >
                        <div class="text-center">
                            <div
                                class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-500/20"
                            >
                                <AlertTriangle :size="24" class="text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">
                                Akhiri Perjalanan?
                            </h3>
                            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-300">
                                Apakah Anda yakin ingin mengakhiri perjalanan ini?
                                Data kecepatan akan disimpan.
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                class="min-h-[44px] flex-1 rounded-lg bg-zinc-100 px-6 py-3 font-medium text-zinc-700 transition-all duration-200 hover:bg-zinc-200 active:scale-[0.98] dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600"
                                @click="closeStopConfirmation"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="min-h-[44px] flex-1 rounded-lg bg-red-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:bg-red-700 hover:shadow-lg active:scale-[0.98] dark:bg-red-500 dark:hover:bg-red-600"
                                @click="confirmStopTrip"
                            >
                                Ya, Akhiri
                            </button>
                        </div>
                    </motion.div>
                </motion.div>
            </AnimatePresence>
        </Teleport>
    </div>
</template>
