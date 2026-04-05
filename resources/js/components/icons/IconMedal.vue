<script setup lang="ts">
/**
 * Medal Icon Component
 *
 * SVG icon for ranking/awards following design system standards.
 * Supports color variants for gold/silver/bronze through CSS classes.
 *
 * @example
 * <IconMedal :size="24" />
 * <IconMedal :size="20" class="text-yellow-500" />
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
        <circle cx="12" cy="12" r="8" />
        <path d="M12 8v4" />
        <path d="M10 10l2-2 2 2" />
    </svg>
</template>
