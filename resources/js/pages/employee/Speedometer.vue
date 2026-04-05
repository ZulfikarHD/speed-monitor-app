<!--
SpeedoMontor - Production GPS Speedometer

Full production speedometer with backend integration via Trip Store and Settings Store.
Features professional design from HTML spec while maintaining proper API integration.
Uses EmployeeLayout for consistent navigation across all employee pages.
-->

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import OfflineIndicator from '@/components/offline/OfflineIndicator.vue';
import DevGpsSimulator from '@/components/speedometer/DevGpsSimulator.vue';
import ProductionGauge from '@/components/speedometer/ProductionGauge.vue';
import TripControls from '@/components/speedometer/TripControls.vue';

const isDev = import.meta.env.DEV;
import { useAutoStop } from '@/composables/useAutoStop';
import { useBackgroundSync } from '@/composables/useBackgroundSync';
import { useGeolocation } from '@/composables/useGeolocation';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useSettingsStore } from '@/stores/settings';
import { useTripStore } from '@/stores/trip';
import { haversineDistance, metersToKm, metersToMiles } from '@/utils/distance';
import { mpsToDisplay } from '@/utils/units';

const KMH_TO_MPH = 0.621371;

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
const { speedKmh, speedMps, accuracy, coords, locatedAt, stopTracking } = useGeolocation();

// Duration update interval
let durationInterval: ReturnType<typeof setInterval> | null = null;

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

    // Load active trip if exists
    // WHY: User might have a stuck/forgotten trip from a previous session
    // WHY: Show it with "Stop Trip" button so they can properly end it
    // WHY: Don't auto-resume GPS - user should explicitly restart tracking if needed
    await tripStore.loadActiveTrip();

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

/**
 * Accumulate trip distance via Haversine on each GPS update.
 *
 * WHY: Watch locatedAt (timestamp) as trigger — fires exactly once per GPS callback.
 * WHY: Filter by accuracy to avoid GPS drift inflating distance.
 * WHY: Reject jumps > 200m per update (unrealistic GPS teleport).
 */
watch(locatedAt, () => {
    if (!tripStore.hasActiveTrip) {
return;
}

    const lat = coords.value.latitude;
    const lon = coords.value.longitude;
    const acc = coords.value.accuracy;

    if (lat === Infinity || lon === Infinity) {
return;
}

    if (acc && acc > 30) {
return;
}

    if (lastPosition.value) {
        const dist = haversineDistance(
            lastPosition.value.lat,
            lastPosition.value.lon,
            lat,
            lon,
        );

        if (dist < 200) {
            tripStore.stats.distance += dist;
        }
    }

    lastPosition.value = { lat, lon };
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

/**
 * WHY: Stats store max/avg in km/h (from speed logs). Convert to mph if needed.
 * Previous bug: mpsToDisplay treated km/h as m/s, inflating values by 3.6×.
 */
const maxSpeed = computed(() => {
    const kmh = tripStore.stats.maxSpeed;

    return unit.value === 'kmh' ? kmh : kmh * KMH_TO_MPH;
});
const avgSpeed = computed(() => {
    const kmh = tripStore.stats.averageSpeed;

    return unit.value === 'kmh' ? kmh : kmh * KMH_TO_MPH;
});

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
        
        // Start duration interval when trip becomes active
        if (!durationInterval) {
            durationInterval = setInterval(() => {
                if (!tripStore.hasActiveTrip || !tripStore.currentTrip?.started_at) {
                    return;
                }

                const startTime = new Date(tripStore.currentTrip.started_at).getTime();
                const now = Date.now();
                tripStore.stats.duration = Math.floor((now - startTime) / 1000);
            }, 1000);
        }
    } else {
        autoStop.stopMonitoring();
        lastPosition.value = null;
        
        // Clear duration interval when trip ends
        if (durationInterval) {
            clearInterval(durationInterval);
            durationInterval = null;
        }
    }
}, { immediate: true });

