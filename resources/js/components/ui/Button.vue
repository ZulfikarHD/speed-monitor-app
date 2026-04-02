<script setup lang="ts">
/**
 * Button Component
 *
 * Reusable button with VeloTrack dark theme styling.
 * Supports multiple variants, loading states, and disabled states.
 *
 * Features:
 * - Three variants: primary, secondary, danger
 * - Loading state with spinner
 * - Disabled state
 * - Full width option
 * - Touch-friendly (min 44px height)
 * - ARIA accessibility attributes
 * - VeloTrack dark theme colors with gradient effects
 */

// ========================================================================
// Props
// ========================================================================

interface Props {
    /** Button style variant */
    variant?: 'primary' | 'secondary' | 'danger';
    /** Loading state (shows spinner) */
    loading?: boolean;
    /** Disabled state */
    disabled?: boolean;
    /** Full width button */
    fullWidth?: boolean;
    /** Button type */
    type?: 'button' | 'submit' | 'reset';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    loading: false,
    disabled: false,
    fullWidth: false,
    type: 'button',
});

// ========================================================================
// Computed Classes
// ========================================================================

/**
 * Get variant-specific classes for button styling.
 */
function getVariantClasses(): string {
    switch (props.variant) {
        case 'primary':
            return 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-600 hover:to-blue-700 focus:ring-cyan-500';
        case 'secondary':
            return 'border-2 border-[#3E3E3A] bg-transparent text-[#e5e7eb] hover:bg-[#1a1d23] focus:ring-[#3E3E3A]';
        case 'danger':
            return 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700 focus:ring-red-500';
        default:
            return '';
    }
}
</script>

<template>
    <!-- ======================================================================
        Button
        Touch-friendly button with loading states and variant styling
    ======================================================================= -->
    <button
        :type="type"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#0a0c0f] disabled:cursor-not-allowed disabled:opacity-60"
        :class="[getVariantClasses(), fullWidth ? 'w-full' : '']"
        :aria-busy="loading"
    >
        <!-- Loading Spinner -->
        <svg
            v-if="loading"
            class="h-5 w-5 animate-spin"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <!-- Button Content (slot) -->
        <slot />
    </button>
</template>
