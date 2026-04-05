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
    <div 
        class="sim-panel fixed bottom-4 left-4 z-[9999] w-60 bg-white/95 dark:bg-[rgba(17,19,24,0.95)] border border-zinc-200 dark:border-[#1e2230] rounded-xl backdrop-blur-xl font-[Barlow] text-xs text-zinc-900 dark:text-[#e8eaf0] overflow-hidden transition-shadow duration-300"
        :class="{ 'shadow-lg shadow-cyan-200/30 dark:shadow-[0_0_20px_rgba(0,229,255,0.1)]': isExpanded }"
    >
        <!-- Collapsed bar -->
        <button 
            class="sim-header w-full flex items-center justify-between px-3.5 py-2.5 bg-transparent border-none text-zinc-900 dark:text-[#e8eaf0] cursor-pointer text-xs hover:bg-zinc-100/30 dark:hover:bg-white/3"
            @click="isExpanded = !isExpanded"
        >
            <div class="sim-header-left flex items-center gap-2">
                <Bug :size="14" />
                <span class="sim-title font-semibold tracking-wider uppercase text-cyan-600 dark:text-[#00e5ff]">GPS Sim</span>
                <span 
                    class="sim-badge px-1.5 py-0.5 rounded font-[Share_Tech_Mono] text-[0.65rem] text-black font-bold"
                    :style="{ background: statusColor }"
                >
                    {{ statusText }}
                </span>
            </div>
            <component :is="isExpanded ? ChevronDown : ChevronUp" :size="14" />
        </button>

        <!-- Expanded content -->
        <div v-if="isExpanded" class="sim-body px-3.5 pb-3.5 flex flex-col gap-3">
            <!-- Mock toggle -->
            <div class="sim-row flex items-center justify-between">
                <span class="sim-label text-[0.7rem] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068]">Mock GPS</span>
                <button
                    class="sim-toggle px-3 py-1 rounded-md border border-zinc-300 dark:border-[#1e2230] bg-white dark:bg-[#0a0c0f] text-zinc-500 dark:text-[#4a5068] text-[0.7rem] font-semibold tracking-wider cursor-pointer transition-all duration-200"
                    :class="{ 
                        'bg-cyan-600 dark:bg-[#00e5ff] border-cyan-600 dark:border-[#00e5ff] text-white dark:text-black': geo.isMockMode.value 
                    }"
                    @click="toggleMock"
                >
                    {{ geo.isMockMode.value ? 'ON' : 'OFF' }}
                </button>
            </div>

            <template v-if="geo.isMockMode.value">
                <!-- Speed slider -->
                <div class="sim-section flex flex-col gap-1.5">
                    <div class="sim-row flex items-center justify-between">
                        <span class="sim-label text-[0.7rem] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068]">Speed</span>
                        <span class="sim-value font-[Share_Tech_Mono] text-cyan-600 dark:text-[#00e5ff]">{{ speedSlider }} km/h</span>
                    </div>
                    <input
                        v-model.number="speedSlider"
                        type="range"
                        min="0"
                        max="150"
                        step="1"
                        class="sim-slider w-full h-1 appearance-none bg-zinc-300 dark:bg-[#1e2230] rounded-sm outline-none cursor-pointer"
                    />
                </div>

                <!-- Presets -->
                <div class="sim-presets flex gap-1">
                    <button
                        v-for="p in presets"
                        :key="p.label"
                        class="sim-preset flex-1 px-0.5 py-1.5 rounded-md border border-zinc-300 dark:border-[#1e2230] bg-white dark:bg-[#0a0c0f] text-zinc-500 dark:text-[#4a5068] text-[0.6rem] font-semibold text-center cursor-pointer transition-all duration-150 flex flex-col items-center gap-0.5 leading-none hover:border-cyan-600 dark:hover:border-[#00e5ff] hover:text-zinc-900 dark:hover:text-[#e8eaf0]"
                        :class="{ 
                            'border-cyan-600 dark:border-[#00e5ff] bg-cyan-50 dark:bg-[rgba(0,229,255,0.1)] text-cyan-600 dark:text-[#00e5ff]': speedSlider === p.speed 
                        }"
                        @click="applyPreset(p.speed)"
                    >
                        {{ p.label }}
                        <small 
                            class="font-[Share_Tech_Mono] text-[0.55rem]"
                            :class="speedSlider === p.speed ? 'text-cyan-600 dark:text-[#00e5ff]' : 'text-zinc-400 dark:text-[#4a5068]'"
                        >
                            {{ p.speed }}
                        </small>
                    </button>
                </div>

                <!-- Accuracy -->
                <div class="sim-section flex flex-col gap-1.5">
                    <div class="sim-row flex items-center justify-between">
                        <span class="sim-label text-[0.7rem] tracking-[1.5px] uppercase text-zinc-500 dark:text-[#4a5068]">Accuracy</span>
                        <span class="sim-value font-[Share_Tech_Mono] text-cyan-600 dark:text-[#00e5ff]">{{ geo.mockAccuracy.value }}m</span>
                    </div>
                    <input
                        :value="geo.mockAccuracy.value"
                        type="range"
                        min="1"
                        max="50"
                        step="1"
                        class="sim-slider w-full h-1 appearance-none bg-zinc-300 dark:bg-[#1e2230] rounded-sm outline-none cursor-pointer"
                        @input="geo.setMockAccuracy(Number(($event.target as HTMLInputElement).value))"
                    />
                </div>

                <!-- Live readout -->
                <div class="sim-readout p-2 px-2.5 bg-zinc-50 dark:bg-[#0a0c0f] border border-zinc-200 dark:border-[#1e2230] rounded-lg flex flex-col gap-1">
                    <div class="readout-item flex justify-between">
                        <span class="readout-label text-[0.6rem] text-zinc-500 dark:text-[#4a5068] uppercase tracking-wider">Smoothed</span>
                        <span class="readout-value font-[Share_Tech_Mono] text-[0.65rem] text-zinc-800 dark:text-[#e8eaf0]">{{ geo.speedKmh.value }} km/h</span>
                    </div>
                    <div class="readout-item flex justify-between">
                        <span class="readout-label text-[0.6rem] text-zinc-500 dark:text-[#4a5068] uppercase tracking-wider">Raw m/s</span>
                        <span class="readout-value font-[Share_Tech_Mono] text-[0.65rem] text-zinc-800 dark:text-[#e8eaf0]">{{ geo.speedMps.value.toFixed(2) }}</span>
                    </div>
                    <div class="readout-item flex justify-between">
                        <span class="readout-label text-[0.6rem] text-zinc-500 dark:text-[#4a5068] uppercase tracking-wider">Tracking</span>
                        <span class="readout-value font-[Share_Tech_Mono] text-[0.65rem] text-zinc-800 dark:text-[#e8eaf0]">{{ geo.isTracking.value ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </template>

            <p v-else class="sim-hint text-[0.65rem] text-zinc-500 dark:text-[#4a5068] leading-relaxed p-2 bg-zinc-50 dark:bg-[#0a0c0f] rounded-lg border border-dashed border-zinc-300 dark:border-[#1e2230] text-center">
                Enable mock mode, then click "Mulai Perjalanan" to test.
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Slider thumb styling - not easily replicated with Tailwind */
.sim-slider::-webkit-slider-thumb {
    appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #0891b2; /* cyan-600 for light mode */
    box-shadow: 0 0 6px rgba(8, 145, 178, 0.4);
    cursor: pointer;
}

.dark .sim-slider::-webkit-slider-thumb {
    background: #00e5ff;
    box-shadow: 0 0 6px rgba(0, 229, 255, 0.4);
}

.sim-slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border: none;
    border-radius: 50%;
    background: #0891b2;
    box-shadow: 0 0 6px rgba(8, 145, 178, 0.4);
    cursor: pointer;
}

.dark .sim-slider::-moz-range-thumb {
    background: #00e5ff;
    box-shadow: 0 0 6px rgba(0, 229, 255, 0.4);
}
</style>
