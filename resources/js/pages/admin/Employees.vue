<script setup lang="ts">
/**
 * Employee Management Page (Admin)
 *
 * Comprehensive user management page for admins with CRUD operations,
 * search functionality, role/status filtering, and pagination.
 *
 * Features:
 * - User listing with search by name/email
 * - Filter by role (employee/supervisor/admin) and status (active/inactive)
 * - Add new user with modal form
 * - Edit user details with modal form
 * - Deactivate user with confirmation modal
 * - Responsive design (table on desktop, cards on mobile)
 * - motion-v animations following Law of UX
 * - Pagination (20 per page)
 * - Empty states
 * - Loading states
 * - Success/error flash messages
 *
 * @example Route: /admin/employees
 */

import { router, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

import ConfirmDeactivateModal from '@/components/admin/ConfirmDeactivateModal.vue';
import UserFormModal from '@/components/admin/UserFormModal.vue';
import Pagination from '@/components/trips/Pagination.vue';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import { useAuthStore } from '@/stores/auth';
import type { User, UserListFilters } from '@/types/auth';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Users for current page */
    users: User[];

    /** Pagination metadata */
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };

    /** Current filter values */
    filters: UserListFilters;
}

const props = defineProps<Props>();

// ========================================================================
// Stores & Page Data
// ========================================================================

const authStore = useAuthStore();
const page = usePage();

// ========================================================================
// Local State
// ========================================================================

/** Show add user modal */
const showAddModal = ref(false);

/** Show edit user modal */
const showEditModal = ref(false);

/** Show deactivate confirmation modal */
const showDeactivateModal = ref(false);

/** User being edited */
const editingUser = ref<User | null>(null);

/** User being deactivated */
const deactivatingUser = ref<User | null>(null);

/** Local filter state (synced with props) */
const localFilters = ref<UserListFilters>({
    search: props.filters.search,
    role: props.filters.role,
    status: props.filters.status,
});

/** Debounce timer for search input */
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

// ========================================================================
// Computed
// ========================================================================

/**
 * Check if there are any active filters.
 */
const hasActiveFilters = computed(() => {
    return !!(localFilters.value.search || localFilters.value.role || localFilters.value.status);
});

/**
 * Flash message from server.
 */
const flashMessage = computed(() => {
    return page.props.flash?.success as string | undefined;
});

// ========================================================================
// Watchers
// ========================================================================

/**
 * Watch for search input changes and debounce.
 */
watch(
    () => localFilters.value.search,
    () => {
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = setTimeout(() => {
            applyFilters();
        }, 500);
    }
);

/**
 * Watch for role filter changes.
 */
watch(
    () => localFilters.value.role,
    () => {
        applyFilters();
    }
);

/**
 * Watch for status filter changes.
 */
watch(
    () => localFilters.value.status,
    () => {
        applyFilters();
    }
);

// ========================================================================
// Methods
// ========================================================================

/**
 * Apply filters by navigating with query parameters.
 */
function applyFilters(): void {
    const query: Record<string, string> = {};

    if (localFilters.value.search) {
        query.search = localFilters.value.search;
    }

    if (localFilters.value.role) {
        query.role = localFilters.value.role;
    }

    if (localFilters.value.status) {
        query.status = localFilters.value.status;
    }

    router.get(route('admin.employees'), query, {
        preserveState: true,
        preserveScroll: true,
    });
}

/**
 * Clear all filters.
 */
