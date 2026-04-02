<!--
SpeedGauge Component

Circular speedometer gauge displaying real-time speed with color-coded
visual feedback. Integrates with geolocation composable for live updates
and settings store for speed limit configuration.

Features:
- 270° circular arc gauge
- Color-coded zones (green: safe, yellow: warning, red: violation)
- Speed limit marker
- Smooth animations using motion-v
- Responsive design (mobile-first)
- Full accessibility support

Usage Example:
  <SpeedGauge :speed="speedKmh" />

Props:
- speed: Current speed in km/h
- speedLimit: Speed limit threshold (default: 60)
- maxSpeed: Maximum gauge range (default: 120)
- size: Gauge size 'sm' | 'md' | 'lg' (default: 'md')
-->

<script setup lang="ts">
import { computed, ref } from 'vue';

import { useSettingsStore } from '@/stores/settings';
import type { SpeedGaugeProps } from '@/types/speedometer';

// ========================================================================
// Props
// ========================================================================

/**
 * Component props with defaults.
 *
 * WHY: Provide sensible defaults for optional props.
 * WHY: speedLimit fallback to 60 km/h matches Indonesia speed limits.
 * WHY: maxSpeed at 120 km/h covers typical speedometer range.
 */
const props = withDefaults(defineProps<SpeedGaugeProps>(), {
    speed: 0,
    speedLimit: 60,
    maxSpeed: 120,
    size: 'md',
});

// ========================================================================
// Store Integration
// ========================================================================

const settingsStore = useSettingsStore();

/**
 * Effective speed limit from either props or settings store.
 *
 * WHY: Props take precedence for component flexibility.
 * WHY: Fallback to settings store for global configuration.
 */
const effectiveSpeedLimit = computed(() => {
    return props.speedLimit ?? settingsStore.settings.speed_limit;
});

// ========================================================================
// SVG Gauge Configuration
// ========================================================================

const SVG_SIZE = 200;
const SVG_CENTER_X = SVG_SIZE / 2;
const SVG_CENTER_Y = SVG_SIZE / 2;
const ARC_RADIUS = 80;
const ARC_STROKE_WIDTH = 16;

/**
 * Gauge arc angles in degrees.
 *
 * WHY: 270° arc (from -135° to +135°) provides better visibility than full circle.
 * WHY: Gap at bottom allows for better speed text placement.
 */
const GAUGE_START_ANGLE = -135;
const GAUGE_END_ANGLE = 135;
const GAUGE_TOTAL_ANGLE = GAUGE_END_ANGLE - GAUGE_START_ANGLE;

// ========================================================================
// SVG Path Calculations
// ========================================================================

/**
 * Convert polar coordinates to cartesian coordinates.
 *
 * WHY: SVG paths use cartesian (x, y) coordinates.
 * WHY: Speed data is conceptually radial (angle-based).
 *
 * @param centerX - X coordinate of arc center
 * @param centerY - Y coordinate of arc center
 * @param radius - Arc radius
 * @param angleInDegrees - Angle in degrees (0° = top, clockwise)
 * @returns Cartesian coordinates {x, y}
 */
function polarToCartesian(
    centerX: number,
    centerY: number,
    radius: number,
    angleInDegrees: number,
): { x: number; y: number } {
    const angleInRadians = ((angleInDegrees - 90) * Math.PI) / 180.0;

    return {
        x: centerX + radius * Math.cos(angleInRadians),
        y: centerY + radius * Math.sin(angleInRadians),
    };
}

/**
 * Generate SVG path string for circular arc.
 *
 * WHY: SVG `<path>` element requires specific string format.
 * WHY: Arc calculation allows dynamic gauge fill based on speed.
 *
 * @param x - Center X coordinate
 * @param y - Center Y coordinate
 * @param radius - Arc radius
 * @param startAngle - Starting angle in degrees
 * @param endAngle - Ending angle in degrees
 * @returns SVG path string
 */
function describeArc(
    x: number,
    y: number,
    radius: number,
    startAngle: number,
    endAngle: number,
): string {
    const start = polarToCartesian(x, y, radius, endAngle);
    const end = polarToCartesian(x, y, radius, startAngle);

    /**
     * WHY: largeArcFlag determines which arc to draw (short or long way around).
     * WHY: Use large arc when angle > 180° to ensure correct rendering.
     */
    const largeArcFlag = endAngle - startAngle <= 180 ? '0' : '1';

    return [
        'M',
        start.x,
        start.y,
        'A',
        radius,
        radius,
        0,
        largeArcFlag,
        0,
        end.x,
        end.y,
    ].join(' ');
}

// ========================================================================
// Computed Properties
// ========================================================================

/**
 * Current speed as percentage of maximum speed.
 *
 * WHY: Percentage simplifies arc angle calculation.
 * WHY: Clamp to 100% prevents gauge overflow.
 */
