<!--
Dev GPS Simulator - Development-only floating panel for testing the speedometer
without physical movement. Injects mock position data through the shared
geolocation singleton so the full pipeline (smoothing, distance, trip save) is tested.

Only rendered when import.meta.env.DEV is true.
-->

<script setup lang="ts">
import { Bug, ChevronDown, ChevronUp } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import { useGeolocation } from '@/composables/useGeolocation';

const geo = useGeolocation();

const isExpanded = ref(false);
const speedSlider = ref(0);

const presets = [
    { label: 'Stop', speed: 0 },
    { label: 'Jalan', speed: 5 },
    { label: 'Kota', speed: 40 },
    { label: 'Tol', speed: 80 },
    { label: 'Ngebut', speed: 110 },
] as const;

const statusText = computed(() => {
    if (!geo.isMockMode.value) {
return 'OFF';
}

    if (!geo.isTracking.value) {
return 'READY';
}

    return `${geo.speedKmh.value} km/h`;
});

const statusColor = computed(() => {
    if (!geo.isMockMode.value) {
return '#4a5068';
}

    if (!geo.isTracking.value) {
return '#ffab00';
}

    return '#00e676';
});

function toggleMock(): void {
    if (geo.isMockMode.value) {
        geo.disableMockGps();
        speedSlider.value = 0;
    } else {
        geo.enableMockGps();
    }
}

function applyPreset(speed: number): void {
    speedSlider.value = speed;
    geo.setMockSpeed(speed);
}

watch(speedSlider, (val) => {
    geo.setMockSpeed(val);
});
</script>

<template>
    <div class="sim-panel" :class="{ expanded: isExpanded }">
        <!-- Collapsed bar -->
        <button class="sim-header" @click="isExpanded = !isExpanded">
            <div class="sim-header-left">
                <Bug :size="14" />
                <span class="sim-title">GPS Sim</span>
                <span class="sim-badge" :style="{ background: statusColor }">
                    {{ statusText }}
                </span>
            </div>
            <component :is="isExpanded ? ChevronDown : ChevronUp" :size="14" />
        </button>

        <!-- Expanded content -->
        <div v-if="isExpanded" class="sim-body">
            <!-- Mock toggle -->
            <div class="sim-row">
                <span class="sim-label">Mock GPS</span>
                <button
                    class="sim-toggle"
                    :class="{ active: geo.isMockMode.value }"
                    @click="toggleMock"
                >
                    {{ geo.isMockMode.value ? 'ON' : 'OFF' }}
                </button>
            </div>

            <template v-if="geo.isMockMode.value">
                <!-- Speed slider -->
                <div class="sim-section">
                    <div class="sim-row">
                        <span class="sim-label">Speed</span>
                        <span class="sim-value">{{ speedSlider }} km/h</span>
                    </div>
                    <input
                        v-model.number="speedSlider"
                        type="range"
                        min="0"
                        max="150"
                        step="1"
                        class="sim-slider"
                    />
                </div>

                <!-- Presets -->
                <div class="sim-presets">
                    <button
                        v-for="p in presets"
                        :key="p.label"
                        class="sim-preset"
                        :class="{ active: speedSlider === p.speed }"
                        @click="applyPreset(p.speed)"
                    >
                        {{ p.label }}
                        <small>{{ p.speed }}</small>
                    </button>
                </div>

                <!-- Accuracy -->
                <div class="sim-section">
                    <div class="sim-row">
                        <span class="sim-label">Accuracy</span>
                        <span class="sim-value">{{ geo.mockAccuracy.value }}m</span>
                    </div>
                    <input
                        :value="geo.mockAccuracy.value"
                        type="range"
                        min="1"
                        max="50"
                        step="1"
                        class="sim-slider"
                        @input="geo.setMockAccuracy(Number(($event.target as HTMLInputElement).value))"
                    />
                </div>

                <!-- Live readout -->
                <div class="sim-readout">
                    <div class="readout-item">
                        <span class="readout-label">Smoothed</span>
                        <span class="readout-value">{{ geo.speedKmh.value }} km/h</span>
                    </div>
                    <div class="readout-item">
                        <span class="readout-label">Raw m/s</span>
                        <span class="readout-value">{{ geo.speedMps.value.toFixed(2) }}</span>
                    </div>
                    <div class="readout-item">
                        <span class="readout-label">Tracking</span>
                        <span class="readout-value">{{ geo.isTracking.value ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </template>

            <p v-else class="sim-hint">
                Enable mock mode, then click "Mulai Perjalanan" to test.
            </p>
        </div>
    </div>
</template>

<style scoped>
.sim-panel {
    position: fixed;
    bottom: 16px;
    left: 16px;
    z-index: 9999;
    width: 240px;
    background: rgba(17, 19, 24, 0.95);
    border: 1px solid #1e2230;
    border-radius: 12px;
    backdrop-filter: blur(12px);
    font-family: 'Barlow', sans-serif;
    font-size: 0.75rem;
    color: #e8eaf0;
    overflow: hidden;
    transition: box-shadow 0.3s;
}

.sim-panel.expanded {
    box-shadow: 0 0 20px rgba(0, 229, 255, 0.1);
}

.sim-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: transparent;
    border: none;
    color: #e8eaf0;
    cursor: pointer;
    font-size: 0.75rem;
}

.sim-header:hover {
    background: rgba(255, 255, 255, 0.03);
}

.sim-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.sim-title {
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #00e5ff;
}

.sim-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.65rem;
    color: #000;
    font-weight: 700;
}

