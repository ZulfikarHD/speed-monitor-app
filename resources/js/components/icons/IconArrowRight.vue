<script setup lang="ts">
/**
 * Arrow Right Icon Component
 *
 * SVG icon for forward navigation.
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
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
    </svg>
</template>
