<script setup lang="ts">
/**
 * User Profile Dropdown Component
 *
 * Displays user information with a dropdown menu containing profile and logout options.
 * Accessible with keyboard navigation and ARIA support.
 *
 * Features:
 * - User avatar and name display
 * - Dropdown menu with profile and logout
 * - Keyboard navigation (Escape to close)
 * - Click outside to close
 * - ARIA accessibility attributes
 * - SpeedoMontor dark theme styling
 */

import { Link } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { onBeforeUnmount, onMounted, ref } from 'vue';

import { show as profileShow } from '@/actions/App/Http/Controllers/ProfileController';
import { useAuth } from '@/composables/useAuth';
import { useAuthStore } from '@/stores/auth';

// Import Wayfinder profile route

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

/** Dropdown reference for click outside detection */
const dropdownRef = ref<HTMLDivElement | null>(null);

// ========================================================================
// Methods
// ========================================================================

/**
 * Toggle dropdown visibility.
 */
function toggleDropdown(): void {
    isOpen.value = !isOpen.value;
}

/**
 * Close dropdown.
 */
function closeDropdown(): void {
    isOpen.value = false;
}

/**
 * Handle click outside to close dropdown.
 */
function handleClickOutside(event: MouseEvent): void {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        closeDropdown();
    }
}

/**
 * Handle escape key to close dropdown.
 */
function handleEscapeKey(event: KeyboardEvent): void {
    if (event.key === 'Escape' && isOpen.value) {
        closeDropdown();
    }
}

/**
 * Get user initials for avatar.
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
        Accessible dropdown menu with user info and actions
        Theme-aware design following SpeedMonitor design system
    ======================================================================= -->
    <div ref="dropdownRef" class="relative">
        <!-- Dropdown Trigger Button -->
        <motion.button
            @click="toggleDropdown"
            type="button"
            :whileHover="{ scale: 1.02 }"
            :whilePress="{ scale: 0.98 }"
            :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
            class="flex min-h-[44px] items-center gap-3 rounded-lg px-4 py-3 transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
            :aria-expanded="isOpen"
            aria-haspopup="true"
            aria-label="User menu"
        >
            <!-- User Avatar -->
            <motion.div
                :animate="{ rotate: isOpen ? 180 : 0 }"
                :transition="{ type: 'spring', bounce: 0.5, duration: 0.5 }"
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 dark:from-cyan-500 dark:to-blue-600 text-sm font-semibold text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25"
                aria-hidden="true"
            >
                {{ getUserInitials() }}
            </motion.div>

            <!-- User Info (Hidden on mobile) -->
            <div class="hidden text-left md:block">
                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ authStore.user?.name }}
                </p>
                <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                    {{ authStore.user?.role }}
                </p>
            </div>

            <!-- Dropdown Arrow -->
            <motion.svg
                :animate="{ rotate: isOpen ? 180 : 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
                class="h-4 w-4 text-zinc-500 dark:text-zinc-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </motion.svg>
        </motion.button>

        <!-- Dropdown Menu -->
        <AnimatePresence>
            <motion.div
                v-if="isOpen"
                :initial="{ opacity: 0, scale: 0.95, y: -10 }"
                :animate="{ opacity: 1, scale: 1, y: 0 }"
                :exit="{ opacity: 0, scale: 0.95, y: -10 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.3 }"
                class="absolute right-0 z-[100] mt-2 w-56 origin-top-right rounded-lg border border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900/98 backdrop-blur-xl shadow-lg shadow-zinc-200 dark:shadow-cyan-500/10"
                role="menu"
                aria-orientation="vertical"
            >
                <!-- User Info (Mobile Only) -->
                <motion.div
                    :initial="{ opacity: 0, y: -5 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.05, duration: 0.3 }"
                    class="border-b border-zinc-200 dark:border-white/10 px-4 py-3 md:hidden"
                >
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ authStore.user?.name }}
                    </p>
                    <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                        {{ authStore.user?.email }}
                    </p>
                </motion.div>

                <!-- Menu Items -->
                <div class="py-1">
                    <!-- Profile Link -->
                    <motion.div
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ delay: 0.08, type: 'spring', bounce: 0.3, duration: 0.4 }"
                    >
                        <Link
                            :href="profileShow.url()"
                            @click="closeDropdown"
                            class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-100"
                            role="menuitem"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <span>Profile</span>
                        </Link>
                    </motion.div>

                    <!-- Change Password Link -->
                    <motion.div
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ delay: 0.12, type: 'spring', bounce: 0.3, duration: 0.4 }"
                    >
                        <Link
                            href="/profile/change-password"
                            @click="closeDropdown"
                            class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-100"
                            role="menuitem"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                                />
                            </svg>
                            <span>Change Password</span>
                        </Link>
                    </motion.div>

                    <!-- Divider -->
                    <div class="my-1 border-t border-zinc-200 dark:border-white/10"></div>

                    <!-- Logout Button -->
                    <motion.button
                        @click="handleLogout"
                        type="button"
                        :disabled="isLoading"
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :whileHover="{ x: 3 }"
                        :transition="{ delay: 0.16, type: 'spring', bounce: 0.3, duration: 0.4 }"
                        class="flex min-h-[44px] w-full items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 transition-all duration-200 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-700 dark:hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                        role="menuitem"
                    >
                        <svg
                            v-if="!isLoading"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />
                        </svg>
                        <svg
                            v-else
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
                        <span>{{
                            isLoading ? 'Logging out...' : 'Logout'
                        }}</span>
                    </motion.button>
                </div>
            </motion.div>
        </AnimatePresence>
    </div>
</template>
