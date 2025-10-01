<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Pengguna {
    id_pengguna: string;
    nama: string;
    email: string;
    telepon?: string;
    role: 'admin' | 'kasir';
    terakhir_login?: string;
}

interface Props {
    pengguna: Pengguna[];
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const deleteTarget = ref<Pengguna | null>(null);

const adminMenuItems = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'fas fa-tachometer-alt',
        active: false,
    },
    {
        name: 'Manajemen Data',
        icon: 'fas fa-database',
        children: [
            { name: 'Pengguna', href: '/admin/pengguna', icon: 'fas fa-users', active: true },
            { name: 'Produk', href: '/admin/products', icon: 'fas fa-boxes' },
            { name: 'Kategori', href: '/admin/categories', icon: 'fas fa-tags' },
        ],
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-cash-register',
        children: [
            { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
            { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
            { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
        ],
    },
    {
        name: 'Laporan',
        href: '/admin/reports',
        icon: 'fas fa-chart-bar',
    },
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: 'fas fa-cog',
    },
];

function confirmDelete(pengguna: Pengguna) {
    deleteTarget.value = pengguna;
    showDeleteModal.value = true;
}

function deletePengguna() {
    if (deleteTarget.value) {
        router.delete(`/admin/pengguna/${deleteTarget.value.id_pengguna}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
}

function getRoleBadgeClass(role: string) {
    return role === 'admin' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-green-100 text-green-800 border-green-200';
}

function formatDate(dateString?: string) {
    if (!dateString) return 'Belum pernah login';
    return new Date(dateString).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Manajemen Pengguna - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
                    <p class="text-gray-600">Kelola data pengguna sistem</p>
                </div>
                <Link
                    href="/admin/pengguna/create"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                >
                    <i class="fas fa-plus"></i>
                    Tambah Pengguna
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Terakhir Login</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="user in pengguna" :key="user.id_pengguna" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ user.nama }}</div>
                                            <div class="text-sm text-gray-500">{{ user.id_pengguna }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                                    <div class="text-sm text-gray-500">{{ user.telepon || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getRoleBadgeClass(user.role)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ user.role.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                    {{ formatDate(user.terakhir_login) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/pengguna/${user.id_pengguna}`"
                                            class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-800"
                                            title="Lihat Detail"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </Link>
                                        <Link
                                            :href="`/admin/pengguna/${user.id_pengguna}/edit`"
                                            class="rounded-lg p-2 text-yellow-600 transition-colors hover:bg-yellow-50 hover:text-yellow-800"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-800"
                                            title="Hapus"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="pengguna.length === 0" class="py-12 text-center">
                    <i class="fas fa-users mb-4 text-4xl text-gray-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-gray-900">Belum ada pengguna</h3>
                    <p class="mb-4 text-gray-500">Mulai dengan menambahkan pengguna pertama</p>
                    <Link
                        href="/admin/pengguna/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                    >
                        <i class="fas fa-plus"></i>
                        Tambah Pengguna
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="mx-4 max-w-md rounded-lg bg-white p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus pengguna ini?</p>
                    </div>
                </div>

                <div v-if="deleteTarget" class="mb-4 rounded-lg bg-gray-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">{{ deleteTarget.nama }}</div>
                        <div class="text-gray-500">{{ deleteTarget.email }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200"
                    >
                        Batal
                    </button>
                    <button @click="deletePengguna" class="rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
