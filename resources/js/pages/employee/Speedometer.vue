<!--
VeloTrack - Production GPS Speedometer

Full production speedometer with backend integration via Trip Store and Settings Store.
Features professional design from HTML spec while maintaining proper API integration.
Uses EmployeeLayout for consistent navigation across all employee pages.
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
    inactivityDuration: settingsStore.auto_stop_duration,
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
// Fetch Settings on Mount
// ========================================================================

onMounted(async () => {
    // Fetch settings from backend if not loaded
    if (!settingsStore.isLoaded) {
        // Settings will be loaded via API in settings store
        localSpeedLimit.value = settingsStore.speed_limit;
    } else {
        localSpeedLimit.value = settingsStore.speed_limit;
    }

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

const speedLimit = computed(() => localSpeedLimit.value);

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
// Speed Limit Controls
// ========================================================================

function changeLimit(delta: number) {
    localSpeedLimit.value = Math.max(10, Math.min(200, localSpeedLimit.value + delta));
}

function setUnit(newUnit: 'kmh' | 'mph') {
    unit.value = newUnit;
}

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

        <div class="speedometer-page">
            <!-- Header -->
            <header class="velo-header">
                <div class="header-left">
                    <div class="velo-logo">Velo<span>Track</span></div>
                </div>
                <div class="status-indicator">
                    <div :class="['status-dot', gpsStatusClass]" />
                    <span>{{ gpsStatus }}</span>
                </div>
            </header>

        <!-- Main -->
        <main class="velo-main">
            <!-- Speed Limit Banner -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="limit-banner"
            >
                <div>
                    <div class="limit-label">Speed Limit</div>
                </div>
                <div class="limit-controls">
                    <motion.button
                        @click="changeLimit(-10)"
                        :whilePress="{ scale: 0.9 }"
                        :whileHover="{ scale: 1.05 }"
                        :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                    >
                        −
                    </motion.button>
                    <motion.div
                        :animate="{ scale: [1, 1.05, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="speedLimit"
                        class="limit-value"
                    >
                        {{ speedLimit }} {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </motion.div>
                    <motion.button
                        @click="changeLimit(10)"
                        :whilePress="{ scale: 0.9 }"
                        :whileHover="{ scale: 1.05 }"
                        :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                    >
                        +
                    </motion.button>
                </div>
                <div class="unit-toggle">
                    <motion.button
                        :class="{ active: unit === 'kmh' }"
                        @click="setUnit('kmh')"
                        :whilePress="{ scale: 0.95 }"
                        :animate="{ scale: unit === 'kmh' ? 1.02 : 1 }"
                        :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
                    >
                        km/h
                    </motion.button>
                    <motion.button
                        :class="{ active: unit === 'mph' }"
                        @click="setUnit('mph')"
                        :whilePress="{ scale: 0.95 }"
                        :animate="{ scale: unit === 'mph' ? 1.02 : 1 }"
                        :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
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
                    :speed-limit="speedLimit"
                    :unit="unit"
                />
            </motion.div>

            <!-- Stats Grid -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
                class="stats-grid"
            >
                <motion.div
                    :whileHover="{ scale: 1.03, y: -2 }"
                    :whilePress="{ scale: 0.98 }"
                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                    class="stat-card danger"
                >
                    <div class="stat-label">Max Speed</div>
                    <motion.div
                        :animate="{ scale: [1, 1.1, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="Math.round(maxSpeed)"
                        class="stat-value"
                    >
                        {{ Math.round(maxSpeed) }}
                    </motion.div>
                    <div class="stat-unit">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </motion.div>
                <motion.div
                    :whileHover="{ scale: 1.03, y: -2 }"
                    :whilePress="{ scale: 0.98 }"
                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                    class="stat-card warn"
                >
                    <div class="stat-label">Avg Speed</div>
                    <motion.div
                        :animate="{ scale: [1, 1.1, 1] }"
                        :transition="{ duration: 0.3 }"
                        :key="Math.round(avgSpeed)"
                        class="stat-value"
                    >
                        {{ Math.round(avgSpeed) }}
                    </motion.div>
                    <div class="stat-unit">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </motion.div>
            </motion.div>

            <!-- Trip Bar -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.3 }"
                class="trip-bar"
            >
                <div class="trip-item">
                    <motion.div
                        :animate="{ scale: [1, 1.08, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="tripDistance.toFixed(2)"
                        class="trip-val"
                    >
                        {{ tripDistance.toFixed(2) }}
                    </motion.div>
                    <div class="trip-lbl">Distance ({{ unit === 'kmh' ? 'km' : 'mi' }})</div>
                </div>
                <div class="trip-divider" />
                <div class="trip-item">
                    <motion.div
                        :animate="{ scale: [1, 1.08, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="tripStore.stats.duration"
                        class="trip-val"
                    >
                        {{ Math.floor(tripStore.stats.duration / 60).toString().padStart(2, '0') }}:{{ (tripStore.stats.duration % 60).toString().padStart(2, '0') }}
                    </motion.div>
                    <div class="trip-lbl">Duration</div>
                </div>
                <div class="trip-divider" />
                <div class="trip-item">
                    <motion.div
                        :animate="{ scale: [1, 1.08, 1] }"
                        :transition="{ duration: 0.4 }"
                        :key="satelliteCount"
                        class="trip-val"
                    >
                        {{ satelliteCount || '—' }}
                    </motion.div>
                    <div class="trip-lbl">Satellites</div>
                </div>
            </motion.div>

            <!-- GPS Accuracy -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.4 }"
                class="accuracy-row"
            >
                <div class="accuracy-label">GPS Accuracy</div>
                <div class="accuracy-bar">
                    <motion.div
                        :animate="{
                            width: accuracyPercentage + '%',
                        }"
                        :transition="{ type: 'spring', bounce: 0.2, duration: 0.8 }"
                        class="accuracy-fill"
                        :style="{
                            background: accuracyColor,
                        }"
                    />
                </div>
                <div class="accuracy-text">
                    {{ accuracy !== null ? Math.round(accuracy) + ' m' : '— m' }}
                </div>
            </motion.div>

            <!-- Trip Controls -->
            <TripControls />
        </main>
        </div>
    </EmployeeLayout>
