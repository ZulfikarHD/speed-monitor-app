<script setup lang="ts">
/**
 * Zap/Lightning Icon Component
 *
 * SVG icon for speed/energy representation.
 * Follows design system standards with currentColor and size prop.
 */

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
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
    </svg>
</template>
