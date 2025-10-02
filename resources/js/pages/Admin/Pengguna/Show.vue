<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

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
    return role === 'admin' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-emerald-200 text-emerald-800 border-emerald-300';
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
                    <BaseButton @click="$inertia.visit(`/admin/pengguna/${pengguna.id_pengguna}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit
                    </BaseButton>
                    <BaseButton @click="$inertia.visit('/admin/pengguna')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                </div>
            </div>

            <!-- User Profile Card -->
            <div class="shadow-emerald overflow-hidden rounded-lg border border-emerald-200 bg-white-emerald">
                <!-- Header Profile -->
                <div class="bg-emerald-gradient-subtle-2 px-6 py-8">
                    <div class="flex items-center gap-6">
                        <div class="shadow-emerald flex h-20 w-20 items-center justify-center rounded-full bg-white-emerald">
                            <i class="fas fa-user text-3xl text-emerald-600"></i>
                        </div>
                        <div class="text-white">
                            <h2 class="text-2xl font-bold drop-shadow-sm">{{ pengguna.nama }}</h2>
                            <p class="mb-2 text-emerald-100 drop-shadow-sm">{{ pengguna.id_pengguna }}</p>
                            <span
                                :class="getRoleBadgeClass(pengguna.role)"
                                class="shadow-emerald-sm inline-flex rounded-full border bg-white-emerald px-3 py-1 text-sm font-semibold"
                            >
                                {{ pengguna.role.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="bg-white-emerald p-6">
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
            <div class="shadow-emerald rounded-lg border border-emerald-200 bg-white-emerald p-6">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Aksi</h3>
                <div class="flex gap-3">
                    <BaseButton @click="$inertia.visit(`/admin/pengguna/${pengguna.id_pengguna}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit Pengguna
                    </BaseButton>

                    <!-- Reset Password (future feature) -->
                    <BaseButton variant="secondary" icon="fas fa-key" :disabled="true" title="Fitur akan datang"> Reset Password </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
