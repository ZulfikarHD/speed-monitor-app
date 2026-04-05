<!--
SpeedMonitor - Production GPS Speedometer

Full production speedometer with backend integration via Trip Store and Settings Store.
Redesigned with SpeedMonitor design system featuring theme-aware styling and modern UX.
Uses EmployeeLayout for consistent navigation across all employee pages.

Features:
- Theme-aware styling with full light/dark mode support
- SpeedMonitor branding and professional design
- Real-time GPS tracking with accuracy indicators
- Speed limit monitoring with visual violations
- Trip statistics and duration tracking
- Auto-stop functionality for safety
- Offline sync support with background sync
- Responsive design with proper layout containment
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import OfflineIndicator from '@/components/offline/OfflineIndicator.vue';
import ProductionGauge from '@/components/speedometer/ProductionGauge.vue';
import TripControls from '@/components/speedometer/TripControls.vue';
import { useAutoStop } from '@/composables/useAutoStop';
import { useBackgroundSync } from '@/composables/useBackgroundSync';
import { useGeolocation } from '@/composables/useGeolocation';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useSettingsStore } from '@/stores/settings';
import { useTripStore } from '@/stores/trip';
import { haversineDistance, metersToKm, metersToMiles } from '@/utils/distance';
import { mpsToDisplay } from '@/utils/units';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Speed limit from backend settings (km/h) */
    speedLimit: number;
    /** Auto-stop duration from backend settings (seconds) */
    autoStopDuration: number;
}

const props = defineProps<Props>();

// ========================================================================
// Store Integration
// ========================================================================

const tripStore = useTripStore();
const settingsStore = useSettingsStore();
const { speedKmh, speedMps, accuracy, coords, stopTracking } = useGeolocation();

/**
 * Background sync composable for automatic synchronization.
 *
 * Provides auto-sync functionality and state tracking.
 */
const {
    isSyncing: isBackgroundSyncing,
    isAutoSyncEnabled,
    startManualSync: triggerBackgroundSync,
} = useBackgroundSync();

// ========================================================================
// Local State
// ========================================================================

const unit = ref<'kmh' | 'mph'>('kmh');
const localSpeedLimit = ref<number>(60);
const lastPosition = ref<{ lat: number; lon: number } | null>(null);
const pendingSyncCount = ref<number>(0);

// ========================================================================
// Auto-Stop Monitoring
// ========================================================================

const autoStop = useAutoStop({
    inactivityDuration: props.autoStopDuration,
    speedThreshold: 5,
    onWarning: () => {
        alert('⚠️ Peringatan: Trip akan berhenti otomatis dalam 5 menit karena tidak ada pergerakan.');
    },
    onAutoStop: async () => {
        await tripStore.endTrip('Auto-stopped: no movement detected');
        stopTracking();
        alert('🛑 Trip dihentikan otomatis karena tidak ada pergerakan selama 30 menit.');
    },
});

// ========================================================================
// Initialize Settings on Mount
// ========================================================================

