<script setup lang="ts">
/**
 * Sync Badge Component
 *
 * Displays pending sync count with visual status indicator.
 * Shows real-time sync status (pending, synced, syncing, error) with
 * appropriate colors and lightweight animations.
 *
 * Features:
 * - Circular badge with pending count
 * - CSS pulse animation for pending state
 * - Lucide icons for syncing/synced states
 * - Click to open sync queue modal
 * - Tooltip with last sync time
 *
 * UX Principles:
 * - Fitts's Law: Minimum 36x36px clickable area
 * - Feedback: Visual status indicators for all states
 * - Jakob's Law: Familiar notification badge pattern
 */

import { Check, Loader2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import { useSyncQueue } from '@/composables/useSyncQueue';
import type { BadgeSize } from '@/types/sync';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Size variant */
    size?: BadgeSize;
    /** Whether badge is clickable */
    clickable?: boolean;
}

interface Emits {
    /** Badge clicked (if clickable) */
    (e: 'click', event: MouseEvent): void;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    clickable: true,
});

const emit = defineEmits<Emits>();

// ========================================================================
// Dependencies
// ========================================================================

const {
    pendingCount,
    badgeStatus,
    formattedLastSyncTime,
} = useSyncQueue();

// ========================================================================
// State
// ========================================================================

const shouldShake = ref(false);

// ========================================================================
// Computed
// ========================================================================

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return { badge: 'h-5 w-5 text-[10px]', icon: 'h-3 w-3' };
        case 'lg':
            return { badge: 'h-9 w-9 text-sm', icon: 'h-5 w-5' };
        case 'md':
        default:
            return { badge: 'h-7 w-7 text-xs', icon: 'h-4 w-4' };
    }
});

const colorClasses = computed(() => {
    switch (badgeStatus.value) {
        case 'syncing': return 'bg-cyan-500 text-white';
        case 'error': return 'bg-red-500 text-white';
        case 'pending': return 'bg-amber-500 text-black';
        case 'synced':
        default: return 'bg-emerald-500 text-white';
    }
});

const clickableClasses = computed(() => {
    if (!props.clickable) {
return '';
}

    return 'cursor-pointer hover:scale-110 transition-transform duration-200';
});

/**
 * WHY: Hide badge when synced and no pending items — reduce visual clutter.
 */
const isVisible = computed(() => {
    return pendingCount.value > 0 || badgeStatus.value !== 'synced';
});

const tooltipText = computed(() => {
    if (badgeStatus.value === 'syncing') {
return 'Sedang sinkronisasi...';
}

    if (badgeStatus.value === 'error') {
return 'Sinkronisasi gagal';
}

    if (badgeStatus.value === 'pending') {
return `${pendingCount.value} item menunggu sync`;
}

    return `Tersinkronisasi - ${formattedLastSyncTime.value}`;
});

const iconSize = computed(() => {
    switch (props.size) {
        case 'sm': return 10;
        case 'lg': return 18;
        default: return 14;
    }
});

// ========================================================================
// Methods
// ========================================================================

function handleClick(event: MouseEvent): void {
    if (props.clickable) {
emit('click', event);
}
}

// ========================================================================
// Watchers
// ========================================================================

/**
 * WHY: Shake animation draws attention to sync errors.
 */
watch(badgeStatus, (newStatus, oldStatus) => {
    if (newStatus === 'error' && oldStatus !== 'error') {
        shouldShake.value = true;
        setTimeout(() => {
 shouldShake.value = false; 
}, 500);
    }
});
</script>

<template>
    <!-- Sync Badge -->
    <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="scale-0 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition-all duration-150"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-0 opacity-0"
    >
        <div
            v-if="isVisible"
            :class="[
                sizeClasses.badge,
                colorClasses,
                clickableClasses,
                'flex items-center justify-center rounded-full font-bold shadow-lg',
                badgeStatus === 'pending' ? 'animate-pulse' : '',
                shouldShake ? 'animate-shake' : '',
            ]"
            :title="tooltipText"
            :aria-label="tooltipText"
            :role="clickable ? 'button' : undefined"
            :tabindex="clickable ? 0 : undefined"
            @click="handleClick"
            @keydown.enter="handleClick"
            @keydown.space.prevent="handleClick"
        >
            <!-- Syncing: Spinner -->
            <Loader2
                v-if="badgeStatus === 'syncing'"
                :size="iconSize"
                class="animate-spin"
            />

            <!-- Synced: Checkmark -->
            <Check
                v-else-if="badgeStatus === 'synced'"
                :size="iconSize"
                :stroke-width="3"
            />

            <!-- Pending/Error: Count Number -->
            <span v-else>{{ pendingCount }}</span>
        </div>
    </Transition>
</template>

<style scoped>
.cursor-pointer {
    min-width: 36px;
    min-height: 36px;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    50% { transform: translateX(6px); }
    75% { transform: translateX(-6px); }
}

.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>