function clearFilters(): void {
    localFilters.value = {
        search: '',
        role: '',
        status: '',
    };

    router.get(route('admin.employees'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

/**
 * Open add user modal.
 */
function openAddModal(): void {
    showAddModal.value = true;
}

/**
 * Close add user modal.
 */
function closeAddModal(): void {
    showAddModal.value = false;
}

/**
 * Open edit user modal.
 */
function openEditModal(user: User): void {
    editingUser.value = user;
    showEditModal.value = true;
}

/**
 * Close edit user modal.
 */
function closeEditModal(): void {
    showEditModal.value = false;
    editingUser.value = null;
}

/**
 * Open deactivate confirmation modal.
 */
function openDeactivateModal(user: User): void {
    deactivatingUser.value = user;
    showDeactivateModal.value = true;
}

/**
 * Close deactivate confirmation modal.
 */
function closeDeactivateModal(): void {
    showDeactivateModal.value = false;
    deactivatingUser.value = null;
}

/**
 * Handle page change from pagination.
 */
function handlePageChange(page: number): void {
    const query: Record<string, string | number> = { page };

    if (localFilters.value.search) {
        query.search = localFilters.value.search;
    }

    if (localFilters.value.role) {
        query.role = localFilters.value.role;
    }

    if (localFilters.value.status) {
        query.status = localFilters.value.status;
    }

    router.get(route('admin.employees'), query, {
        preserveScroll: true,
    });
}

/**
 * Get role badge color classes.
 */
function getRoleBadgeColor(role: string): string {
    switch (role) {
        case 'admin':
            return 'bg-purple-500/20 text-purple-400 border-purple-500/30';
        case 'supervisor':
            return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        case 'employee':
            return 'bg-green-500/20 text-green-400 border-green-500/30';
        default:
            return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
}

/**
 * Get role display label.
 */
function getRoleLabel(role: string): string {
    switch (role) {
        case 'admin':
            return 'Admin';
        case 'supervisor':
            return 'Supervisor';
        case 'employee':
            return 'Karyawan';
        default:
            return role;
    }
}

/**
 * Get status badge color classes.
 */
function getStatusBadgeColor(isActive: boolean): string {
    return isActive
        ? 'bg-green-500/20 text-green-400 border-green-500/30'
        : 'bg-red-500/20 text-red-400 border-red-500/30';
}

/**
 * Get status display label.
 */
function getStatusLabel(isActive: boolean): string {
    return isActive ? 'Aktif' : 'Nonaktif';
}
</script>

<template>
    <SupervisorLayout title="Kelola Karyawan">
        <div class="min-h-screen bg-[#0a0c0f] p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- ======================================================================
                    Header Section
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Title -->
                    <div>
                        <h1 class="text-3xl font-bold text-[#EDEDEC] md:text-4xl">
                            Kelola Karyawan
                        </h1>
                        <p class="mt-1 text-sm text-[#A1A09A]">
                            Manajemen pengguna dan hak akses sistem
                        </p>
                    </div>

                    <!-- Add Button -->
                    <motion.button
                        @click="openAddModal"
                        :whileHover="{ scale: 1.05 }"
                        :whilePress="{ scale: 0.95 }"
                        :transition="{ type: 'spring', stiffness: 400 }"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:from-cyan-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
                    >
                        <span class="text-lg" aria-hidden="true">➕</span>
                        <span>Tambah Karyawan</span>
                    </motion.button>
                </motion.div>

                <!-- ======================================================================
                    Success Message
                ======================================================================= -->
                <motion.div
                    v-if="flashMessage"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.95 }"
                    :transition="{ duration: 0.3 }"
                    class="rounded-lg border border-green-500/30 bg-green-500/10 p-4"
                >
                    <div class="flex items-start gap-3">
                        <span class="text-xl" aria-hidden="true">✅</span>
                        <p class="flex-1 text-sm text-green-300">
                            {{ flashMessage }}
                        </p>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Filters Section
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.4 }"
                    class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4 md:p-6"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <!-- Search Input -->
                        <div>
                            <label
                                for="search"
                                class="mb-2 block text-sm font-medium text-[#e5e7eb]"
                            >
                                Cari Karyawan
                            </label>
                            <input
                                id="search"
                                v-model="localFilters.search"
                                type="text"
                                placeholder="Nama atau email..."
                                class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-2 text-sm text-[#e5e7eb] placeholder-[#6b7280] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                            />
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label
                                for="role-filter"
                                class="mb-2 block text-sm font-medium text-[#e5e7eb]"
                            >
                                Filter Role
                            </label>
                            <select
                                id="role-filter"
                                v-model="localFilters.role"
                                class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                            >
                                <option value="">Semua Role</option>
                                <option value="employee">Karyawan</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label
                                for="status-filter"
                                class="mb-2 block text-sm font-medium text-[#e5e7eb]"
                            >
                                Filter Status
                            </label>
                            <select
                                id="status-filter"
                                v-model="localFilters.status"
                                class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Clear Filters Button -->
                    <div
                        v-if="hasActiveFilters"
                        class="mt-4 flex justify-end"
                    >
                        <button
                            @click="clearFilters"
                            class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors"
                        >
                            Hapus Filter
                        </button>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Users Table (Desktop)
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.2, duration: 0.4 }"
                    class="hidden md:block overflow-hidden rounded-lg border border-[#3E3E3A] bg-[#1a1d23]"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-[#3E3E3A] bg-[#0a0c0f]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#e5e7eb]">
                                        Nama
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#e5e7eb]">
                                        Email
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#e5e7eb]">
                                        Role
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#e5e7eb]">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-[#e5e7eb]">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <motion.tr
                                    v-for="(user, index) in users"
                                    :key="user.id"
                                    :initial="{ opacity: 0, x: -20 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ delay: index * 0.05, duration: 0.3 }"
                                    class="border-b border-[#3E3E3A] last:border-b-0 hover:bg-[#0a0c0f]/50 transition-colors"
                                >
                                    <td class="px-6 py-4 text-sm text-[#e5e7eb]">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#A1A09A]">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getRoleBadgeColor(user.role)"
                                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                        >
                                            {{ getRoleLabel(user.role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusBadgeColor(user.is_active)"
                                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                        >
                                            {{ getStatusLabel(user.is_active) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Edit Button -->
                                            <motion.button
                                                v-if="user.id !== authStore.user?.id"
                                                @click="openEditModal(user)"
                                                :whileHover="{ scale: 1.1 }"
                                                :whilePress="{ scale: 0.9 }"
                                                :transition="{ type: 'spring', stiffness: 400 }"
                                                class="rounded-lg bg-blue-500/20 p-2 text-blue-400 transition-colors hover:bg-blue-500/30"
                                                title="Edit"
                                            >
                                                <span class="text-lg" aria-hidden="true">✏️</span>
                                            </motion.button>

                                            <!-- Deactivate Button -->
                                            <motion.button
                                                v-if="user.id !== authStore.user?.id && user.is_active"
                                                @click="openDeactivateModal(user)"
                                                :whileHover="{ scale: 1.1 }"
                                                :whilePress="{ scale: 0.9 }"
                                                :transition="{ type: 'spring', stiffness: 400 }"
                                                class="rounded-lg bg-red-500/20 p-2 text-red-400 transition-colors hover:bg-red-500/30"
                                                title="Nonaktifkan"
                                            >
                                                <span class="text-lg" aria-hidden="true">🚫</span>
                                            </motion.button>

                                            <!-- Self Indicator -->
                                            <span
                                                v-if="user.id === authStore.user?.id"
                                                class="text-xs text-[#6b7280]"
                                            >
                                                (Anda)
                                            </span>
                                        </div>
                                    </td>
                                </motion.tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="users.length === 0"
                        class="py-12 text-center"
                    >
                        <p class="text-sm text-[#6b7280]">
                            Tidak ada karyawan ditemukan.
                        </p>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Users Cards (Mobile)
                ======================================================================= -->
                <div class="md:hidden space-y-4">
                    <motion.div
                        v-for="(user, index) in users"
                        :key="user.id"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: index * 0.05, duration: 0.3 }"
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
                    >
                        <div class="space-y-3">
                            <!-- Name & Email -->
                            <div>
                                <p class="font-semibold text-[#e5e7eb]">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-[#A1A09A]">
                                    {{ user.email }}
                                </p>
                            </div>

                            <!-- Badges -->
                            <div class="flex items-center gap-2">
                                <span
                                    :class="getRoleBadgeColor(user.role)"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                >
                                    {{ getRoleLabel(user.role) }}
                                </span>
                                <span
                                    :class="getStatusBadgeColor(user.is_active)"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                >
                                    {{ getStatusLabel(user.is_active) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div
                                v-if="user.id !== authStore.user?.id"
                                class="flex items-center gap-2 pt-2"
                            >
                                <button
                                    @click="openEditModal(user)"
                                    class="flex-1 rounded-lg bg-blue-500/20 px-4 py-2 text-sm font-medium text-blue-400 transition-colors hover:bg-blue-500/30"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="user.is_active"
                                    @click="openDeactivateModal(user)"
                                    class="flex-1 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-400 transition-colors hover:bg-red-500/30"
                                >
                                    Nonaktifkan
                                </button>
                            </div>

                            <!-- Self Indicator -->
                            <p
                                v-else
                                class="text-center text-xs text-[#6b7280] pt-2"
                            >
                                (Akun Anda)
                            </p>
                        </div>
                    </motion.div>

                    <!-- Empty State -->
                    <div
                        v-if="users.length === 0"
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] py-12 text-center"
                    >
                        <p class="text-sm text-[#6b7280]">
                            Tidak ada karyawan ditemukan.
                        </p>
                    </div>
                </div>

                <!-- ======================================================================
                    Pagination
                ======================================================================= -->
                <motion.div
                    v-if="meta.last_page > 1"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3, duration: 0.4 }"
                >
                    <Pagination
                        :current-page="meta.current_page"
                        :last-page="meta.last_page"
                        :total="meta.total"
                        @page-change="handlePageChange"
                    />
                </motion.div>
            </div>
        </div>

        <!-- ======================================================================
            Modals
        ======================================================================= -->
        <UserFormModal
            :show="showAddModal"
            @close="closeAddModal"
        />

        <UserFormModal
            v-if="editingUser"
            :show="showEditModal"
            :user="editingUser"
            @close="closeEditModal"
        />

        <ConfirmDeactivateModal
            v-if="deactivatingUser"
            :show="showDeactivateModal"
            :user="deactivatingUser"
            @close="closeDeactivateModal"
        />
    </SupervisorLayout>
</template>
