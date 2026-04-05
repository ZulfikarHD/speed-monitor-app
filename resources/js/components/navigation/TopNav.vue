<script setup lang="ts">
/**
 * Top Navigation Component
 *
 * Horizontal navigation bar for desktop and tablet devices.
 * Professional design with glassmorphism and subtle animations.
 *
 * Features:
 * - SpeedoMontor logo/branding on left
 * - Horizontal navigation links with SVG icons
 * - Sync status badge on My Trips link
 * - User profile dropdown on right
 * - Active state indicators
 * - Responsive design (hidden on mobile)
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
    IconUsers,
} from '@/components/icons';
import UserProfileDropdown from '@/components/navigation/UserProfileDropdown.vue';
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

/** Employee navigation items */
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
];

/** Supervisor/Admin navigation items */
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
];

/** Admin-only navigation items */
const adminNavItems: NavItem[] = [
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
 * Admins get additional employee management link.
 */
const navItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'admin') {
        return [...supervisorNavItems, ...adminNavItems];
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
        Top Navigation Bar (Desktop/Tablet)
        Theme-aware glassmorphism design with extra dark mode
    ======================================================================= -->
    <nav
        class="fixed top-0 left-0 right-0 z-50 hidden border-b border-zinc-200 dark:border-white/5 bg-white/90 dark:bg-black/98 backdrop-blur-xl md:block"
        role="navigation"
        aria-label="Main navigation"
    >
        <!-- Subtle grid pattern overlay (theme-aware) -->
        <div class="pointer-events-none absolute inset-0 z-0 bg-[linear-gradient(rgba(6,182,212,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(255,255,255,.01)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.01)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Left: Logo/Branding -->
                <div class="flex items-center gap-8">
                    <!-- SpeedMonitor Logo -->
                    <Link
                        href="/employee/dashboard"
                        class="group flex items-center gap-3 transition-all duration-300"
                    >
                        <div class="relative flex h-10 w-10 items-center justify-center rounded-lg border border-cyan-200 dark:border-cyan-500/20 bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-500/10 dark:to-blue-600/10 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/5 transition-all duration-300 group-hover:border-cyan-300 dark:group-hover:border-cyan-400/40 group-hover:shadow-cyan-300 dark:group-hover:shadow-cyan-500/20">
                            <IconCar
                                :size="20"
                                class="text-cyan-600 dark:text-cyan-400 transition-transform duration-300 group-hover:scale-110"
                            />
                            <div class="absolute inset-0 rounded-lg bg-gradient-to-br from-cyan-400/0 to-blue-500/0 opacity-0 transition-opacity duration-300 group-hover:opacity-20"></div>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-mono text-base font-semibold tracking-wider text-zinc-900 dark:text-zinc-50">
                                SpeedMonitor
                            </span>
                            <span class="text-[10px] font-medium tracking-widest text-zinc-500 dark:text-zinc-500">
                                TRACKING
                            </span>
                        </div>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.id"
                            :href="item.href"
                            class="group relative flex items-center gap-2.5 rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                            :class="
                                isActive(item.href)
                                    ? 'bg-cyan-100 dark:bg-gradient-to-br dark:from-cyan-500/15 dark:to-blue-600/15 text-cyan-700 dark:text-cyan-100 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/10'
                                    : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-200'
                            "
                            :aria-current="isActive(item.href) ? 'page' : undefined"
                        >
                            <!-- Sync Badge (My Trips only, employees only) -->
                            <SyncBadge
                                v-if="item.id === 'trips' && isEmployee"
                                class="absolute -right-1 -top-1 z-10"
                                size="sm"
                                @click="handleSyncBadgeClick"
                            />
                            <!-- Icon -->
                            <component
                                :is="item.icon"
                                :size="18"
                                class="transition-all duration-200"
                                :class="
                                    isActive(item.href)
                                        ? 'text-cyan-600 dark:text-cyan-300'
                                        : 'text-zinc-500 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                                "
                            />

                            <!-- Label -->
                            <span class="font-medium">{{ item.label }}</span>

                            <!-- Active indicator line -->
                            <div
                                v-if="isActive(item.href)"
                                class="absolute -bottom-2 left-1/2 h-0.5 w-8 -translate-x-1/2 rounded-full bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-400 dark:to-blue-500"
                            ></div>
                        </Link>
                    </div>
                </div>

                <!-- Right: User Profile -->
                <div class="flex items-center">
                    <UserProfileDropdown />
                </div>
            </div>
        </div>
    </nav>
</template>
