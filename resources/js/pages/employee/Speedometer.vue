<!--
SafeTrack - Production GPS Speedometer

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
import SuperuserLayout from '@/layouts/SuperuserLayout.vue';
import { useAuthStore } from '@/stores/auth';
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
const authStore = useAuthStore();

const LayoutComponent = computed(() => {
    const role = authStore.role;

    return role === 'superuser' || role === 'admin' ? SuperuserLayout : EmployeeLayout;
});
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

const selectedShift = ref<string>('shift_pagi');
const selectedVehicle = ref<string>('mobil');

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
    // WHY: Ensures speedometer uses superuser-configured limits, not defaults
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
return 'Berhenti';
}

    if (accuracy.value === null) {
return 'Mencari GPS…';
}

    if (accuracy.value > 50) {
return 'GPS Lemah';
}

    return 'GPS Aktif';
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
 * WHY: When superuser changes settings, all employees should immediately
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
    <component :is="LayoutComponent" title="Speedometer">
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

        <div class="speedometer-page bg-white dark:bg-[#0a0c0f] text-zinc-900 dark:text-[#e8eaf0]">
            <!-- Header -->
            <header class="velo-header w-full px-4 py-1.5 flex justify-between items-center border-b border-zinc-200 dark:border-[#1e2230] bg-white/95 dark:bg-[rgba(10,12,15,0.95)] backdrop-blur-[10px] sticky top-0 z-10">
                <div class="header-left flex items-center gap-3">
                    <div class="velo-logo font-[Bebas_Neue] text-lg tracking-[3px] text-cyan-600 dark:text-[#00e5ff] [text-shadow:0_0_20px_rgba(6,182,212,0.35)] dark:[text-shadow:0_0_20px_rgba(0,229,255,0.35)]">
                        Safe<span class="text-zinc-900 dark:text-[#e8eaf0]">Track</span>
                    </div>
                </div>
                <div class="status-indicator flex items-center gap-1.5 text-[10px] tracking-[1.5px] uppercase text-zinc-400 dark:text-[#4a5068]">
                    <div :class="['status-dot w-1.5 h-1.5 rounded-full bg-zinc-400 dark:bg-[#4a5068] transition-all duration-400', gpsStatusClass]" />
                    <span>{{ gpsStatus }}</span>
                </div>
            </header>

        <!-- Main (ultra-compact layout for mobile) -->
        <main class="velo-main w-full max-w-md md:max-w-lg lg:max-w-2xl px-2.5 py-1.5 pb-2 mx-auto flex flex-col items-center gap-1.5">
            <!-- Speed Limit + Unit Toggle (single compact row) -->
            <div class="limit-banner w-full flex items-center justify-between bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg px-3 py-1.5">
                <div class="limit-info flex items-center gap-2">
                    <div class="limit-label text-[9px] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068]">Batas</div>
                    <div class="limit-value font-[Bebas_Neue] text-xl tracking-[2px] text-cyan-600 dark:text-[#00e5ff] leading-none">
                        {{ currentSpeedLimit }} <span class="text-xs text-zinc-400 dark:text-[#4a5068]">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</span>
                    </div>
                </div>
                <div class="unit-toggle flex bg-white dark:bg-[#0a0c0f] border border-zinc-200 dark:border-[#1e2230] rounded-md overflow-hidden">
                    <button
                        :class="{
                            'bg-cyan-600 text-white font-semibold': unit === 'kmh',
                            'bg-transparent text-zinc-500 dark:text-[#4a5068]': unit !== 'kmh'
                        }"
                        class="px-2.5 py-1 border-none text-[10px] tracking-wider uppercase cursor-pointer transition-all duration-200"
                        @click="setUnit('kmh')"
                    >
                        km/h
                    </button>
                    <button
                        :class="{
                            'bg-cyan-600 text-white font-semibold': unit === 'mph',
                            'bg-transparent text-zinc-500 dark:text-[#4a5068]': unit !== 'mph'
                        }"
                        class="px-2.5 py-1 border-none text-[10px] tracking-wider uppercase cursor-pointer transition-all duration-200"
                        @click="setUnit('mph')"
                    >
                        mph
                    </button>
                </div>
            </div>

            <!-- Gauge (scales down on pre-trip to fit controls on screen) -->
            <div class="gauge-wrapper w-full flex items-center justify-center" :class="tripStore.hasActiveTrip ? 'gauge-active' : 'gauge-idle'">
                <ProductionGauge
                    :speed="currentSpeed"
                    :speed-limit="currentSpeedLimit"
                    :unit="unit"
                />
            </div>

            <!-- Stats Row (inline 3-col: Max | Avg | STD) -->
            <div class="stats-grid w-full grid grid-cols-3 gap-1.5">
                <div class="stat-card danger relative overflow-hidden bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg p-2 flex flex-col gap-0.5">
                    <div class="stat-label text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068]">Maks</div>
                    <div class="stat-value font-[Share_Tech_Mono] text-lg text-zinc-900 dark:text-[#e8eaf0] leading-none">{{ Math.round(maxSpeed) }}</div>
                    <div class="stat-unit text-[0.55rem] text-zinc-400 dark:text-[#4a5068]">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </div>
                <div class="stat-card warn relative overflow-hidden bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg p-2 flex flex-col gap-0.5">
                    <div class="stat-label text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068]">Rata2</div>
                    <div class="stat-value font-[Share_Tech_Mono] text-lg text-zinc-900 dark:text-[#e8eaf0] leading-none">{{ Math.round(avgSpeed) }}</div>
                    <div class="stat-unit text-[0.55rem] text-zinc-400 dark:text-[#4a5068]">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </div>
                <div class="stat-card relative overflow-hidden bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg p-2 flex flex-col gap-0.5">
                    <div class="stat-label text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068]">STD</div>
                    <div class="stat-value font-[Share_Tech_Mono] text-lg text-cyan-600 dark:text-[#00e5ff] leading-none">{{ currentSpeedLimit }}</div>
                    <div class="stat-unit text-[0.55rem] text-zinc-400 dark:text-[#4a5068]">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
                </div>
            </div>

            <!-- Trip Bar (compact inline) -->
            <div class="trip-bar w-full bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg px-3 py-2 flex justify-between items-center">
                <div class="trip-item text-center">
                    <div class="trip-val font-[Share_Tech_Mono] text-base text-cyan-600 dark:text-[#00e5ff] leading-none">{{ tripDistance.toFixed(2) }}</div>
                    <div class="trip-lbl text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068] mt-0.5">{{ unit === 'kmh' ? 'km' : 'mi' }}</div>
                </div>
                <div class="trip-divider w-px h-5 bg-zinc-300 dark:bg-[#1e2230]" />
                <div class="trip-item text-center">
                    <div class="trip-val font-[Share_Tech_Mono] text-base text-cyan-600 dark:text-[#00e5ff] leading-none">
                        {{ Math.floor(tripStore.stats.duration / 60).toString().padStart(2, '0') }}:{{ (tripStore.stats.duration % 60).toString().padStart(2, '0') }}
                    </div>
                    <div class="trip-lbl text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068] mt-0.5">Durasi</div>
                </div>
                <div class="trip-divider w-px h-5 bg-zinc-300 dark:bg-[#1e2230]" />
                <div class="trip-item text-center">
                    <div class="trip-val font-[Share_Tech_Mono] text-base text-cyan-600 dark:text-[#00e5ff] leading-none">{{ tripStore.stats.violationCount }}</div>
                    <div class="trip-lbl text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068] mt-0.5">Pelanggaran</div>
                </div>
            </div>

            <!-- GPS Accuracy (compact) -->
            <div class="accuracy-row w-full flex items-center gap-1.5 px-0.5">
                <div class="accuracy-label text-[9px] tracking-[1px] uppercase text-zinc-500 dark:text-[#4a5068] whitespace-nowrap">GPS</div>
                <div class="accuracy-bar flex-1 h-[2px] bg-zinc-300 dark:bg-[#1e2230] rounded-[2px] overflow-hidden">
                    <div
                        class="accuracy-fill h-full rounded-[2px] transition-all duration-500"
                        :style="{
                            width: accuracyPercentage + '%',
                            background: accuracyColor,
                        }"
                    />
                </div>
                <div class="accuracy-text font-[Share_Tech_Mono] text-[0.6rem] text-zinc-500 dark:text-[#4a5068] min-w-[38px] text-right">
                    {{ accuracy !== null ? Math.round(accuracy) + 'm' : '—' }}
                </div>
            </div>

            <!-- Shift & Vehicle Selection (combined single card, shown before trip starts) -->
            <div v-if="!tripStore.hasActiveTrip" class="w-full bg-zinc-100 dark:bg-[#111318] border border-zinc-200 dark:border-[#1e2230] rounded-lg p-2.5 space-y-2">
                <!-- Shift Type -->
                <div>
                    <div class="text-[9px] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068] mb-1.5">Shift</div>
                    <div class="grid grid-cols-2 gap-1">
                        <button
                            v-for="opt in [
                                { value: 'shift_pagi', label: 'Pagi' },
                                { value: 'shift_malam', label: 'Malam' },
                            ]"
                            :key="opt.value"
                            type="button"
                            class="rounded-md px-2 py-1.5 text-[11px] font-medium transition-all duration-200 text-center"
                            :class="selectedShift === opt.value
                                ? 'bg-cyan-600 text-white shadow-sm'
                                : 'bg-white dark:bg-[#0a0c0f] text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-[#1e2230]'"
                            @click="selectedShift = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Vehicle Type -->
                <div>
                    <div class="text-[9px] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068] mb-1.5">Kendaraan</div>
                    <div class="grid grid-cols-2 gap-1">
                        <button
                            v-for="opt in [
                                { value: 'mobil', label: 'Mobil' },
                                { value: 'motor', label: 'Motor' },
                            ]"
                            :key="opt.value"
                            type="button"
                            class="rounded-md px-2 py-1.5 text-[11px] font-medium transition-all duration-200 text-center"
                            :class="selectedVehicle === opt.value
                                ? 'bg-cyan-600 text-white shadow-sm'
                                : 'bg-white dark:bg-[#0a0c0f] text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-[#1e2230]'"
                            @click="selectedVehicle = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trip Controls -->
            <TripControls
                :shift-type="selectedShift"
                :vehicle-type="selectedVehicle"
            />
        </main>
        </div>
    </component>