.sim-body {
    padding: 0 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sim-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sim-label {
    font-size: 0.7rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #4a5068;
}

.sim-value {
    font-family: 'Share Tech Mono', monospace;
    color: #00e5ff;
}

.sim-toggle {
    padding: 4px 12px;
    border-radius: 6px;
    border: 1px solid #1e2230;
    background: #0a0c0f;
    color: #4a5068;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.2s;
}

.sim-toggle.active {
    background: #00e5ff;
    border-color: #00e5ff;
    color: #000;
}

.sim-section {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sim-slider {
    width: 100%;
    height: 4px;
    appearance: none;
    background: #1e2230;
    border-radius: 2px;
    outline: none;
    cursor: pointer;
}

.sim-slider::-webkit-slider-thumb {
    appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #00e5ff;
    box-shadow: 0 0 6px rgba(0, 229, 255, 0.4);
    cursor: pointer;
}

.sim-presets {
    display: flex;
    gap: 4px;
}

.sim-preset {
    flex: 1;
    padding: 6px 2px;
    border-radius: 6px;
    border: 1px solid #1e2230;
    background: #0a0c0f;
    color: #4a5068;
    font-size: 0.6rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    line-height: 1;
}

.sim-preset small {
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.55rem;
    color: #4a5068;
}

.sim-preset:hover {
    border-color: #00e5ff;
    color: #e8eaf0;
}

.sim-preset.active {
    border-color: #00e5ff;
    background: rgba(0, 229, 255, 0.1);
    color: #00e5ff;
}

.sim-preset.active small {
    color: #00e5ff;
}

.sim-readout {
    padding: 8px 10px;
    background: #0a0c0f;
    border: 1px solid #1e2230;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.readout-item {
    display: flex;
    justify-content: space-between;
}

.readout-label {
    font-size: 0.6rem;
    color: #4a5068;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.readout-value {
    font-family: 'Share Tech Mono', monospace;
    font-size: 0.65rem;
    color: #e8eaf0;
}

.sim-hint {
    font-size: 0.65rem;
    color: #4a5068;
    line-height: 1.5;
    padding: 8px;
    background: #0a0c0f;
    border-radius: 8px;
    border: 1px dashed #1e2230;
    text-align: center;
}
</style>
