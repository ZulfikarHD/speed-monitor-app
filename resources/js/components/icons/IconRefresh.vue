<script setup lang="ts">
/**
 * Refresh Icon Component
 *
 * SVG icon for refresh/reload actions following design system standards.
 *
 * @example
 * <IconRefresh :size="20" />
 * <IconRefresh :size="24" class="text-cyan-500" />
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
        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2" />
    </svg>
</template>
