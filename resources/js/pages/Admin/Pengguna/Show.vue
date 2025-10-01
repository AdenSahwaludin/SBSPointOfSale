<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Pengguna {
    id_pengguna: string;
    nama: string;
    email: string;
    telepon?: string;
    role: 'admin' | 'kasir';
    terakhir_login?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    pengguna: Pengguna;
}

const props = defineProps<Props>();

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

function getRoleBadgeClass(role: string) {
    return role === 'admin' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-green-100 text-green-800 border-green-200';
}

function formatDate(dateString?: string) {
    if (!dateString) return 'Tidak tersedia';
    return new Date(dateString).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Detail Pengguna - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Pengguna</h1>
                    <p class="text-gray-600">Informasi lengkap pengguna</p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="`/admin/pengguna/${pengguna.id_pengguna}/edit`"
                        class="flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-white transition-colors hover:bg-yellow-700"
                    >
                        <i class="fas fa-edit"></i>
                        Edit
                    </Link>
                    <Link
                        href="/admin/pengguna"
                        class="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                    >
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- User Profile Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <!-- Header Profile -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-8">
                    <div class="flex items-center gap-6">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white">
                            <i class="fas fa-user text-3xl text-blue-600"></i>
                        </div>
                        <div class="text-white">
                            <h2 class="text-2xl font-bold">{{ pengguna.nama }}</h2>
                            <p class="mb-2 text-blue-100">{{ pengguna.id_pengguna }}</p>
                            <span
                                :class="getRoleBadgeClass(pengguna.role)"
                                class="inline-flex rounded-full border bg-white px-3 py-1 text-sm font-semibold"
                            >
                                {{ pengguna.role.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Contact Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900">Informasi Kontak</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                        <i class="fas fa-envelope text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium text-gray-900">{{ pengguna.email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <i class="fas fa-phone text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Telepon</p>
                                        <p class="font-medium text-gray-900">{{ pengguna.telepon || 'Tidak diisi' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                                        <i class="fas fa-id-badge text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">ID Pengguna</p>
                                        <p class="font-medium text-gray-900">{{ pengguna.id_pengguna }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900">Informasi Aktivitas</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100">
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Terakhir Login</p>
                                        <p class="font-medium text-gray-900">{{ formatDate(pengguna.terakhir_login) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                                        <i class="fas fa-user-plus text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Dibuat</p>
                                        <p class="font-medium text-gray-900">{{ formatDate(pengguna.created_at) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100">
                                        <i class="fas fa-edit text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Terakhir Diperbarui</p>
                                        <p class="font-medium text-gray-900">{{ formatDate(pengguna.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Aksi</h3>
                <div class="flex gap-3">
                    <Link
                        :href="`/admin/pengguna/${pengguna.id_pengguna}/edit`"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                    >
                        <i class="fas fa-edit"></i>
                        Edit Pengguna
                    </Link>

                    <!-- Reset Password (future feature) -->
                    <button
                        class="flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-white transition-colors hover:bg-yellow-700"
                        disabled
                        title="Fitur akan datang"
                    >
                        <i class="fas fa-key"></i>
                        Reset Password
                    </button>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
