<script setup lang="ts">
/**
 * Bottom Navigation Component
 *
 * Fixed bottom navigation bar for mobile devices with fake glass effect.
 * Follows SpeedMonitor design system with lucide icons and theme-aware styling.
 *
 * Features:
 * - 5 navigation items with lucide icons
 * - Sync status badge on My Trips icon
 * - Touch-friendly targets (>=44x44px) per Fitts's Law
 * - Safe area inset aware for notched devices
 * - Active state with gradient accent
 * - ARIA accessibility
 */

import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Car,
    ClipboardList,
    Gauge,
    Home,
    Trophy,
    User,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';
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
    icon: Component;
    href: string;
}

/** Employee navigation items (mobile) */
const employeeNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard', icon: Home, href: '/employee/dashboard' },
    { id: 'speedometer', label: 'Speedometer', icon: Gauge, href: '/employee/speedometer' },
    { id: 'trips', label: 'My Trips', icon: ClipboardList, href: '/employee/my-trips' },
    { id: 'statistics', label: 'Statistics', icon: BarChart3, href: '/employee/statistics' },
    { id: 'profile', label: 'Profile', icon: User, href: '/profile' },
];

/** Supervisor/Admin navigation items (mobile) */
const supervisorNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard', icon: BarChart3, href: '/supervisor/dashboard' },
    { id: 'trips', label: 'All Trips', icon: Car, href: '/supervisor/trips' },
    { id: 'leaderboard', label: 'Leaderboard', icon: Trophy, href: '/supervisor/leaderboard' },
    { id: 'employees', label: 'Employees', icon: Users, href: '/supervisor/employees' },
    { id: 'profile', label: 'Profile', icon: User, href: '/profile' },
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
 * WHY: Supervisors/admins see monitoring tools while employees see trip tracking.
 */
const navItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'supervisor' || role === 'admin') {
        return supervisorNavItems;
    }

    return employeeNavItems;
});

/**
 * WHY: Only employees need sync badge — they track trips offline.
 */
const isEmployee = computed(() => authStore.role === 'employee');

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle sync badge click — opens sync queue modal without navigating.
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
        Fake glass effect — no backdrop-blur, uses semi-transparent bg + borders
    ======================================================================= -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white/95 dark:bg-zinc-900/98 border-t border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 pb-safe"
        role="navigation"
        aria-label="Mobile bottom navigation"
    >
        <!-- Grid pattern overlay (32px, theme-aware) -->
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(6,182,212,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(255,255,255,.01)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.01)_1px,transparent_1px)] bg-[size:32px_32px]"
        ></div>

        <!-- Navigation Items Grid -->
        <div class="relative grid grid-cols-5 gap-0">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="group relative flex min-h-[60px] flex-col items-center justify-center gap-1.5 px-2 py-2.5 transition-colors duration-200"
                :class="
                    isActive(item.href)
                        ? 'text-cyan-700 dark:text-white'
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
                <div class="relative flex items-center justify-center">
                    <!-- Active background pill (dark mode only) -->
                    <div
                        v-if="isActive(item.href)"
                        class="absolute inset-0 -m-1 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 opacity-0 dark:opacity-100"
                    ></div>

                    <component
                        :is="item.icon"
                        :size="22"
                        class="relative transition-colors duration-200"
                        :class="
                            isActive(item.href)
                                ? 'text-cyan-600 dark:text-white'
                                : 'text-zinc-500 dark:text-zinc-500'
                        "
                    />
                </div>

                <!-- Label -->
                <span
                    class="relative text-[10px] font-medium transition-colors duration-200"
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
 * Safe area inset for devices with notches/home indicators.
 */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