onMounted(async () => {
    // Initialize settings store with backend values
    // WHY: Ensures speedometer uses supervisor-configured limits, not defaults
    settingsStore.setSettings({
        speed_limit: props.speedLimit,
        auto_stop_duration: props.autoStopDuration,
        violation_threshold: props.speedLimit, // Violation occurs when exceeding limit
    });

    // Set local speed limit from initialized store
    localSpeedLimit.value = props.speedLimit;

    // Load pending sync count
    await updatePendingSyncCount();
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

/**
 * Handle manual sync request from OfflineIndicator.
 *
 * Delegates to background sync composable for centralized sync logic.
 */
const handleManualSync = async (): Promise<void> => {
    try {
        await triggerBackgroundSync();
        await updatePendingSyncCount();
    } catch (error: any) {
        console.error('[Speedometer] Manual sync error:', error);
    }
};

// ========================================================================
// Distance Calculation (Haversine)
// ========================================================================

watch([coords], () => {
    if (!tripStore.hasActiveTrip) {
return;
}

    if (!coords.value.latitude || !coords.value.longitude) {
return;
}

    if (lastPosition.value) {
        const dist = haversineDistance(
            lastPosition.value.lat,
            lastPosition.value.lon,
            coords.value.latitude,
            coords.value.longitude,
        );

        // Update trip store distance
        tripStore.stats.distance += dist;
    }

    lastPosition.value = {
        lat: coords.value.latitude,
        lon: coords.value.longitude,
    };
});

// ========================================================================
// Computed
// ========================================================================

const currentSpeed = computed(() => mpsToDisplay(speedMps.value, unit.value));

const currentSpeedLimit = computed(() => localSpeedLimit.value);

const accuracyPercentage = computed(() => {
    if (!accuracy.value) {
return 0;
}

    return Math.max(0, Math.min(100, 100 - (accuracy.value / 50) * 100));
});

const accuracyColor = computed(() => {
    const pct = accuracyPercentage.value;

    if (pct > 60) {
return '#00e5ff';
}

    if (pct > 30) {
return '#ffab00';
}

    return '#ff3d57';
});

const gpsStatus = computed(() => {
    if (!tripStore.hasActiveTrip) {
return 'Stopped';
}

    if (accuracy.value === null) {
return 'Acquiring GPS…';
}

    if (accuracy.value > 50) {
return 'GPS Weak';
}

    return 'GPS Active';
});

const gpsStatusClass = computed(() => {
    if (!tripStore.hasActiveTrip) {
return 'muted';
}

    if (accuracy.value === null) {
return 'warn';
}

    if (accuracy.value > 50) {
return 'warn';
}

    return 'active';
});

const tripDistance = computed(() => {
    return unit.value === 'kmh'
        ? metersToKm(tripStore.stats.distance)
        : metersToMiles(tripStore.stats.distance);
});

const maxSpeed = computed(() => mpsToDisplay(tripStore.stats.maxSpeed, unit.value));

// ========================================================================
// Speed Unit Controls (km/h or mph)
// ========================================================================

/**
 * Change speed display unit between km/h and mph.
 *
 * WHY: Allow users to view speed in their preferred unit.
 * WHY: Speed limit enforcement still uses backend km/h value.
 */
function setUnit(newUnit: 'kmh' | 'mph') {
    unit.value = newUnit;
}

// ========================================================================
// Settings Sync Watcher
// ========================================================================

/**
 * Watch for settings updates from store.
 *
 * WHY: When supervisor changes settings, all employees should immediately
 * use the new limits without page reload. This ensures consistent enforcement.
 */
watch(
    () => settingsStore.settings.speed_limit,
    (newLimit) => {
        localSpeedLimit.value = newLimit;
    }
);

// ========================================================================
// Auto-Stop Integration
// ========================================================================

watch(() => tripStore.hasActiveTrip, (isActive) => {
    if (isActive) {
        autoStop.startMonitoring(() => speedKmh.value);
    } else {
        autoStop.stopMonitoring();
        lastPosition.value = null;
    }
});

// ========================================================================
// Cleanup
// ========================================================================

onBeforeUnmount(() => {
    autoStop.stopMonitoring();
    lastPosition.value = null;
});
</script>

<template>
    <EmployeeLayout title="Speedometer">
        <!-- ============================================================ -->
        <!-- OFFLINE INDICATOR (Fixed position, outside main layout) -->
        <!-- ============================================================ -->
        <OfflineIndicator
            :pending-count="pendingSyncCount"
            :is-syncing="isBackgroundSyncing"
            :is-auto-sync-enabled="isAutoSyncEnabled"
            @sync="handleManualSync"
        />

        <!-- ============================================================
             Main Container with proper background containment
             ============================================================ -->
        <div class="min-h-screen bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black">
            <!-- Header -->
            <header class="sticky top-0 z-10 w-full border-b border-zinc-200 bg-white/90 backdrop-blur-xl px-7 py-4.5 dark:border-white/5 dark:bg-zinc-900/95">
                <div class="mx-auto flex max-w-7xl items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="text-2xl font-bold tracking-wider text-cyan-600 dark:text-cyan-400" style="font-family: 'Bebas Neue', sans-serif">
                            Speed<span class="text-zinc-900 dark:text-white">Monitor</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm font-medium uppercase tracking-wider">
                        <div :class="[
                            'h-2 w-2 rounded-full transition-all',
                            gpsStatusClass === 'active' ? 'bg-green-500 shadow-lg shadow-green-500/50 dark:shadow-green-500/30' :
                            gpsStatusClass === 'warn' ? 'bg-amber-500 shadow-lg shadow-amber-500/50 dark:shadow-amber-500/30' :
                            'bg-zinc-400 dark:bg-zinc-600'
                        ]" />
                        <span class="text-zinc-700 dark:text-zinc-300">{{ gpsStatus }}</span>
                    </div>
                </div>
            </header>

        <!-- Main Content -->
        <main class="mx-auto w-full max-w-2xl px-4 py-6 sm:px-6">
            <!-- Trip Controls - PRIMARY ACTION AT TOP (Fitts's Law) -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }"
                class="mb-6"
            >
                <TripControls />
            </motion.div>

            <!-- Speed Limit & Unit Toggle - COMPACT -->
            <div class="mb-4 flex items-center justify-between gap-4 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-zinc-800"
            >
                <div class="flex items-center gap-3">
                    <div class="text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        Limit:
                    </div>
                    <div class="text-xl font-bold text-cyan-600 dark:text-cyan-400" style="font-family: 'Bebas Neue', sans-serif">
                        {{ currentSpeedLimit }} {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </div>
                </div>
                <div class="flex rounded-lg border border-zinc-200 bg-zinc-50 p-0.5 dark:border-white/10 dark:bg-zinc-900/50">
                    <button
                        :class="[
                            'min-h-[36px] rounded-md px-3 py-1 text-xs font-medium transition-all',
                            unit === 'kmh'
                                ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white dark:from-cyan-500 dark:to-blue-600'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100'
                        ]"
                        @click="setUnit('kmh')"
                    >
                        km/h
                    </button>
                    <button
                        :class="[
                            'min-h-[36px] rounded-md px-3 py-1 text-xs font-medium transition-all',
                            unit === 'mph'
                                ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white dark:from-cyan-500 dark:to-blue-600'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100'
                        ]"
                        @click="setUnit('mph')"
                    >
                        mph
                    </button>
                </div>
            </div>

            <!-- Gauge -->
            <div v-if="tripStore.hasActiveTrip" class="mb-6">
                <ProductionGauge
                    :speed="currentSpeed"
                    :speed-limit="currentSpeedLimit"
                    :unit="unit"
                />
            </div>

            <!-- Active Trip Stats - PROGRESSIVE DISCLOSURE -->
            <div v-if="tripStore.hasActiveTrip" class="space-y-3">
                <!-- Compact Trip Bar -->
                <div class="flex items-center justify-around rounded-lg border border-zinc-200 bg-white py-3 dark:border-white/10 dark:bg-zinc-800">
                    <div class="text-center">
                        <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400" style="font-family: 'Share Tech Mono', monospace">
                            {{ tripDistance.toFixed(2) }}
                        </div>
                        <div class="text-[10px] font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            {{ unit === 'kmh' ? 'km' : 'mi' }}
                        </div>
                    </div>
                    <div class="h-8 w-px bg-zinc-200 dark:bg-white/10" />
                    <div class="text-center">
                        <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400" style="font-family: 'Share Tech Mono', monospace">
                            {{ Math.floor(tripStore.stats.duration / 60).toString().padStart(2, '0') }}:{{ (tripStore.stats.duration % 60).toString().padStart(2, '0') }}
                        </div>
                        <div class="text-[10px] font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Time
                        </div>
                    </div>
                    <div class="h-8 w-px bg-zinc-200 dark:bg-white/10" />
                    <div class="text-center">
                        <div class="text-lg font-bold" :class="accuracyPercentage > 60 ? 'text-green-600 dark:text-green-400' : accuracyPercentage > 30 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'" style="font-family: 'Share Tech Mono', monospace">
                            {{ Math.round(maxSpeed) }}
                        </div>
                        <div class="text-[10px] font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Max
                        </div>
                    </div>
                </div>

                <!-- GPS Accuracy - INLINE -->
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-zinc-800">
                    <div class="text-[10px] font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        GPS
                    </div>
                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div
                            class="h-full rounded-full transition-all duration-300"
                            :style="{ width: accuracyPercentage + '%', background: accuracyColor }"
                        />
                    </div>
                    <div class="text-[10px] font-medium text-zinc-700 dark:text-zinc-300" style="font-family: 'Share Tech Mono', monospace">
                        {{ accuracy !== null ? Math.round(accuracy) + 'm' : '—' }}
                    </div>
                </div>
            </div>

            <!-- Empty State - When No Active Trip -->
            <div
                v-else
                class="rounded-lg border border-zinc-200 bg-white p-6 text-center dark:border-white/10 dark:bg-zinc-800"
            >
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-900">
                    <svg class="h-6 w-6 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Start trip to begin tracking
                </p>
            </div>
        </main>
        </div>
    </EmployeeLayout>
</template>
