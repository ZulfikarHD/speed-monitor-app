<!--
Employee Speedometer Page

Complete speedometer interface integrating GPS tracking, trip management,
and real-time statistics display. Mobile-first design optimized for
portrait phone usage during commute sessions.

Features:
- Real-time speed gauge (270° arc with color zones)
- Trip start/stop controls with GPS integration
- Live statistics (speed, distance, duration, violations)
- Mobile-optimized layout (touch-friendly buttons, full viewport)
- Automatic speed logging every ~5 seconds
- Speed log batching (sync every 10 logs/50s)
- Route guard: employee role only

Integration:
- SpeedGauge: Visual speedometer display
- TripControls: Start/stop buttons + GPS permission
- TripStats: Real-time metrics grid
- useGeolocation: GPS speed tracking
- Trip Store: Session management + speed logging
- Settings Store: Speed limit configuration

Page Flow:
1. Employee navigates from dashboard
2. Page loads with idle state (no active trip)
3. User clicks "Start Trip" → GPS permission requested
4. If granted → trip starts → speed logging begins
5. Real-time updates: gauge + stats display current data
6. Speed logs buffered locally, synced every 10 logs
7. User clicks "Stop Trip" → confirmation shown
8. If confirmed → remaining logs synced → trip ends
-->

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import SpeedGauge from '@/components/speedometer/SpeedGauge.vue';
import TripControls from '@/components/speedometer/TripControls.vue';
import TripStats from '@/components/speedometer/TripStats.vue';
import { useGeolocation } from '@/composables/useGeolocation';
import { useViolationAlert } from '@/composables/useViolationAlert';
import { useSettingsStore } from '@/stores/settings';
import { useTripStore } from '@/stores/trip';

// ========================================================================
// Store and Composable Integration
// ========================================================================

/**
 * Trip store for session management and speed logging.
 *
 * WHY: Centralized state for current trip, speed logs, and statistics.
 * WHY: Provides actions for start/stop/sync operations.
 */
const tripStore = useTripStore();

/**
 * Settings store for speed limit configuration.
 *
 * WHY: Speed limit is admin-configurable, needs to be fetched from settings.
 * WHY: Used for violation detection and gauge marker display.
 */
const settingsStore = useSettingsStore();

/**
 * Geolocation composable for real-time speed tracking.
 *
 * WHY: Provides GPS-based speed data in km/h.
 * WHY: speedKmh is reactive and updates on GPS position changes.
 */
const { speedKmh } = useGeolocation();

/**
 * Violation alert composable for multi-channel alerting.
 *
 * WHY: Provides browser notifications, audio beeps, and violation tracking.
 * WHY: Handles permission requests and cooldown logic.
 */
const {
    checkViolation,
    triggerAlert,
    requestNotificationPermission,
    resetViolationState,
} = useViolationAlert();

// ========================================================================
// Component Refs
// ========================================================================

/**
 * Reference to SpeedGauge component instance.
 *
 * WHY: Needed to call triggerFlash() method imperatively.
 * WHY: TypeScript type ensures compile-time safety for method calls.
 */
const speedGaugeRef = ref<InstanceType<typeof SpeedGauge> | null>(null);

// ========================================================================
// Computed Properties
// ========================================================================

/**
 * Current speed limit from settings.
 *
 * WHY: Dynamic speed limit allows admin configuration changes.
 * WHY: Defaults to 60 km/h if settings not loaded yet.
 */
const speedLimit = computed<number>(() => settingsStore.speed_limit);

/**
 * Whether there's an active trip in progress.
 *
 * WHY: Used to conditionally show stats component.
 * WHY: Provides better UX by hiding empty stats when no trip.
 */
const hasActiveTrip = computed<boolean>(() => tripStore.hasActiveTrip);

// ========================================================================
// Violation Detection Logic
// ========================================================================

/**
 * Watch for speed changes and check for violations.
 *
 * Monitors current speed against speed limit and triggers multi-channel
 * alerts when violation detected. Only active during trip with alerts enabled.
 *
 * WHY: Reactive watch automatically triggers when speed or limit changes.
 * WHY: Early returns prevent unnecessary checks when conditions not met.
 * WHY: Multi-channel approach ensures user notices (notification + audio + visual).
 */
watch(
    [speedKmh, speedLimit],
    ([currentSpeed, limit]) => {
        // Skip if alerts disabled in settings
        if (!settingsStore.settings.violation_alerts_enabled) {
            return;
        }

        // Skip if no active trip
        if (!hasActiveTrip.value) {
            return;
        }

        // Check if violation should trigger alert (handles cooldown logic)
        if (checkViolation(currentSpeed, limit)) {
            // Fire notification and audio alerts
            triggerAlert(currentSpeed, limit);

            // Trigger visual flash animation on gauge
            speedGaugeRef.value?.triggerFlash();
        }
    },
    { immediate: false },
);