</template>

<style scoped>
.speedometer-page {
    background: #0a0c0f;
    color: #e8eaf0;
    font-family: 'Barlow', sans-serif;
    display: flex;
    flex-direction: column;
    position: relative;
}

/* Noise overlay */
.speedometer-page::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 999;
    opacity: 0.5;
}

.velo-header {
    width: 100%;
    padding: 18px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #1e2230;
    background: rgba(10, 12, 15, 0.95);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 10;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.back-button {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #1e2230;
    background: #111318;
    color: #e8eaf0;
    transition: all 0.2s;
    cursor: pointer;
}

.back-button:hover {
    border-color: #00e5ff;
    color: #00e5ff;
    background: #0a0c0f;
}

.back-icon {
    width: 20px;
    height: 20px;
}

.velo-logo {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    letter-spacing: 3px;
    color: #00e5ff;
    text-shadow: 0 0 20px rgba(0, 229, 255, 0.35);
}

.velo-logo span {
    color: #e8eaf0;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #4a5068;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4a5068;
    transition: all 0.4s;
}

.status-dot.active {
    background: #00e676;
    box-shadow: 0 0 8px #00e676;
    animation: pulse 2s infinite;
}

.status-dot.warn {
    background: #ffab00;
    box-shadow: 0 0 8px #ffab00;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.velo-main {
    width: 100%;
    max-width: 28rem;
    padding: 24px 16px 40px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

@media (min-width: 768px) {
    .velo-main {
        max-width: 32rem;
    }
}

@media (min-width: 1024px) {
    .velo-main {
        max-width: 42rem;
    }
}

/* Landscape mode optimizations (short screens) */
@media (orientation: landscape) and (max-height: 500px) {
    .velo-main {
        padding: 16px 16px 20px;
        gap: 12px;
    }

    .gauge-container {
        max-height: 60vh;
    }

    .stats-grid,
    .trip-bar,
    .accuracy-row {
        transform: scale(0.9);
    }

    .limit-banner {
        padding: 8px 12px;
    }
}

.limit-banner {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    background: #111318;
    border: 1px solid #1e2230;
    border-radius: 14px;
    padding: 12px 18px;
    gap: 12px;
}

.limit-label {
    font-size: 0.875rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #4a5068;
}

.limit-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.limit-controls button {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    border: 1px solid #1e2230;
    background: #0a0c0f;
    color: #e8eaf0;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.limit-controls button:hover {
    border-color: #00e5ff;
    color: #00e5ff;
}

.limit-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.4rem;
    letter-spacing: 2px;
    color: #e8eaf0;
    min-width: 80px;
    text-align: center;
}

.unit-toggle {
    display: flex;
    background: #0a0c0f;
    border: 1px solid #1e2230;
    border-radius: 8px;
    overflow: hidden;
}

.unit-toggle button {
    min-height: 44px;
    padding: 10px 16px;
    border: none;
    background: transparent;
    color: #4a5068;
    font-size: 0.875rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
}

.unit-toggle button.active {
    background: #00e5ff;
    color: #000;
    font-weight: 600;
}

.stats-grid {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.stat-card {
    background: #111318;
    border: 1px solid #1e2230;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #00e5ff;
    opacity: 0.4;
}

.stat-card.danger::before {
    background: #ff3d57;
    opacity: 0.6;
}

.stat-card.warn::before {
    background: #ffab00;
    opacity: 0.6;
}

.stat-label {
    font-size: 0.875rem;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #4a5068;
}

.stat-value {
    font-family: 'Share Tech Mono', monospace;
    font-size: 1.7rem;
    color: #e8eaf0;
    line-height: 1;
}

.stat-unit {
    font-size: 0.65rem;
    color: #4a5068;
    letter-spacing: 1px;
}

.trip-bar {
    width: 100%;
    background: #111318;
    border: 1px solid #1e2230;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.trip-item {
    text-align: center;
}

.trip-val {
    font-family: 'Share Tech Mono', monospace;
    font-size: 1.3rem;
    color: #00e5ff;
}

.trip-lbl {
    font-size: 0.875rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #4a5068;
    margin-top: 3px;
}

.trip-divider {
    width: 1px;
    height: 36px;
    background: #1e2230;
}

.accuracy-row {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 4px;
}

.accuracy-label {
    font-size: 0.875rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #4a5068;
    white-space: nowrap;
}

.accuracy-bar {
    flex: 1;
    height: 3px;
    background: #1e2230;
    border-radius: 2px;
    overflow: hidden;
}

.accuracy-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.5s, background 0.5s;
}

.accuracy-text {
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.72rem;
    color: #4a5068;
    min-width: 50px;
    text-align: right;
}
</style>
