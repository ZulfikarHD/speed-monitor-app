<script setup lang="ts">
/**
 * DateRangeFilter - Quick date range selection buttons.
 *
 * Provides one-click filters for common date ranges (Today, This Week, This Month).
 * Note: v1 implementation is UI-only, backend filtering not yet implemented.
 */

type DateRange = 'today' | 'week' | 'month';

interface Props {
    /** Currently selected date range */
    modelValue?: DateRange;
}

interface Emits {
    /** Emitted when date range selection changes */
    (e: 'update:modelValue', value: DateRange): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

/**
 * Handle date range button click.
 *
 * @param range - The selected date range
 */
function selectRange(range: DateRange) {
    emit('update:modelValue', range);
}
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Today Button -->
        <button
            type="button"
            :class="[
                'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                modelValue === 'today'
                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                    : 'bg-[#2A2A28] text-[#A1A09A] border border-[#3E3E3A] hover:border-[#4E4E4A] hover:text-[#EDEDEC]',
            ]"
            @click="selectRange('today')"
        >
            Today
        </button>

        <!-- This Week Button -->
        <button
            type="button"
            :class="[
                'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                modelValue === 'week'
                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                    : 'bg-[#2A2A28] text-[#A1A09A] border border-[#3E3E3A] hover:border-[#4E4E4A] hover:text-[#EDEDEC]',
            ]"
            @click="selectRange('week')"
        >
            This Week
        </button>

        <!-- This Month Button -->
        <button
            type="button"
            :class="[
                'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                modelValue === 'month'
                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                    : 'bg-[#2A2A28] text-[#A1A09A] border border-[#3E3E3A] hover:border-[#4E4E4A] hover:text-[#EDEDEC]',
            ]"
            @click="selectRange('month')"
        >
            This Month
        </button>
    </div>
</template>
