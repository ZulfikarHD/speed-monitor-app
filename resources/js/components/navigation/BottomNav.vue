<script setup lang="ts">
/**
 * Bottom Navigation Component
 *
 * Fixed bottom navigation bar optimized for mobile devices.
 * Provides touch-friendly navigation with 4 primary app sections.
 *
 * Features:
 * - 4 navigation items: Dashboard, Speedometer, My Trips, Statistics
 * - Icon + label for each item
 * - Active state with cyan accent color
 * - Touch-friendly targets (≥44x44px)
 * - Safe area inset aware (for notched devices)
 * - Smooth transitions
 * - ARIA accessibility
 */

import { Link } from '@inertiajs/vue3';

import { useActiveRoute } from '@/composables/useActiveRoute';

// ========================================================================
// Navigation Configuration
// ========================================================================

interface NavItem {
    id: string;
    label: string;
    icon: string;
    href: string;
}

const navItems: NavItem[] = [
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

// ========================================================================
// Dependencies
// ========================================================================

const { isActive } = useActiveRoute();
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
        <div class="grid grid-cols-4 gap-1">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="flex flex-col items-center justify-center gap-1 px-2 py-3 transition-colors"
                :class="
                    isActive(item.href)
                        ? 'text-cyan-400'
                        : 'text-[#9ca3af] hover:text-[#e5e7eb]'
                "
                :aria-label="item.label"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <!-- Icon (Emoji) -->
                <span
                    class="text-2xl transition-transform"
                    :class="{ 'scale-110': isActive(item.href) }"
                    aria-hidden="true"
                >
                    {{ item.icon }}
                </span>

                <!-- Label -->
                <span
                    class="text-xs font-medium"
                    :class="
                        isActive(item.href) ? 'font-semibold' : 'font-normal'
                    "
                >
                    {{ item.label }}
                </span>

                <!-- Active Indicator Bar -->
                <div
                    v-if="isActive(item.href)"
                    class="absolute bottom-0 left-1/2 h-1 w-12 -translate-x-1/2 rounded-t-full bg-cyan-400"
                    aria-hidden="true"
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