</template>

<style scoped>
.speedometer-page {
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

/* Status dot animations */
.status-dot.active {
    background: #00e676 !important;
    box-shadow: 0 0 8px #00e676;
    animation: pulse 2s infinite;
}

.status-dot.warn {
    background: #ffab00 !important;
    box-shadow: 0 0 8px #ffab00;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* Stat card top indicator bars */
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(to right, rgb(6 182 212), rgb(59 130 246));
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

/* Gauge scaling — shrink before trip to fit start button on screen */
.gauge-wrapper {
    overflow: hidden;
}

.gauge-idle {
    max-height: 22vh;
}

.gauge-idle :deep(.gauge-container) {
    transform: scale(0.72);
    transform-origin: center center;
}

.gauge-active {
    max-height: 32vh;
}

.gauge-active :deep(.gauge-container) {
    transform: scale(0.85);
    transform-origin: center center;
}

@media (min-height: 750px) {
    .gauge-idle {
        max-height: 26vh;
    }

    .gauge-idle :deep(.gauge-container) {
        transform: scale(0.82);
    }

    .gauge-active {
        max-height: 36vh;
    }

    .gauge-active :deep(.gauge-container) {
        transform: scale(0.95);
    }
}

/* Short screen / landscape optimizations */
@media (max-height: 700px) {
    .velo-main {
        gap: 4px !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }
}

@media (orientation: landscape) and (max-height: 500px) {
    .velo-main {
        padding: 4px 10px 8px !important;
        gap: 3px !important;
    }

    .stats-grid,
    .trip-bar,
    .accuracy-row {
        transform: scale(0.9);
    }

    .limit-banner {
        padding: 4px 8px !important;
    }
}
</style>
