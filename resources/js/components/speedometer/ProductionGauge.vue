<!--
Production Canvas Gauge with Store Integration

Professional circular gauge with tick marks, labels, and glow effects.
Integrates with Settings Store for speed limit configuration.
-->

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';

interface Props {
    speed: number;
    speedLimit: number;
    unit: 'kmh' | 'mph';
}

const props = defineProps<Props>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrame: number | null = null;

const maxSpeedDisplay = () => (props.unit === 'kmh' ? 200 : 125);

function drawGauge() {
    const canvas = canvasRef.value;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

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

    // BG arc
    ctx.beginPath();
    ctx.arc(cx, cy, R, startAngle, endAngle);
    ctx.strokeStyle = '#1e2230';
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
        ctx.strokeStyle = '#2a3048';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Labels
        const labelR = R - 34;
        const spVal = Math.round((maxSpeedDisplay() / ticks) * i);
        ctx.fillStyle = '#4a5068';
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

onMounted(() => {
    animate();
});

onUnmounted(() => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
});

watch([() => props.speed, () => props.speedLimit, () => props.unit], () => {
    drawGauge();
});
</script>

<template>
    <div class="gauge-container">
        <canvas ref="canvasRef" width="300" height="300" class="gauge-canvas" />
        <div class="gauge-overlay">
            <div
                :class="['speed-display', { violation: speed > speedLimit }]"
            >
                {{ Math.round(speed) }}
            </div>
            <div class="unit-label">{{ unit === 'kmh' ? 'km/h' : 'mph' }}</div>
            <div
                v-if="speed > speedLimit"
                class="warning-text"
            >
                ⚠ OVER LIMIT
            </div>
        </div>
    </div>
</template>

<style scoped>
.gauge-container {
    position: relative;
    width: 300px;
    height: 300px;
}

.gauge-canvas {
    position: absolute;
    inset: 0;
}

.gauge-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.speed-display {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 5.5rem;
    line-height: 1;
    color: #e8eaf0;
    transition: color 0.3s;
    text-shadow: 0 0 30px rgba(232, 234, 240, 0.2);
}

.speed-display.violation {
    color: #ff3d57;
    text-shadow: 0 0 20px rgba(255, 61, 87, 0.5);
}

.unit-label {
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #4a5068;
    margin-top: -4px;
}

.warning-text {
    margin-top: 6px;
    font-size: 0.65rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #ff3d57;
    animation: blink 0.8s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
</style>
