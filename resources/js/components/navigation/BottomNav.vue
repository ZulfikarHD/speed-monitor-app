<script setup lang="ts">
/**
 * Bottom Navigation Component
 *
 * Fixed bottom navigation bar optimized for mobile devices.
 * Provides touch-friendly navigation with 4 primary app sections.
 *
 * Features:
 * - 5 navigation items: Dashboard, Speedometer, My Trips, Statistics, Profile
 * - Sync status badge on My Trips icon
 * - Icon + label for each item
 * - Active state with cyan accent color
 * - Touch-friendly targets (≥44x44px)
 * - Safe area inset aware (for notched devices)
 * - Smooth transitions
 * - ARIA accessibility
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

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

/** Employee navigation items (mobile) */
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
    {
        id: 'profile',
        label: 'Profile',
        icon: '👤',
        href: '/profile',
    },
];

/** Supervisor navigation items (mobile) */
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
    {
        id: 'settings',
        label: 'Settings',
        icon: '⚙️',
        href: '/settings',
    },
    {
        id: 'profile',
        label: 'Profile',
        icon: '👤',
        href: '/profile',
    },
];

/** Admin navigation items (mobile) */
const adminNavItems: NavItem[] = [
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
        id: 'employees',
        label: 'Employees',
        icon: '👥',
        href: '/admin/employees',
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: '⚙️',
        href: '/settings',
    },
    {
        id: 'profile',
        label: 'Profile',
        icon: '👤',
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
        Fixed position navigation with 4 main app sections
    ======================================================================= -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-40 border-t border-[#3E3E3A] bg-[#1a1d23] pb-safe md:hidden"
        role="navigation"
        aria-label="Mobile bottom navigation"
    >
        <!-- Navigation Items Grid -->
        <div class="grid grid-cols-5 gap-1">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="relative flex min-h-[44px] flex-col items-center justify-center gap-1 px-2 py-3 transition-colors"
                :class="
                    isActive(item.href)
                        ? 'text-cyan-400'
                        : 'text-[#9ca3af] hover:text-[#e5e7eb]'
                "
                :aria-label="item.label"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <!-- Sync Badge (My Trips only, employees only) -->
                <SyncBadge
                    v-if="item.id === 'trips' && isEmployee"
                    class="absolute right-1 top-2 z-10"
                    size="sm"
                    @click="handleSyncBadgeClick"
                />
                <!-- Icon (Emoji) -->
                <motion.span
                    :animate="{
                        scale: isActive(item.href) ? 1.1 : 1,
                        y: isActive(item.href) ? -2 : 0,
                    }"
                    :whileHover="{ scale: 1.15, y: -2 }"
                    :whilePress="{ scale: 0.95 }"
                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.5 }"
                    class="text-2xl"
                    aria-hidden="true"
                >
                    {{ item.icon }}
                </motion.span>

                <!-- Label -->
                <motion.span
                    :animate="{
                        opacity: isActive(item.href) ? 1 : 0.8,
                    }"
                    :transition="{ duration: 0.3 }"
                    class="text-xs font-medium"
                    :class="
                        isActive(item.href) ? 'font-semibold' : 'font-normal'
                    "
                >
                    {{ item.label }}
                </motion.span>

                <!-- Active Indicator Bar -->
                <motion.div
                    v-if="isActive(item.href)"
                    :initial="{ scaleX: 0, opacity: 0 }"
                    :animate="{ scaleX: 1, opacity: 1 }"
                    :exit="{ scaleX: 0, opacity: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.5 }"
                    class="absolute bottom-0 left-1/2 h-1 w-12 -translate-x-1/2 rounded-t-full bg-cyan-400"
                    aria-hidden="true"
                ></motion.div>
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