// ========================================================================
// Cleanup
// ========================================================================

onBeforeUnmount(() => {
    autoStop.stopMonitoring();
    lastPosition.value = null;
    
    // Clear duration interval
    if (durationInterval) {
        clearInterval(durationInterval);
        durationInterval = null;
    }
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

        <DevGpsSimulator v-if="isDev" />

        <div class="speedometer-page">
            <!-- Header -->
            <header class="velo-header">
                <div class="header-left">
                    <div class="velo-logo">Speed<span>Monitor</span></div>
                </div>
                <div class="status-indicator">
                    <div :class="['status-dot', gpsStatusClass]" />
                    <span>{{ gpsStatus }}</span>
                </div>
            </header>

        <!-- Main -->
        <main class="velo-main">
            <!-- Speed Limit Banner (Read-Only) -->
            <div class="limit-banner">
                <div class="limit-info">
                    <div class="limit-label">Speed Limit</div>
                    <div class="limit-value">
                        {{ currentSpeedLimit }} {{ unit === 'kmh' ? 'km/h' : 'mph' }}
                    </div>
                    <div class="limit-subtext">Diatur oleh supervisor</div>
                </div>
                <div class="unit-toggle">
                    <button
                        :class="{ active: unit === 'kmh' }"
                        @click="setUnit('kmh')"
                    >
                        km/h
                    </button>
                    <button
                        :class="{ active: unit === 'mph' }"
                        @click="setUnit('mph')"
                    >
                        mph
                    </button>
                </div>
            </div>

            <!-- Gauge -->
            <ProductionGauge
                :speed="currentSpeed"
                :speed-limit="currentSpeedLimit"
                :unit="unit"
            />

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card danger">
                    <div class="stat-label">Max Speed</div>
                    <div class="stat-value">{{ Math.round(maxSpeed) }}</div>
                    <div class="stat-unit">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </div>
                <div class="stat-card warn">
                    <div class="stat-label">Avg Speed</div>
                    <div class="stat-value">{{ Math.round(avgSpeed) }}</div>
                    <div class="stat-unit">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </div>
            </div>

            <!-- Trip Bar -->
            <div class="trip-bar">
                <div class="trip-item">
                    <div class="trip-val">{{ tripDistance.toFixed(2) }}</div>
                    <div class="trip-lbl">Distance ({{ unit === 'kmh' ? 'km' : 'mi' }})</div>
                </div>
                <div class="trip-divider" />
                <div class="trip-item">
                    <div class="trip-val">
                        {{ Math.floor(tripStore.stats.duration / 60).toString().padStart(2, '0') }}:{{ (tripStore.stats.duration % 60).toString().padStart(2, '0') }}
                    </div>
                    <div class="trip-lbl">Duration</div>
                </div>
                <div class="trip-divider" />
                <div class="trip-item">
                    <div class="trip-val">{{ tripStore.stats.violationCount }}</div>
                    <div class="trip-lbl">Violations</div>
                </div>
            </div>

            <!-- GPS Accuracy -->
            <div class="accuracy-row">
                <div class="accuracy-label">GPS Accuracy</div>
                <div class="accuracy-bar">
                    <div
                        class="accuracy-fill"
                        :style="{
                            width: accuracyPercentage + '%',
                            background: accuracyColor,
                        }"
                    />
                </div>
                <div class="accuracy-text">
                    {{ accuracy !== null ? Math.round(accuracy) + ' m' : '— m' }}
                </div>
            </div>

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
    padding: 16px 20px;
    gap: 16px;
}

.limit-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.limit-label {
    font-size: 0.75rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #4a5068;
}

.limit-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.8rem;
    letter-spacing: 2px;
    color: #00e5ff;
    text-shadow: 0 0 10px rgba(0, 229, 255, 0.3);
}

.limit-subtext {
    font-size: 0.7rem;
    color: #4a5068;
    font-style: italic;
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