const speedPercentage = computed(() => {
    return Math.min((props.speed / props.maxSpeed) * 100, 100);
});

/**
 * Speed limit as percentage of maximum speed.
 *
 * WHY: Used to position speed limit marker on gauge.
 */
const speedLimitPercentage = computed(() => {
    return (effectiveSpeedLimit.value / props.maxSpeed) * 100;
});

/**
 * Current angle for speed indicator on gauge.
 *
 * WHY: Convert speed percentage to angle within gauge range.
 */
const currentSpeedAngle = computed(() => {
    return (
        GAUGE_START_ANGLE + (speedPercentage.value / 100) * GAUGE_TOTAL_ANGLE
    );
});

/**
 * Angle for speed limit marker.
 *
 * WHY: Position marker at configured speed limit threshold.
 */
const speedLimitAngle = computed(() => {
    return (
        GAUGE_START_ANGLE +
        (speedLimitPercentage.value / 100) * GAUGE_TOTAL_ANGLE
    );
});

/**
 * SVG path for background arc (full gauge range).
 *
 * WHY: Gray background shows full speed range available.
 */
const backgroundArcPath = computed(() => {
    return describeArc(
        SVG_CENTER_X,
        SVG_CENTER_Y,
        ARC_RADIUS,
        GAUGE_START_ANGLE,
        GAUGE_END_ANGLE,
    );
});

/**
 * SVG path for foreground arc (current speed).
 *
 * WHY: Colored arc fills from start to current speed.
 * WHY: Dynamic path updates as speed changes.
 */
const foregroundArcPath = computed(() => {
    return describeArc(
        SVG_CENTER_X,
        SVG_CENTER_Y,
        ARC_RADIUS,
        GAUGE_START_ANGLE,
        currentSpeedAngle.value,
    );
});

/**
 * Position of speed limit marker.
 *
 * WHY: Marker placed on arc at speed limit percentage.
 */
const speedLimitMarkerPosition = computed(() => {
    return polarToCartesian(
        SVG_CENTER_X,
        SVG_CENTER_Y,
        ARC_RADIUS,
        speedLimitAngle.value,
    );
});

/**
 * Gauge color based on current speed relative to limit.
 *
 * WHY: Green = safe (0-85% of limit).
 * WHY: Yellow = warning (85-100% of limit).
 * WHY: Red = violation (>100% of limit).
 */
const gaugeColor = computed(() => {
    if (props.speed > effectiveSpeedLimit.value) {
        return {
            stroke: 'rgb(239 68 68)', // red-500
            text: 'text-red-500',
        };
    }

    if (props.speed > effectiveSpeedLimit.value * 0.85) {
        return {
            stroke: 'rgb(234 179 8)', // yellow-500
            text: 'text-yellow-500',
        };
    }

    return {
        stroke: 'rgb(34 197 94)', // green-500
        text: 'text-green-500',
    };
});

/**
 * Display speed rounded to whole number.
 *
 * WHY: Whole numbers easier to read at a glance.
 * WHY: Decimal places add visual noise.
 */
const displaySpeed = computed(() => {
    return Math.round(props.speed);
});

/**
 * Responsive size classes based on size prop.
 *
 * WHY: Predefined sizes ensure consistent appearance.
 * WHY: Responsive breakpoints optimize for different screens.
 */
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'w-48 h-48';
        case 'md':
            return 'w-64 h-64 md:w-80 md:h-80';
        case 'lg':
            return 'w-80 h-80 md:w-96 md:h-96';
        default:
            return 'w-64 h-64 md:w-80 md:h-80';
    }
});

/**
 * Status text for screen readers.
 *
 * WHY: Provide context-aware status for accessibility.
 */
const statusText = computed(() => {
    if (props.speed > effectiveSpeedLimit.value) {
        return 'Peringatan: Melebihi batas kecepatan';
    }

    if (props.speed > effectiveSpeedLimit.value * 0.85) {
        return 'Perhatian: Mendekati batas kecepatan';
    }

    return 'Kecepatan normal';
});

// ========================================================================
// Violation Flash Animation
// ========================================================================

/**
 * Flash animation state for violation alerts.
 *
 * WHY: Reactive state triggers CSS animation via class binding.
 * WHY: Automatically resets after animation completes (500ms).
 */
const isFlashing = ref<boolean>(false);

/**
 * Trigger red flash animation for speed violation alert.
 *
 * Activates flash animation for 500ms, then automatically resets state.
 * Called by parent component when violation detected.
 *
 * WHY: Visual feedback reinforces audio/notification alerts.
 * WHY: 500ms duration provides noticeable alert without being intrusive.
 * WHY: Auto-reset prevents manual state management in parent.
 *
 * @example
 * ```ts
 * // Parent component usage
 * const gaugeRef = ref<InstanceType<typeof SpeedGauge> | null>(null);
 *
 * function onViolation() {
 *     gaugeRef.value?.triggerFlash();
 * }
 * ```
 */
