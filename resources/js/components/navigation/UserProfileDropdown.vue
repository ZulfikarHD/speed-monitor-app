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
 * - VeloTrack dark theme styling
 */

import { Link } from '@inertiajs/vue3';
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
    ======================================================================= -->
    <div ref="dropdownRef" class="relative">
        <!-- Dropdown Trigger Button -->
        <button
            @click="toggleDropdown"
            type="button"
            class="flex items-center gap-3 rounded-lg px-3 py-2 transition-colors hover:bg-[#1a1d23] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
            :aria-expanded="isOpen"
            aria-haspopup="true"
            aria-label="User menu"
        >
            <!-- User Avatar -->
            <div
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-sm font-semibold text-white"
                aria-hidden="true"
            >
                {{ getUserInitials() }}
            </div>

            <!-- User Info (Hidden on mobile) -->
            <div class="hidden text-left md:block">
                <p class="text-sm font-medium text-[#e5e7eb]">
                    {{ authStore.user?.name }}
                </p>
                <p class="text-xs text-[#9ca3af]">
                    {{ authStore.user?.role }}
                </p>
            </div>

            <!-- Dropdown Arrow -->
            <svg
                class="h-4 w-4 text-[#9ca3af] transition-transform"
                :class="{ 'rotate-180': isOpen }"
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
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-lg border border-[#3E3E3A] bg-[#1a1d23] shadow-lg ring-1 ring-black ring-opacity-5"
                role="menu"
                aria-orientation="vertical"
            >
                <!-- User Info (Mobile Only) -->
                <div class="border-b border-[#3E3E3A] px-4 py-3 md:hidden">
                    <p class="text-sm font-medium text-[#e5e7eb]">
                        {{ authStore.user?.name }}
                    </p>
                    <p class="text-xs text-[#9ca3af]">
                        {{ authStore.user?.email }}
                    </p>
                </div>

                <!-- Menu Items -->
                <div class="py-1">
                    <!-- Profile Link -->
                    <Link
                        :href="profileShow.url()"
                        @click="closeDropdown"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-[#9ca3af] transition-colors hover:bg-[#0a0c0f] hover:text-[#e5e7eb]"
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

                    <!-- Change Password Link -->
                    <Link
                        href="/profile/change-password"
                        @click="closeDropdown"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-[#9ca3af] transition-colors hover:bg-[#0a0c0f] hover:text-[#e5e7eb]"
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

                    <!-- Divider -->
                    <div class="my-1 border-t border-[#3E3E3A]"></div>

                    <!-- Logout Button -->
                    <button
                        @click="handleLogout"
                        type="button"
                        :disabled="isLoading"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-400 transition-colors hover:bg-[#0a0c0f] hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-60"
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
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