/**
 * Request notification permission when trip starts.
 *
 * Automatically requests browser notification permission when user begins
 * trip session. Permission only requested if alerts are enabled in settings.
 *
 * WHY: Permission must be requested in response to user action (trip start).
 * WHY: Avoids permission prompt on page load (better UX).
 * WHY: Reset violation state ensures clean tracking for new trip.
 */
watch(
    () => tripStore.hasActiveTrip,
    (isActive) => {
        if (isActive && settingsStore.settings.violation_alerts_enabled) {
            // Request notification permission
            requestNotificationPermission();
        } else {
            // Clear violation state when trip ends
            resetViolationState();
        }
    },
);
</script>

<template>
    <Head title="Speedometer" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <!-- ================================================================ -->
        <!-- Page Header -->
        <!-- ================================================================ -->

        <header
            class="sticky top-0 z-10 border-b border-[#e3e3e0] bg-white/80 backdrop-blur-sm dark:border-[#3E3E3A] dark:bg-[#161615]/80"
        >
            <div class="mx-auto max-w-2xl px-4 py-4">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <Link
                        href="/employee/dashboard"
                        class="flex items-center justify-center rounded-lg border border-[#e3e3e0] bg-white p-2 text-[#1b1b18] transition-colors hover:bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:hover:bg-[#161615]"
                        aria-label="Back to dashboard"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </Link>

                    <!-- Page Title -->
                    <div class="flex-1">
                        <h1
                            class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Speedometer
                        </h1>
                        <p
                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Track your trip speed
                        </p>
                    </div>

                    <!-- Alert Toggle Button -->
                    <button
                        @click="
                            settingsStore.updateSetting(
                                'violation_alerts_enabled',
                                !settingsStore.settings.violation_alerts_enabled,
                            )
                        "
                        :class="[
                            'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                            settingsStore.settings.violation_alerts_enabled
                                ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700',
                        ]"
                        :aria-label="
                            settingsStore.settings.violation_alerts_enabled
                                ? 'Disable violation alerts'
                                : 'Enable violation alerts'
                        "
                        :title="
                            settingsStore.settings.violation_alerts_enabled
                                ? 'Click to disable alerts'
                                : 'Click to enable alerts'
                        "
                    >
                        <!-- Bell Icon -->
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                v-if="settingsStore.settings.violation_alerts_enabled"
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
                            />
                            <path
                                v-else
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
                                opacity="0.5"
                            />
                        </svg>

                        <!-- Status Text -->
                        <span class="hidden sm:inline">
                            {{
                                settingsStore.settings.violation_alerts_enabled
                                    ? 'Alerts On'
                                    : 'Alerts Off'
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ================================================================ -->
        <!-- Main Content -->
        <!-- ================================================================ -->

        <main class="mx-auto max-w-2xl px-4 pb-8 pt-6">
            <!-- Speed Gauge Section -->
            <section class="mb-8">
                <div class="flex justify-center">
                    <SpeedGauge
                        ref="speedGaugeRef"
                        :speed="speedKmh"
                        :speed-limit="speedLimit"
                        size="lg"
                    />
                </div>
            </section>

            <!-- Trip Controls Section -->
            <section class="mb-8">
                <TripControls />
            </section>

            <!-- Trip Statistics Section -->
            <section v-if="hasActiveTrip">
                <TripStats />
            </section>

            <!-- Empty State (No Active Trip) -->
            <section v-else>
                <div
                    class="rounded-lg border border-[#e3e3e0] bg-white p-6 text-center dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <div class="mb-2 text-4xl">🚗</div>
                    <h3
                        class="mb-1 text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        No Active Trip
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Start a trip to see real-time statistics and track your
                        speed
                    </p>
                </div>
            </section>
        </main>

        <!-- ================================================================ -->
        <!-- Info Footer -->
        <!-- ================================================================ -->

        <footer class="mx-auto max-w-2xl px-4 pb-6">
            <div
                class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-900/10"
            >
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h4
                            class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200"
                        >
                            Speed Tracking Tips
                        </h4>
                        <ul
                            class="space-y-1 text-xs text-blue-700 dark:text-blue-300"
                        >
                            <li>
                                • Keep your phone in a stable position during
                                the trip
                            </li>
                            <li>
                                • Ensure GPS is enabled for accurate speed
                                tracking
                            </li>
                            <li>
                                • Speed is logged automatically every ~5 seconds
                            </li>
                            <li>
                                • Data syncs to server every 50 seconds or when
                                trip ends
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
