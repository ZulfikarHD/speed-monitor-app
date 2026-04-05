<script setup lang="ts">
/**
 * Label Component
 *
 * Reusable form label with SpeedoMontor dark theme styling.
 * Supports required field indicator and proper accessibility.
 *
 * Features:
 * - Required field indicator (red asterisk)
 * - Proper `for` attribute for input association
 * - SpeedoMontor dark theme colors
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
        Form label with required indicator and SpeedoMontor styling
    ======================================================================= -->
    <label
        :for="props.for"
        class="mb-2 block text-sm font-medium text-[#e5e7eb]"
    >
        <slot />
        <span
            v-if="required"
            class="ml-1 text-red-400"
            aria-label="required"
            >*</span
        >
    </label>
</template>
