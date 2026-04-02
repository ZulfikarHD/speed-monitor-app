<!--
SpeedGauge Demo Page

Testing and demonstration page for the SpeedGauge component.
Provides interactive controls to simulate different speed scenarios,
test color zones, and verify responsive behavior.

Usage: Navigate to this page during development to test the component.
-->

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';

import SpeedGauge from '@/components/speedometer/SpeedGauge.vue';
import type { SpeedGaugeProps } from '@/types/speedometer';

// ========================================================================
// State
// ========================================================================

/** Current simulated speed */
const currentSpeed = ref<number>(0);

/** Speed limit for testing */
const speedLimit = ref<number>(60);

/** Maximum speed for gauge */
const maxSpeed = ref<number>(120);

/** Selected gauge size */
const selectedSize = ref<SpeedGaugeProps['size']>('md');

/** Auto-increment enabled */
const autoIncrementEnabled = ref<boolean>(false);

/** Auto-increment interval ID */
let autoIncrementInterval: ReturnType<typeof setInterval> | null = null;

// ========================================================================
// Computed
// ========================================================================

/** Color zone for current speed */
const currentZone = computed(() => {
    if (currentSpeed.value > speedLimit.value) {
        return {
            name: 'VIOLATION',
            color: 'text-red-600 bg-red-50',
            icon: '⛔',
        };
    }

    if (currentSpeed.value > speedLimit.value * 0.85) {
        return {
            name: 'WARNING',
            color: 'text-yellow-600 bg-yellow-50',
            icon: '⚠️',
        };
    }

    return {
        name: 'SAFE',
        color: 'text-green-600 bg-green-50',
        icon: '✅',
    };
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Set speed to predefined test value.
 *
 * WHY: Quick access to common test scenarios.
 */
function setSpeed(speed: number): void {
    currentSpeed.value = Math.max(0, Math.min(speed, maxSpeed.value));
}

/**
 * Increment speed by specified amount.
 */
function incrementSpeed(amount: number): void {
    currentSpeed.value = Math.max(
        0,
        Math.min(currentSpeed.value + amount, maxSpeed.value),
    );
}

/**
 * Toggle auto-increment feature.
 *
 * WHY: Simulate continuous speed changes for animation testing.
 */
function toggleAutoIncrement(): void {
    autoIncrementEnabled.value = !autoIncrementEnabled.value;

    if (autoIncrementEnabled.value) {
        // Increment by 5 km/h every 500ms
        autoIncrementInterval = setInterval(() => {
            if (currentSpeed.value >= maxSpeed.value) {
                currentSpeed.value = 0;
            } else {
                currentSpeed.value = Math.min(
                    currentSpeed.value + 5,
                    maxSpeed.value,
                );
            }
        }, 500);
    } else {
        if (autoIncrementInterval) {
            clearInterval(autoIncrementInterval);
            autoIncrementInterval = null;
        }
    }
}

/**
 * Reset all settings to defaults.
 */
function reset(): void {
    currentSpeed.value = 0;
    speedLimit.value = 60;
    maxSpeed.value = 120;
    selectedSize.value = 'md';

    if (autoIncrementEnabled.value) {
        toggleAutoIncrement();
    }
}

// ========================================================================
// Lifecycle
// ========================================================================

/**
 * Cleanup auto-increment interval on component unmount.
 *
 * WHY: Prevent memory leaks from running intervals.
 */
onBeforeUnmount(() => {
    if (autoIncrementInterval) {
        clearInterval(autoIncrementInterval);
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="mx-auto max-w-6xl">
            <!-- ======================================================== -->
            <!-- Header -->
            <!-- ======================================================== -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-gray-900 md:text-4xl">
                    SpeedGauge Component Demo
                </h1>
                <p class="text-gray-600">
                    Testing & demonstration page for speedometer gauge component
                </p>
            </div>

            <!-- ======================================================== -->
            <!-- Main Layout -->
            <!-- ======================================================== -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Column: Gauge Display -->
                <div class="rounded-lg bg-white p-8 shadow-lg">
                    <div class="flex flex-col items-center">
                        <!-- Gauge Component -->
                        <SpeedGauge
                            :speed="currentSpeed"
                            :speed-limit="speedLimit"
                            :max-speed="maxSpeed"
                            :size="selectedSize"
                        />

                        <!-- Status Badge -->
                        <div
                            :class="[
                                'mt-6 flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold',
                                currentZone.color,
                            ]"
                        >
                            <span class="text-xl">{{ currentZone.icon }}</span>
                            <span>{{ currentZone.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Controls -->
                <div class="space-y-6">
                    <!-- Speed Controls -->
                    <div class="rounded-lg bg-white p-6 shadow-lg">
                        <h2 class="mb-4 text-xl font-semibold text-gray-900">
                            Speed Controls
                        </h2>

                        <!-- Current Speed Display -->
                        <div class="mb-4">
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Current Speed: {{ currentSpeed }} km/h
                            </label>
                            <input
                                v-model.number="currentSpeed"
                                type="range"
                                :min="0"
                                :max="maxSpeed"
                                class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200"
                            />
                        </div>

                        <!-- Quick Speed Buttons -->
                        <div class="mb-4 grid grid-cols-4 gap-2">
                            <button
                                @click="setSpeed(0)"
                                class="rounded bg-gray-100 px-3 py-2 text-sm font-medium transition-colors hover:bg-gray-200"
                            >
                                0
                            </button>
                            <button
                                @click="setSpeed(30)"
                                class="rounded bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200"
                            >
                                30
                            </button>
                            <button
                                @click="setSpeed(55)"
                                class="rounded bg-yellow-100 px-3 py-2 text-sm font-medium text-yellow-700 transition-colors hover:bg-yellow-200"
                            >
                                55
                            </button>
                            <button
                                @click="setSpeed(75)"
                                class="rounded bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200"
                            >
                                75
                            </button>
                        </div>

                        <!-- Increment Buttons -->
                        <div class="mb-4 grid grid-cols-2 gap-2">
                            <button
                                @click="incrementSpeed(-10)"
                                class="rounded bg-gray-100 px-4 py-2 font-medium transition-colors hover:bg-gray-200"
                            >
                                -10 km/h
                            </button>
                            <button
                                @click="incrementSpeed(10)"
                                class="rounded bg-gray-100 px-4 py-2 font-medium transition-colors hover:bg-gray-200"
                            >
                                +10 km/h
                            </button>
                        </div>

                        <!-- Auto Increment Toggle -->
                        <button
                            @click="toggleAutoIncrement"
                            :class="[
                                'w-full rounded px-4 py-2 font-medium transition-colors',
                                autoIncrementEnabled
                                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            ]"
                        >
                            {{
                                autoIncrementEnabled
                                    ? '⏸️ Stop Auto Increment'
                                    : '▶️ Start Auto Increment'
                            }}
                        </button>
                    </div>

                    <!-- Configuration -->
                    <div class="rounded-lg bg-white p-6 shadow-lg">
                        <h2 class="mb-4 text-xl font-semibold text-gray-900">
                            Configuration
                        </h2>

                        <!-- Speed Limit -->
                        <div class="mb-4">
                            <label
                                for="speed-limit"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Speed Limit: {{ speedLimit }} km/h
                            </label>
                            <input
                                id="speed-limit"
                                v-model.number="speedLimit"
                                type="range"
                                min="40"
                                max="100"
                                step="5"
                                class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200"
                            />
                        </div>

                        <!-- Max Speed -->
                        <div class="mb-4">
                            <label
                                for="max-speed"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Max Speed: {{ maxSpeed }} km/h
                            </label>
                            <input
                                id="max-speed"
                                v-model.number="maxSpeed"
                                type="range"
                                min="80"
                                max="200"
                                step="10"
                                class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200"
                            />
                        </div>

                        <!-- Size Selection -->
                        <div class="mb-4">
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Gauge Size
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    @click="selectedSize = 'sm'"
                                    :class="[
                                        'rounded px-4 py-2 font-medium transition-colors',
                                        selectedSize === 'sm'
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 hover:bg-gray-200',
                                    ]"
                                >
                                    Small
                                </button>
                                <button
                                    @click="selectedSize = 'md'"
                                    :class="[
                                        'rounded px-4 py-2 font-medium transition-colors',
                                        selectedSize === 'md'
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 hover:bg-gray-200',
                                    ]"
                                >
                                    Medium
                                </button>
                                <button
                                    @click="selectedSize = 'lg'"
                                    :class="[
                                        'rounded px-4 py-2 font-medium transition-colors',
                                        selectedSize === 'lg'
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 hover:bg-gray-200',
                                    ]"
                                >
                                    Large
                                </button>
                            </div>
                        </div>

                        <!-- Reset Button -->
                        <button
                            @click="reset"
                            class="w-full rounded bg-gray-100 px-4 py-2 font-medium transition-colors hover:bg-gray-200"
                        >
                            🔄 Reset All
                        </button>
                    </div>

                    <!-- Testing Checklist -->
                    <div class="rounded-lg bg-white p-6 shadow-lg">
                        <h2 class="mb-4 text-xl font-semibold text-gray-900">
                            Testing Checklist
                        </h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Gauge displays 0 km/h on mount</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Speed updates smoothly (no jitter)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Green color at safe speeds</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Yellow color approaching limit</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Red color when exceeding limit</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Speed limit marker visible</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Responsive on all sizes</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Smooth animations (60fps)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
