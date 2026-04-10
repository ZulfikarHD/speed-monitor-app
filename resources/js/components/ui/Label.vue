<script setup lang="ts">
/**
 * Label Component
 *
 * Reusable form label with SafeTrack dark theme styling.
 * Supports required field indicator and proper accessibility.
 *
 * Features:
 * - Required field indicator (red asterisk)
 * - Proper `for` attribute for input association
 * - SafeTrack dark theme colors
 * - Accessible label-input relationship
 */

// ========================================================================
// Props
// ========================================================================

interface Props {
    /** Input id for label association */
    for?: string;
    /** Required field indicator */
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    for: '',
    required: false,
});
</script>

<template>
    <!-- ======================================================================
        Label
        Form label with required indicator and SafeTrack styling
    ======================================================================= -->
    <label
        :for="props.for"
        class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
    >
        <slot />
        <span
            v-if="required"
            class="ml-1 text-red-500 dark:text-red-400"
            aria-label="required"
            >*</span
        >
    </label>
</template>
