<script setup lang="ts">
/**
 * Trend Down Icon Component
 *
 * SVG icon for negative trend indicators following design system standards.
 *
 * @example
 * <IconTrendDown :size="16" />
 * <IconTrendDown :size="20" class="text-red-500" />
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
        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
        <polyline points="17 18 23 18 23 12" />
    </svg>
</template>
