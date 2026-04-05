<!--
Production Canvas Gauge with Store Integration

Professional circular gauge with tick marks, labels, and glow effects.
Integrates with Settings Store for speed limit configuration.
Responsive sizing support for different viewport widths.
Supports light/dark mode with theme-aware colors.
-->

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Props {
    speed: number;
    speedLimit: number;
    unit: 'kmh' | 'mph';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrame: number | null = null;

const sizes = {
    sm: { width: 240, height: 240 },
    md: { width: 300, height: 300 },
    lg: { width: 360, height: 360 },
};

const gaugeSize = computed(() => sizes[props.size] || sizes.md);

const maxSpeedDisplay = () => (props.unit === 'kmh' ? 200 : 125);

/**
 * Detect current theme (light/dark mode).
 * 
 * Checks for 'dark' class on document root element.
 */
const isDarkMode = ref(true);

function updateTheme() {
    isDarkMode.value = document.documentElement.classList.contains('dark');
}

/**
 * Get theme-aware colors for canvas drawing.
 */
const themeColors = computed(() => {
    if (isDarkMode.value) {
        return {
            bgArc: '#1e2230',
            tickMark: '#2a3048',
            tickLabel: '#4a5068',
        };
    } else {
        return {
            bgArc: '#d4d4d8', // zinc-300
            tickMark: '#a1a1aa', // zinc-400
            tickLabel: '#71717a', // zinc-500
        };
    }
});

function drawGauge() {
    const canvas = canvasRef.value;

    if (!canvas) {
return;
}

    const ctx = canvas.getContext('2d');

    if (!ctx) {
return;
}

    const W = canvas.width;
    const H = canvas.height;
    const cx = W / 2;
    const cy = H / 2;
    const R = W * 0.42;

    ctx.clearRect(0, 0, W, H);

    const ratio = Math.min(1, props.speed / maxSpeedDisplay());
    const startAngle = Math.PI * 0.75;
    const endAngle = Math.PI * 2.25;
    const arcRange = endAngle - startAngle;
    const colors = themeColors.value;

    // BG arc
    ctx.beginPath();
    ctx.arc(cx, cy, R, startAngle, endAngle);
    ctx.strokeStyle = colors.bgArc;
    ctx.lineWidth = 14;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Speed arc
    const speedEnd = startAngle + arcRange * ratio;
    const over = props.speed > props.speedLimit;
    const grad = ctx.createLinearGradient(cx - R, cy, cx + R, cy);
    grad.addColorStop(0, over ? '#ff3d57' : '#00e5ff');
    grad.addColorStop(1, over ? '#ff8a80' : '#00bcd4');

    ctx.beginPath();
    ctx.arc(cx, cy, R, startAngle, speedEnd);
    ctx.strokeStyle = grad;
    ctx.lineWidth = 14;
    ctx.lineCap = 'round';
    ctx.shadowColor = over ? '#ff3d57' : '#00e5ff';
    ctx.shadowBlur = 18;
    ctx.stroke();
    ctx.shadowBlur = 0;

    // Limit marker
    const limitRatio = Math.min(1, props.speedLimit / maxSpeedDisplay());
    const limitAngle = startAngle + arcRange * limitRatio;
    const lx = cx + Math.cos(limitAngle) * R;
    const ly = cy + Math.sin(limitAngle) * R;

    ctx.beginPath();
    ctx.arc(lx, ly, 5, 0, Math.PI * 2);
    ctx.fillStyle = '#ffab00';
    ctx.shadowColor = '#ffab00';
    ctx.shadowBlur = 10;
    ctx.fill();
    ctx.shadowBlur = 0;

    // Tick marks
    const ticks = 10;

    for (let i = 0; i <= ticks; i++) {
        const a = startAngle + (arcRange / ticks) * i;
        const inner = R - 22;
        const outer = R - 10;

        ctx.beginPath();
        ctx.moveTo(cx + Math.cos(a) * inner, cy + Math.sin(a) * inner);
        ctx.lineTo(cx + Math.cos(a) * outer, cy + Math.sin(a) * outer);
        ctx.strokeStyle = colors.tickMark;
        ctx.lineWidth = 2;
        ctx.stroke();

        // Labels
        const labelR = R - 34;
        const spVal = Math.round((maxSpeedDisplay() / ticks) * i);
        ctx.fillStyle = colors.tickLabel;
        ctx.font = '500 9px Barlow, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(
            String(spVal),
            cx + Math.cos(a) * labelR,
            cy + Math.sin(a) * labelR,
        );
    }

    // Center dot
    ctx.beginPath();
    ctx.arc(cx, cy, 6, 0, Math.PI * 2);
    ctx.fillStyle = over ? '#ff3d57' : '#00e5ff';
    ctx.shadowColor = over ? '#ff3d57' : '#00e5ff';
    ctx.shadowBlur = 12;
    ctx.fill();
    ctx.shadowBlur = 0;
}

function animate() {
    drawGauge();
    animationFrame = requestAnimationFrame(animate);
}

let themeObserver: MutationObserver | null = null;

onMounted(() => {
    updateTheme();
    animate();
    
    // Watch for theme changes via dark class on html element
    themeObserver = new MutationObserver(() => {
        updateTheme();
        drawGauge();
    });
    
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onUnmounted(() => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
    
    if (themeObserver) {
        themeObserver.disconnect();
    }
});

watch([() => props.speed, () => props.speedLimit, () => props.unit], () => {
    drawGauge();
});
</script>

<template>
    <div
        class="gauge-container relative"
        :class="{
            'w-60 h-60': size === 'sm',
            'w-[300px] h-[300px]': size === 'md',
            'w-[360px] h-[360px]': size === 'lg',
        }"
    >
        <canvas
            ref="canvasRef"
            :width="gaugeSize.width"
            :height="gaugeSize.height"
            class="gauge-canvas absolute inset-0"
        />
        <div class="gauge-overlay absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <div
                class="speed-display font-[Bebas_Neue] text-[5.5rem] leading-none transition-colors duration-300"
                :class="
                    speed > speedLimit
                        ? 'text-[#ff3d57] [text-shadow:0_0_20px_rgba(255,61,87,0.5)]'
                        : 'text-zinc-800 dark:text-[#e8eaf0] [text-shadow:0_0_30px_rgba(39,39,42,0.2)] dark:[text-shadow:0_0_30px_rgba(232,234,240,0.2)]'
                "
            >
                {{ Math.round(speed) }}
            </div>
            <div class="unit-label text-xs tracking-[3px] uppercase text-zinc-500 dark:text-[#4a5068] -mt-1">
                {{ unit === 'kmh' ? 'km/h' : 'mph' }}
            </div>
            <div
                v-if="speed > speedLimit"
                class="warning-text mt-1.5 text-[0.65rem] tracking-[2px] uppercase text-[#ff3d57] animate-blink"
            >
                ⚠ OVER LIMIT
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.animate-blink {
    animation: blink 0.8s infinite;
}
</style>
