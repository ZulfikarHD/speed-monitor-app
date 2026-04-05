<script setup lang="ts">
/**
 * Bottom Navigation Component
 *
 * Fixed bottom navigation bar optimized for mobile devices.
 * Professional design with glassmorphism and subtle tech elements.
 *
 * Features:
 * - 5 navigation items with SVG icons
 * - Sync status badge on My Trips icon
 * - Icon + label for each item
 * - Active state with cyan accent color
 * - Touch-friendly targets (≥44x44px)
 * - Safe area inset aware (for notched devices)
 * - Smooth transitions
 * - ARIA accessibility
 */

import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';
import { computed } from 'vue';

import {
    IconCar,
    IconChart,
    IconClipboard,
    IconGauge,
    IconHome,
    IconSettings,
    IconTrophy,
    IconUser,
    IconUsers,
} from '@/components/icons';
import SyncBadge from '@/components/sync/SyncBadge.vue';
import { useActiveRoute } from '@/composables/useActiveRoute';
import { useSyncQueue } from '@/composables/useSyncQueue';
import { useAuthStore } from '@/stores/auth';

// ========================================================================
// Navigation Configuration
// ========================================================================

interface NavItem {
    id: string;
    label: string;
    icon: Component;
    href: string;
}

/** Employee navigation items (mobile) */
const employeeNavItems: NavItem[] = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: IconHome,
        href: '/employee/dashboard',
    },
    {
        id: 'speedometer',
        label: 'Speedometer',
        icon: IconGauge,
        href: '/employee/speedometer',
    },
    {
        id: 'trips',
        label: 'My Trips',
        icon: IconClipboard,
        href: '/employee/my-trips',
    },
    {
        id: 'statistics',
        label: 'Statistics',
        icon: IconChart,
        href: '/employee/statistics',
    },
    {
        id: 'profile',
        label: 'Profile',
        icon: IconUser,
        href: '/profile',
    },
];

/** Supervisor navigation items (mobile) */
const supervisorNavItems: NavItem[] = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: IconChart,
        href: '/supervisor/dashboard',
    },
    {
        id: 'trips',
        label: 'All Trips',
        icon: IconCar,
        href: '/supervisor/trips',
    },
    {
        id: 'leaderboard',
        label: 'Leaderboard',
        icon: IconTrophy,
        href: '/supervisor/leaderboard',
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: IconSettings,
        href: '/admin/settings',
    },
    {
        id: 'profile',
        label: 'Profile',
        icon: IconUser,
        href: '/profile',
    },
];

/** Admin navigation items (mobile) */
const adminNavItems: NavItem[] = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: IconChart,
        href: '/supervisor/dashboard',
    },
    {
        id: 'trips',
        label: 'All Trips',
        icon: IconCar,
        href: '/supervisor/trips',
    },
    {
        id: 'employees',
        label: 'Employees',
        icon: IconUsers,
        href: '/admin/employees',
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: IconSettings,
        href: '/admin/settings',
    },
    {
        id: 'profile',
        label: 'Profile',
        icon: IconUser,
        href: '/profile',
    },
];

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const { isActive } = useActiveRoute();
const { openModal } = useSyncQueue();

// ========================================================================
// Computed
// ========================================================================

/**
 * Get navigation items based on user role.
 *
 * WHY: Supervisors and admins see different navigation than employees.
 * Employees see speedometer and trip tracking, supervisors see monitoring tools.
 * Admins get employee management instead of leaderboard on mobile (limited space).
 */
const navItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'admin') {
        return adminNavItems;
    }

    if (role === 'supervisor') {
        return supervisorNavItems;
    }

    return employeeNavItems;
});

/**
 * Check if user is employee.
 *
 * WHY: Only employees need sync badge (they track trips offline).
 * Supervisors don't track trips, so no sync functionality needed.
 */
const isEmployee = computed(() => authStore.role === 'employee');

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle sync badge click.
 *
 * WHY: Opens sync queue modal without navigating to My Trips page.
 * Allows quick access to sync status from any page.
 */
function handleSyncBadgeClick(event: MouseEvent): void {
    event.preventDefault();
    event.stopPropagation();
    openModal();
}
</script>

<template>
    <!-- ======================================================================
        Bottom Navigation Bar (Mobile)
        Theme-aware glassmorphism design with extra dark mode
    ======================================================================= -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 border-t border-zinc-200 dark:border-white/5 bg-white/98 dark:bg-black/98 pb-safe backdrop-blur-xl md:hidden"
        role="navigation"
        aria-label="Mobile bottom navigation"
    >
        <!-- Subtle grid pattern overlay (theme-aware) -->
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(6,182,212,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(255,255,255,.01)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.01)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <!-- Navigation Items Grid -->
        <div class="relative grid grid-cols-5 gap-0">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="group relative flex min-h-[60px] flex-col items-center justify-center gap-1.5 px-2 py-2.5 transition-all duration-200"
                :class="
                    isActive(item.href)
                        ? 'text-cyan-700 dark:text-cyan-100'
                        : 'text-zinc-500 dark:text-zinc-500 active:bg-zinc-100 dark:active:bg-white/5'
                "
                :aria-label="item.label"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <!-- Sync Badge (My Trips only, employees only) -->
                <SyncBadge
                    v-if="item.id === 'trips' && isEmployee"
                    class="absolute right-2 top-1.5 z-10"
                    size="sm"
                    @click="handleSyncBadgeClick"
                />

                <!-- Icon Container -->
                <div
                    class="relative flex items-center justify-center transition-all duration-200"
                    :class="
                        isActive(item.href)
                            ? 'scale-105'
                            : 'scale-100 group-active:scale-95'
                    "
                >
                    <!-- Icon Glow Effect (dark mode only) -->
                    <div
                        v-if="isActive(item.href)"
                        class="absolute inset-0 -m-2 rounded-full bg-gradient-to-br from-cyan-400/30 to-blue-500/30 blur-md opacity-0 dark:opacity-100"
                    ></div>

                    <!-- Icon -->
                    <component
                        :is="item.icon"
                        :size="22"
                        class="relative transition-colors duration-200"
                        :class="
                            isActive(item.href)
                                ? 'text-cyan-600 dark:text-cyan-300'
                                : 'text-zinc-500 dark:text-zinc-500 group-active:text-zinc-600 dark:group-active:text-zinc-400'
                        "
                    />
                </div>

                <!-- Label -->
                <span
                    class="relative text-[10px] font-medium transition-all duration-200"
                    :class="
                        isActive(item.href)
                            ? 'font-semibold tracking-wide'
                            : 'font-normal tracking-normal'
                    "
                >
                    {{ item.label }}
                </span>

                <!-- Active Indicator -->
                <div
                    v-if="isActive(item.href)"
                    class="absolute top-0 left-1/2 h-0.5 w-10 -translate-x-1/2 rounded-b-full bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-400 dark:to-blue-500 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/50"
                ></div>
            </Link>
        </div>
    </nav>
</template>

<style scoped>
/**
 * Safe area inset support for devices with notches.
 *
 * pb-safe ensures the bottom navigation doesn't overlap
 * with device UI elements (home indicator on iOS).
 */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
