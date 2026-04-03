<script setup lang="ts">
/**
 * Top Navigation Component
 *
 * Horizontal navigation bar for desktop and tablet devices.
 * Provides spacious, accessible navigation with branding and profile.
 *
 * Features:
 * - VeloTrack logo/branding on left
 * - Horizontal navigation links
 * - Sync status badge on My Trips link
 * - User profile dropdown on right
 * - Active state indicators
 * - Responsive design (hidden on mobile)
 * - ARIA accessibility
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

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
    icon: string;
    href: string;
}

/** Employee navigation items */
const employeeNavItems: NavItem[] = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: '🏠',
        href: '/employee/dashboard',
    },
    {
        id: 'speedometer',
        label: 'Speedometer',
        icon: '🚗',
        href: '/employee/speedometer',
    },
    {
        id: 'trips',
        label: 'My Trips',
        icon: '📋',
        href: '/employee/my-trips',
    },
    {
        id: 'statistics',
        label: 'Statistics',
        icon: '📊',
        href: '/employee/statistics',
    },
];

/** Supervisor/Admin navigation items */
const supervisorNavItems: NavItem[] = [
    {
        id: 'dashboard',
        label: 'Dashboard',
        icon: '📊',
        href: '/supervisor/dashboard',
    },
    {
        id: 'trips',
        label: 'All Trips',
        icon: '🚗',
        href: '/supervisor/trips',
    },
    {
        id: 'leaderboard',
        label: 'Leaderboard',
        icon: '🏆',
        href: '/supervisor/leaderboard',
    },
];

/** Admin-only navigation items */
const adminNavItems: NavItem[] = [
    {
        id: 'employees',
        label: 'Employees',
        icon: '👥',
        href: '/admin/employees',
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: '⚙️',
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
        Horizontal navigation with branding and profile
    ======================================================================= -->
    <nav
        class="hidden border-b border-[#3E3E3A] bg-[#1a1d23] md:block"
        role="navigation"
        aria-label="Main navigation"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Left: Logo/Branding -->
                <div class="flex items-center gap-8">
                    <!-- VeloTrack Logo -->
                    <Link
                        href="/employee/dashboard"
                        class="flex items-center gap-2 transition-opacity hover:opacity-80"
                    >
                        <motion.div
                            :whileHover="{ scale: 1.05, rotate: 3 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.6, duration: 0.5 }"
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600"
                        >
                            <span class="text-2xl" aria-hidden="true">🚗</span>
                        </motion.div>
                        <span
                            class="text-xl font-bold tracking-tight text-[#e5e7eb]"
                            style="font-family: 'Bebas Neue', sans-serif"
                        >
                            VeloTrack
                        </span>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.id"
                            :href="item.href"
                            class="relative flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                            :class="
                                isActive(item.href)
                                    ? 'bg-cyan-500/10 text-cyan-400'
                                    : 'text-[#9ca3af] hover:bg-[#0a0c0f] hover:text-[#e5e7eb]'
                            "
                            :aria-current="isActive(item.href) ? 'page' : undefined"
                        >
                            <!-- Sync Badge (My Trips only, employees only) -->
                            <SyncBadge
                                v-if="item.id === 'trips' && isEmployee"
                                class="absolute -right-2 -top-1 z-10"
                                size="sm"
                                @click="handleSyncBadgeClick"
                            />
                            <!-- Icon -->
                            <motion.span
                                :animate="{
                                    scale: isActive(item.href) ? 1.1 : 1,
                                }"
                                :whileHover="{ scale: 1.2, rotate: 5 }"
                                :whilePress="{ scale: 0.9 }"
                                :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                                class="text-lg"
                                aria-hidden="true"
                            >
                                {{ item.icon }}
                            </motion.span>

                            <!-- Label -->
                            <span>{{ item.label }}</span>
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
