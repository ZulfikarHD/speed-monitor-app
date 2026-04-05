<script setup lang="ts">
/**
 * User Profile Dropdown Component
 *
 * Displays user avatar and info with a dropdown menu for profile and logout.
 * Uses fake glass effect dropdown with lucide icons.
 *
 * Features:
 * - User avatar with initials and gradient background
 * - Dropdown with profile, password change, and logout actions
 * - Keyboard navigation (Escape to close)
 * - Click outside to close
 * - ARIA accessibility attributes
 * - Theme-aware fake glass dropdown
 *
 * UX Principles:
 * - Fitts's Law: 44px+ touch targets on all interactive elements
 * - Hick's Law: Limited menu options (3 items)
 * - Jakob's Law: Standard dropdown menu pattern
 */

import { Link } from '@inertiajs/vue3';
import { ChevronDown, KeyRound, Loader2, LogOut, User } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { show as profileShow } from '@/actions/App/Http/Controllers/ProfileController';
import { useAuth } from '@/composables/useAuth';
import { useAuthStore } from '@/stores/auth';

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const { handleLogout, isLoading } = useAuth();

// ========================================================================
// Local State
// ========================================================================

/** Dropdown open/closed state */
const isOpen = ref(false);

/** Dropdown ref for click outside detection */
const dropdownRef = ref<HTMLDivElement | null>(null);

// ========================================================================
// Methods
// ========================================================================

/** Toggle dropdown visibility. */
function toggleDropdown(): void {
    isOpen.value = !isOpen.value;
}

/** Close dropdown. */
function closeDropdown(): void {
    isOpen.value = false;
}

/** Handle click outside to close dropdown. */
function handleClickOutside(event: MouseEvent): void {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        closeDropdown();
    }
}

/** Handle escape key to close dropdown. */
function handleEscapeKey(event: KeyboardEvent): void {
    if (event.key === 'Escape' && isOpen.value) {
        closeDropdown();
    }
}

/**
 * Get user initials for avatar display.
 *
 * @returns Two-letter initials from user name
 */
function getUserInitials(): string {
    const name = authStore.user?.name || '';
    const parts = name.split(' ');

    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    return name.slice(0, 2).toUpperCase();
}

// ========================================================================
// Lifecycle Hooks
// ========================================================================

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleEscapeKey);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleEscapeKey);
});
</script>

<template>
    <!-- ======================================================================
        User Profile Dropdown
        Accessible dropdown with fake glass effect
    ======================================================================= -->
    <div ref="dropdownRef" class="relative">
        <!-- Trigger Button -->
        <button
            @click="toggleDropdown"
            type="button"
            class="flex min-h-[44px] items-center gap-3 rounded-lg px-4 py-3 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
            :aria-expanded="isOpen"
            aria-haspopup="true"
            aria-label="User menu"
        >
            <!-- User Avatar -->
            <div
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25"
                aria-hidden="true"
            >
                {{ getUserInitials() }}
            </div>

            <!-- User Info (Desktop only) -->
            <div class="hidden text-left md:block">
                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ authStore.user?.name }}
                </p>
                <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                    {{ authStore.user?.role }}
                </p>
            </div>

            <!-- Dropdown Arrow (CSS transition for rotation) -->
            <ChevronDown
                :size="16"
                class="text-zinc-500 dark:text-zinc-400 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <!-- Dropdown Menu -->
        <AnimatePresence>
            <motion.div
                v-if="isOpen"
                :initial="{ opacity: 0, scale: 0.95, y: -8 }"
                :animate="{ opacity: 1, scale: 1, y: 0 }"
                :exit="{ opacity: 0, scale: 0.95, y: -8 }"
                :transition="{ duration: 0.15 }"
                class="absolute right-0 z-[100] mt-2 w-56 origin-top-right rounded-lg bg-white/95 dark:bg-zinc-900/98 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/10"
                role="menu"
                aria-orientation="vertical"
            >
                <!-- User Info (Mobile Only) -->
                <div class="border-b border-zinc-200 dark:border-white/10 px-4 py-3 md:hidden">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ authStore.user?.name }}
                    </p>
                    <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                        {{ authStore.user?.email }}
                    </p>
                </div>

                <!-- Menu Items -->
                <div class="py-1">
                    <!-- Profile Link -->
                    <Link
                        :href="profileShow.url()"
                        @click="closeDropdown"
                        class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-100"
                        role="menuitem"
                    >
                        <User :size="18" aria-hidden="true" />
                        <span>Profile</span>
                    </Link>

                    <!-- Change Password Link -->
                    <Link
                        href="/profile/change-password"
                        @click="closeDropdown"
                        class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-100"
                        role="menuitem"
                    >
                        <KeyRound :size="18" aria-hidden="true" />
                        <span>Change Password</span>
                    </Link>

                    <!-- Divider -->
                    <div class="my-1 border-t border-zinc-200 dark:border-white/10"></div>

                    <!-- Logout Button -->
                    <button
                        @click="handleLogout"
                        type="button"
                        :disabled="isLoading"
                        class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 transition-colors duration-200 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-700 dark:hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                        role="menuitem"
                    >
                        <Loader2
                            v-if="isLoading"
                            :size="18"
                            class="animate-spin"
                            aria-hidden="true"
                        />
                        <LogOut
                            v-else
                            :size="18"
                            aria-hidden="true"
                        />
                        <span>{{ isLoading ? 'Logging out...' : 'Logout' }}</span>
                    </button>
                </div>
            </motion.div>
        </AnimatePresence>
    </div>
</template>
