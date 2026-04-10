/**
 * IconLoader - Loading spinner icon.
 *
 * SVG icon component following SafeTrack design system standards.
 * Animated rotating loader, 2px stroke, currentColor, accepts size prop.
 */
<script setup lang="ts">
interface Props {
    /** Icon size in pixels */
    size?: number;
}

withDefaults(defineProps<Props>(), {
    size: 24,
});
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="animate-spin"
    >
        <path d="M21 12a9 9 0 1 1-6.219-8.56" />
    </svg>
</template>
