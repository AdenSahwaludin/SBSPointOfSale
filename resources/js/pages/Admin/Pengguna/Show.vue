<script lang="ts" setup>
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
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

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pengguna');

function getRoleBadgeClass(role: string) {
    return role === 'admin' 
        ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
        : 'bg-emerald-200 text-emerald-800 border-emerald-300';
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
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Pengguna</h1>
                    <p class="text-emerald-600">Informasi lengkap pengguna</p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="`/admin/pengguna/${pengguna.id_pengguna}/edit`"
                        class="flex items-center gap-2 rounded-lg bg-emerald-gradient-subtle-1 px-4 py-2 text-white transition-all hover:scale-105 hover:shadow-emerald emerald-transition emerald-hover-glow"
                    >
                        <i class="fas fa-edit"></i>
                        Edit
                    </Link>
                    <Link
                        href="/admin/pengguna"
                        class="flex items-center gap-2 rounded-lg bg-gray-soft-100 px-4 py-2 text-emerald-700 transition-all hover:bg-gray-soft-200 hover:scale-105 emerald-transition"
                    >
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- User Profile Card -->
            <div class="overflow-hidden rounded-lg border border-emerald-200 bg-white-emerald shadow-emerald">
                <!-- Header Profile -->
                <div class="bg-emerald-gradient-subtle-2 px-6 py-8">
                    <div class="flex items-center gap-6">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white-emerald shadow-emerald">
                            <i class="fas fa-user text-3xl text-emerald-600"></i>
                        </div>
                        <div class="text-white">
                            <h2 class="text-2xl font-bold drop-shadow-sm">{{ pengguna.nama }}</h2>
                            <p class="mb-2 text-emerald-100 drop-shadow-sm">{{ pengguna.id_pengguna }}</p>
                            <span
                                :class="getRoleBadgeClass(pengguna.role)"
                                class="inline-flex rounded-full border bg-white-emerald px-3 py-1 text-sm font-semibold shadow-emerald-sm"
                            >
                                {{ pengguna.role.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="p-6 bg-white-emerald">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Contact Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-emerald-200 pb-2 text-lg font-semibold text-emerald-800">Informasi Kontak</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                        <i class="fas fa-envelope text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Email</p>
                                        <p class="font-medium text-emerald-800">{{ pengguna.email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-200">
                                        <i class="fas fa-phone text-emerald-700"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Telepon</p>
                                        <p class="font-medium text-emerald-800">{{ pengguna.telepon || 'Tidak diisi' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-300">
                                        <i class="fas fa-id-badge text-emerald-800"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">ID Pengguna</p>
                                        <p class="font-medium text-emerald-800">{{ pengguna.id_pengguna }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-emerald-200 pb-2 text-lg font-semibold text-emerald-800">Informasi Aktivitas</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                        <i class="fas fa-clock text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Terakhir Login</p>
                                        <p class="font-medium text-emerald-800">{{ formatDate(pengguna.terakhir_login) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-200">
                                        <i class="fas fa-user-plus text-emerald-700"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Dibuat</p>
                                        <p class="font-medium text-emerald-800">{{ formatDate(pengguna.created_at) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-300">
                                        <i class="fas fa-edit text-emerald-800"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Terakhir Diperbarui</p>
                                        <p class="font-medium text-emerald-800">{{ formatDate(pengguna.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="rounded-lg border border-emerald-200 bg-white-emerald p-6 shadow-emerald">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Aksi</h3>
                <div class="flex gap-3">
                    <Link
                        :href="`/admin/pengguna/${pengguna.id_pengguna}/edit`"
                        class="flex items-center gap-2 rounded-lg bg-emerald-gradient-subtle-2 px-4 py-2 text-white transition-all hover:scale-105 hover:shadow-emerald emerald-transition emerald-hover-glow"
                    >
                        <i class="fas fa-edit"></i>
                        Edit Pengguna
                    </Link>

                    <!-- Reset Password (future feature) -->
                    <button
                        class="flex items-center gap-2 rounded-lg bg-gray-soft-200 px-4 py-2 text-gray-soft-600 transition-all cursor-not-allowed opacity-50"
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
