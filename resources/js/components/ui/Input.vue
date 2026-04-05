<script setup lang="ts">
/**
 * Input Component
 *
 * Reusable form input with SpeedoMontor dark theme styling.
 * Supports error states, disabled states, and various input types.
 *
 * Features:
 * - v-model binding with two-way data flow
 * - Support for all HTML input types (text, email, password, etc.)
 * - Error state styling with red borders
 * - Disabled state with reduced opacity
 * - Touch-friendly (min 44px height)
 * - ARIA accessibility attributes
 * - SpeedoMontor dark theme colors
 */

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Input value (v-model) */
    modelValue: string;
    /** Input type (text, email, password, etc.) */
    type?: string;
    /** Placeholder text */
    placeholder?: string;
    /** Disabled state */
    disabled?: boolean;
    /** Error message to display */
    error?: string;
    /** Input id for label association */
    id?: string;
    /** Autocomplete attribute */
    autocomplete?: string;
    /** Additional CSS classes */
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: '',
    disabled: false,
    error: '',
    id: '',
    autocomplete: 'off',
    class: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle input change and emit update.
 */
function handleInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
}
</script>

<template>
    <!-- ======================================================================
        Input Field
        Touch-friendly input with error states and SpeedoMontor styling
    ======================================================================= -->
    <div class="w-full">
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            @input="handleInput"
            :placeholder="placeholder"
            :disabled="disabled"
            :autocomplete="autocomplete"
            class="w-full rounded-lg border px-4 py-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 disabled:cursor-not-allowed disabled:opacity-60"
            :class="[
                error
                    ? 'border-red-400 dark:border-red-400 bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-100 placeholder-red-500 dark:placeholder-red-400/60 focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50'
                    : 'border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:border-cyan-500 dark:focus:border-cyan-400 focus:ring-cyan-500 dark:focus:ring-cyan-400/50',
                props.class,
            ]"
            :aria-invalid="!!error"
            :aria-describedby="error ? `${id}-error` : undefined"
        />

        <!-- Error Message -->
        <p
            v-if="error"
            :id="`${id}-error`"
            class="mt-1.5 text-sm text-red-600 dark:text-red-400"
            role="alert"
        >
            {{ error }}
        </p>
    </div>
</template>
