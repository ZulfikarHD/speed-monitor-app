<!--
==============================================================================
SYNC BADGE COMPONENT
==============================================================================

Displays pending sync count with visual status indicator and motion-v animations.
Shows real-time sync status (pending, synced, syncing, error) with appropriate
colors and animations.

Features:
- Circular badge with pending count
- Pulsing animation when items pending (motion-v)
- Green checkmark when fully synced
- Spinning animation when syncing
- Shake animation on error
- Tooltip showing last sync time
- Click to open sync queue modal

UX Laws Applied:
- Fitts's Law: Minimum 36x36px clickable area for easy interaction
- Feedback Principle: Animated visual feedback for all states
- Jakob's Law: Familiar badge pattern (like notification badges)
- Color Psychology: Cyan (action), Green (success), Yellow (warning), Red (error)

@example
```vue
<template>
  <SyncBadge 
    size="md" 
    clickable 
    @click="handleBadgeClick"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

import { useSyncQueue } from '@/composables/useSyncQueue';
import type { BadgeSize } from '@/types/sync';

/**
 * Component props.
 */
interface Props {
    /** Size variant for the badge */
    size?: BadgeSize;

    /** Whether badge should be clickable */
    clickable?: boolean;
}

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when badge is clicked (if clickable) */
    (e: 'click', event: MouseEvent): void;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    clickable: true,
});

const emit = defineEmits<Emits>();

// ==============================================================================
// Dependencies
// ==============================================================================

const {
    pendingCount,
    badgeStatus,
    formattedLastSyncTime,
} = useSyncQueue();

// ==============================================================================
// State
// ==============================================================================

/**
 * Whether to trigger shake animation on error.
 */
const shouldShake = ref(false);

/**
 * Previous pending count for detecting changes.
 *
 * WHY: Triggers pop-in animation when count increases.
 */
const previousCount = ref(0);

/**
 * Whether to trigger pop-in animation.
 */
const shouldPopIn = ref(false);

// ==============================================================================
// Computed Properties
// ==============================================================================

/**
 * Badge size classes based on size prop.
 */
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return {
                badge: 'h-5 w-5 text-[10px]',
                icon: 'h-3 w-3',
            };
        case 'lg':
            return {
                badge: 'h-9 w-9 text-sm',
                icon: 'h-5 w-5',
            };
        case 'md':
        default:
            return {
                badge: 'h-7 w-7 text-xs',
                icon: 'h-4 w-4',
            };
    }
});

/**
 * Badge background and text color classes based on status.
 */
const colorClasses = computed(() => {
    switch (badgeStatus.value) {
        case 'syncing':
            return 'bg-cyan-500 text-white';
        case 'error':
            return 'bg-red-500 text-white';
        case 'pending':
            return 'bg-yellow-500 text-black';
        case 'synced':
        default:
            return 'bg-green-500 text-white';
    }
});

/**
 * Clickable state classes.
 */
const clickableClasses = computed(() => {
    if (!props.clickable) {
return '';
    }

    return 'cursor-pointer hover:scale-110 transition-transform';
});

/**
 * Whether badge should be visible.
 *
 * WHY: Hide badge completely when synced and no pending items.
 * Reduces visual clutter when everything is up-to-date.
 */
const isVisible = computed(() => {
    return pendingCount.value > 0 || badgeStatus.value !== 'synced';
});

/**
 * Tooltip text based on current status.
 */
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

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle badge click event.
 */
function handleClick(event: MouseEvent): void {
    if (props.clickable) {
        emit('click', event);
    }
}

/**
 * Trigger shake animation.
 */
function triggerShake(): void {
    shouldShake.value = true;
    setTimeout(() => {
        shouldShake.value = false;
    }, 500);
}

/**
 * Trigger pop-in animation.
 */
function triggerPopIn(): void {
    shouldPopIn.value = true;
    setTimeout(() => {
        shouldPopIn.value = false;
    }, 500);
}

// ==============================================================================
// Watchers
// ==============================================================================

/**
 * Watch badge status for error state.
 *
 * WHY: Triggers shake animation to draw attention to errors.
 */
watch(badgeStatus, (newStatus, oldStatus) => {
    if (newStatus === 'error' && oldStatus !== 'error') {
        triggerShake();
    }
});

/**
 * Watch pending count for increases.
 *
 * WHY: Triggers pop-in animation to show new items.
 */
watch(pendingCount, (newCount, oldCount) => {
    if (newCount > oldCount && oldCount > 0) {
        triggerPopIn();
    }

    previousCount.value = oldCount;
});
</script>

<template>
    <!-- ================================================================ -->
    <!-- Badge Container with Transition -->
    <!-- ================================================================ -->
    <Transition
        :css="false"
        @enter="
            (el, done) => {
                motion(
                    el,
                    { scale: [0, 1.2, 1], opacity: [0, 1] },
                    { duration: 0.3, easing: [0.34, 1.56, 0.64, 1] }
                ).finished.then(done);
            }
        "
        @leave="
            (el, done) => {
                motion(el, { scale: 0, opacity: 0 }, { duration: 0.2 }).finished.then(done);
            }
        "
    >
        <motion.div
            v-if="isVisible"
            :animate="{
                // Pulse animation for pending status
                ...(badgeStatus === 'pending' && !shouldPopIn
                    ? {
                          scale: [1, 1.15, 1],
                          opacity: [0.9, 1, 0.9],
                      }
                    : {}),
                // Shake animation for error status
                ...(shouldShake
                    ? {
                          x: [-10, 10, -10, 10, 0],
                      }
                    : {}),
                // Pop-in animation for count increase
                ...(shouldPopIn
                    ? {
                          scale: [1, 1.3, 1],
                          rotate: [0, 10, -10, 0],
                      }
                    : {}),
            }"
            :transition="{
                // Pulse: 2s loop for pending
                ...(badgeStatus === 'pending' && !shouldPopIn
                    ? { duration: 2, repeat: Infinity, ease: 'easeInOut' }
                    : {}),
                // Shake: 500ms for error
                ...(shouldShake ? { duration: 0.5 } : {}),
                // Pop-in: 500ms spring bounce
                ...(shouldPopIn ? { duration: 0.5, ease: [0.34, 1.56, 0.64, 1] } : {}),
            }"
            :class="[
                sizeClasses.badge,
                colorClasses,
                clickableClasses,
                'flex items-center justify-center rounded-full font-bold shadow-lg',
            ]"
            :title="tooltipText"
            :aria-label="tooltipText"
            :role="clickable ? 'button' : undefined"
            :tabindex="clickable ? 0 : undefined"
            @click="handleClick"
            @keydown.enter="handleClick"
            @keydown.space.prevent="handleClick"
        >
            <!-- ================================================================ -->
            <!-- Badge Content: Count or Icon -->
            <!-- ================================================================ -->

            <!-- Syncing: Spinning Icon -->
            <svg
                v-if="badgeStatus === 'syncing'"
                v-motion="{
                    animate: { rotate: 360 },
                    transition: { duration: 1, repeat: Infinity, ease: 'linear' },
                }"
                :class="sizeClasses.icon"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Cloud Upload Icon -->
                <path
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <!-- Synced: Checkmark Icon -->
            <svg
                v-else-if="badgeStatus === 'synced'"
                :class="sizeClasses.icon"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <!-- Pending/Error: Count Number -->
            <span v-else>
                {{ pendingCount }}
            </span>
        </motion.div>
    </Transition>
</template>

<style scoped>
/**
 * Ensure proper touch target size for accessibility.
 *
 * WHY: Fitts's Law - minimum 36x36px for easy tapping on mobile.
 */
.cursor-pointer {
    min-width: 36px;
    min-height: 36px;
}
</style>
