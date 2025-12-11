<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
    currentUserId: string;
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const deleteTarget = ref<Pengguna | null>(null);

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pengguna');

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
    return role === 'admin' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-blue-100 text-blue-700 border-blue-200';
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
                    <h1 class="text-2xl font-bold text-emerald-800">Manajemen Pengguna</h1>
                    <p class="text-emerald-600">Kelola data pengguna sistem</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/pengguna/create')" variant="primary" icon="fas fa-plus"> Tambah Pengguna </BaseButton>
            </div>

            <!-- Table -->
            <div class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Terakhir Login</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="user in pengguna"
                                :key="user.id_pengguna"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-user text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ user.nama }}</div>
                                            <div class="text-sm text-emerald-500">{{ user.id_pengguna }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ user.email }}</div>
                                    <div class="text-sm text-emerald-500">{{ user.telepon || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getRoleBadgeClass(user.role)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ user.role.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-600">
                                    {{ formatDate(user.terakhir_login) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/pengguna/${user.id_pengguna}`)"
                                            variant="outline"
                                            size="xs"
                                            icon="fas fa-eye"
                                            custom-class="rounded-lg p-2"
                                            title="Lihat Detail"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/pengguna/${user.id_pengguna}/edit`)"
                                            variant="secondary"
                                            size="xs"
                                            icon="fas fa-edit"
                                            custom-class="rounded-lg p-2"
                                            title="Edit"
                                        />
                                        <BaseButton
                                            @click="confirmDelete(user)"
                                            :disabled="user.id_pengguna === props.currentUserId"
                                            variant="danger"
                                            size="xs"
                                            icon="fas fa-trash"
                                            custom-class="rounded-lg p-2"
                                            :title="user.id_pengguna === props.currentUserId ? 'Tidak bisa menghapus akun sendiri' : 'Hapus'"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->    
                <div v-if="pengguna.length === 0" class="py-12 text-center">
                    <i class="fas fa-users mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada pengguna</h3>
                    <p class="mb-4 text-emerald-600">Mulai dengan menambahkan pengguna pertama</p>
                    <BaseButton @click="$inertia.visit('/admin/pengguna/create')" variant="primary" icon="fas fa-plus"> Tambah Pengguna </BaseButton>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[2px]">
            <div class="shadow-emerald-xl mx-4 max-w-md rounded-lg bg-white-emerald p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-emerald-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus pengguna ini?</p>
                    </div>
                </div>

                <div v-if="deleteTarget" class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-emerald-800">{{ deleteTarget.nama }}</div>
                        <div class="text-emerald-600">{{ deleteTarget.email }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <BaseButton @click="showDeleteModal = false" variant="secondary"> Batal </BaseButton>
                    <BaseButton @click="deletePengguna" variant="danger"> Hapus </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
