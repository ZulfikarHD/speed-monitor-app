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
import { estimateSatelliteCount, mpsToDisplay } from '@/utils/units';

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

const satelliteCount = computed(() => estimateSatelliteCount(accuracy.value));

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
const avgSpeed = computed(() => mpsToDisplay(tripStore.stats.averageSpeed, unit.value));

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
        <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Speed Limit Banner (Read-Only) -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-zinc-200 bg-white backdrop-blur-sm px-5 py-4 dark:border-white/5 dark:bg-zinc-800/50"
            >
                <div class="flex flex-col gap-1">
                    <div class="text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        Speed Limit
                    </div>
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="currentSpeedLimit"
                        class="text-3xl font-bold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Bebas Neue', sans-serif"
                    >
                        {{ currentSpeedLimit }} {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </motion.div>
                    <div class="text-xs italic text-zinc-500 dark:text-zinc-500">
                        Diatur oleh supervisor
                    </div>
                </div>
                <div class="flex rounded-lg border border-zinc-200 bg-zinc-100 p-1 dark:border-white/5 dark:bg-zinc-900">
                    <motion.button
                        :class="[
                            'min-h-[44px] rounded-md px-4 py-2 text-sm font-medium transition-all active:scale-95',
                            unit === 'kmh'
                                ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white shadow-lg shadow-cyan-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25'
                                : 'text-zinc-600 hover:bg-white hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'
                        ]"
                        @click="setUnit('kmh')"
                        :whilePress="{ scale: 0.95 }"
                    >
                        km/h
                    </motion.button>
                    <motion.button
                        :class="[
                            'min-h-[44px] rounded-md px-4 py-2 text-sm font-medium transition-all active:scale-95',
                            unit === 'mph'
                                ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white shadow-lg shadow-cyan-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25'
                                : 'text-zinc-600 hover:bg-white hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'
                        ]"
                        @click="setUnit('mph')"
                        :whilePress="{ scale: 0.95 }"
                    >
                        mph
                    </motion.button>
                </div>
            </motion.div>

            <!-- Gauge -->
            <motion.div
                :initial="{ opacity: 0, scale: 0.9 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.7, delay: 0.1 }"
            >
                <ProductionGauge
                    :speed="currentSpeed"
                    :speed-limit="currentSpeedLimit"
                    :unit="unit"
                />
            </motion.div>

            <!-- Stats Grid -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
                class="mb-6 grid grid-cols-2 gap-4"
            >
                <motion.div
                    :whileHover="{ scale: 1.02, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
                    class="rounded-lg border border-red-200 bg-white backdrop-blur-sm p-4 dark:border-red-500/20 dark:bg-red-500/5"
                >
                    <div class="mb-1 text-xs font-medium uppercase tracking-wide text-red-600 dark:text-red-400">
                        Max Speed
                    </div>
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="Math.round(maxSpeed)"
                        class="text-3xl font-bold text-red-600 dark:text-red-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ Math.round(maxSpeed) }}
                    </motion.div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-500">
                        {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </div>
                </motion.div>
                <motion.div
                    :whileHover="{ scale: 1.02, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
                    class="rounded-lg border border-amber-200 bg-white backdrop-blur-sm p-4 dark:border-amber-500/20 dark:bg-amber-500/5"
                >
                    <div class="mb-1 text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-400">
                        Avg Speed
                    </div>
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="Math.round(avgSpeed)"
                        class="text-3xl font-bold text-amber-600 dark:text-amber-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ Math.round(avgSpeed) }}
                    </motion.div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-500">
                        {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </div>
                </motion.div>
            </motion.div>

            <!-- Trip Bar -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.3 }"
                class="mb-6 flex items-center justify-between rounded-lg border border-zinc-200 bg-white backdrop-blur-sm px-5 py-4 dark:border-white/5 dark:bg-zinc-800/50"
            >
                <div class="text-center">
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="tripDistance.toFixed(2)"
                        class="text-2xl font-bold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ tripDistance.toFixed(2) }}
                    </motion.div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        Distance ({{ unit === 'kmh' ? 'km' : 'mi' }})
                    </div>
                </div>
                <div class="h-9 w-px bg-zinc-200 dark:bg-white/5" />
                <div class="text-center">
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="tripStore.stats.duration"
                        class="text-2xl font-bold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ Math.floor(tripStore.stats.duration / 60).toString().padStart(2, '0') }}:{{ (tripStore.stats.duration % 60).toString().padStart(2, '0') }}
                    </motion.div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        Duration
                    </div>
                </div>
                <div class="h-9 w-px bg-zinc-200 dark:bg-white/5" />
                <div class="text-center">
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="satelliteCount"
                        class="text-2xl font-bold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ satelliteCount || '—' }}
                    </motion.div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                        Satellites
                    </div>
                </div>
            </motion.div>

            <!-- GPS Accuracy -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.4 }"
                class="mb-6 flex items-center gap-3 px-1"
            >
                <div class="text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                    GPS Accuracy
                </div>
                <div class="h-1 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                    <motion.div
                        :animate="{ width: accuracyPercentage + '%' }"
                        :transition="{ type: 'spring', bounce: 0.2, duration: 0.8 }"
                        class="h-full rounded-full transition-colors"
                        :style="{ background: accuracyColor }"
                    />
                </div>
                <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300" style="font-family: 'Share Tech Mono', monospace">
                    {{ accuracy !== null ? Math.round(accuracy) + ' m' : '— m' }}
                </div>
            </motion.div>

            <!-- Trip Controls -->
            <TripControls />
        </main>
        </div>
    </EmployeeLayout>
</template>