function triggerFlash(): void {
    isFlashing.value = true;

    setTimeout(() => {
        isFlashing.value = false;
    }, 500);
}

/**
 * Expose triggerFlash method to parent components.
 *
 * WHY: Allows parent to trigger flash animation imperatively.
 * WHY: defineExpose required for composition API <script setup> syntax.
 */
defineExpose({
    triggerFlash,
});
</script>

<template>
    <!-- ================================================================ -->
    <!-- Outer Container with Accessibility -->
    <!-- ================================================================ -->
    <div
        :class="[
            'relative flex items-center justify-center',
            sizeClasses,
            { 'violation-flash': isFlashing },
        ]"
        role="img"
        :aria-label="`Kecepatan saat ini: ${displaySpeed} kilometer per jam`"
        :aria-live="speed > effectiveSpeedLimit ? 'assertive' : 'polite'"
    >
        <!-- ============================================================ -->
        <!-- SVG Gauge -->
        <!-- ============================================================ -->
        <svg
            :viewBox="`0 0 ${SVG_SIZE} ${SVG_SIZE}`"
            class="h-full w-full"
            aria-hidden="true"
        >
            <!-- Background Arc (Gray) -->
            <path
                :d="backgroundArcPath"
                :stroke-width="ARC_STROKE_WIDTH"
                stroke="rgb(229 231 235)"
                stroke-linecap="round"
                fill="none"
                class="transition-all duration-300"
            />

            <!-- Foreground Arc (Color-coded) -->
            <path
                :d="foregroundArcPath"
                :stroke-width="ARC_STROKE_WIDTH"
                :stroke="gaugeColor.stroke"
                stroke-linecap="round"
                fill="none"
                class="transition-all duration-500 ease-in-out"
            />

            <!-- Speed Limit Marker -->
            <circle
                :cx="speedLimitMarkerPosition.x"
                :cy="speedLimitMarkerPosition.y"
                r="6"
                fill="rgb(107 114 128)"
                class="drop-shadow-md"
            />

            <!-- Speed Limit Tick Mark -->
            <line
                :x1="speedLimitMarkerPosition.x"
                :y1="speedLimitMarkerPosition.y - 10"
                :x2="speedLimitMarkerPosition.x"
                :y2="speedLimitMarkerPosition.y + 10"
                stroke="rgb(107 114 128)"
                stroke-width="2"
            />
        </svg>

        <!-- ============================================================ -->
        <!-- Central Speed Display -->
        <!-- ============================================================ -->
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <!-- Speed Value -->
            <div
                :class="[
                    'text-6xl font-bold tabular-nums transition-colors duration-500 md:text-7xl lg:text-8xl',
                    gaugeColor.text,
                ]"
            >
                {{ displaySpeed }}
            </div>

            <!-- Unit Label -->
            <div class="mt-1 text-sm text-gray-500 md:text-base lg:text-lg">
                km/h
            </div>

            <!-- Speed Limit Label -->
            <div
                class="mt-2 flex items-center gap-1 text-xs text-gray-400 md:text-sm"
            >
                <svg
                    class="h-3 w-3 md:h-4 md:w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <span>Batas: {{ effectiveSpeedLimit }} km/h</span>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Screen Reader Fallback -->
        <!-- ============================================================ -->
        <span class="sr-only">
            Kecepatan: {{ displaySpeed }} km/h. Batas kecepatan:
            {{ effectiveSpeedLimit }} km/h. Status: {{ statusText }}.
        </span>
    </div>
</template>

<style scoped>
/**
 * Screen reader only utility class.
 *
 * WHY: Hide content visually but keep it accessible to screen readers.
 */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

/**
 * Violation flash animation keyframes.
 *
 * Creates attention-grabbing red pulse effect with scale transformation.
 * Animation uses opacity and scale variations to create pulsing effect.
 *
 * WHY: 0%, 100% at opacity 1 ensures smooth start/end.
 * WHY: 25%, 75% at opacity 0.5 creates pulsing effect.
 * WHY: 50% at opacity 0.7 adds middle pulse variation.
 * WHY: Scale 1.05 provides subtle "pop" without being jarring.
 */
@keyframes violation-flash {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    25%,
    75% {
        opacity: 0.5;
        transform: scale(1.05);
    }

    50% {
        opacity: 0.7;
        transform: scale(1.02);
    }
}

/**
 * Violation flash animation class.
 *
 * Applied to gauge container when isFlashing is true.
 *
 * WHY: 500ms duration provides noticeable alert without being intrusive.
 * WHY: ease-in-out timing creates smooth acceleration and deceleration.
 * WHY: Bound to isFlashing reactive state for automatic triggering.
 */
.violation-flash {
    animation: violation-flash 500ms ease-in-out;
}
</style>
