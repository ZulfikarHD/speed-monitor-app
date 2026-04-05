<script setup lang="ts">
/**
 * Navigation Icon Component
 *
 * SVG icon for navigation/compass representation.
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
        <polygon points="3 11 22 2 13 21 11 13 3 11" />
    </svg>
</template>
