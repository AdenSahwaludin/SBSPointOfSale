<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Kategori {
    id_kategori: string;
    nama: string;
    produk_count: number;
    created_at: string;
}

interface PaginatedData {
    data: Kategori[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

interface Props {
    kategori: PaginatedData;
    filters: {
        search?: string;
        sort_by: string;
        sort_order: string;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const deleteTarget = ref<Kategori | null>(null);

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/kategori');

function confirmDelete(kategori: Kategori) {
    deleteTarget.value = kategori;
    showDeleteModal.value = true;
}

function deleteKategori() {
    if (deleteTarget.value) {
        router.delete(`/admin/kategori/${deleteTarget.value.id_kategori}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID');
}
</script>

<template>
    <Head title="Manajemen Kategori - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Manajemen Kategori</h1>
                    <p class="text-emerald-600">Kelola kategori produk</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/kategori/create')" variant="primary" icon="fas fa-plus"> Tambah Kategori </BaseButton>
            </div>

            <!-- Table -->
            <div class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">ID Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="item in kategori.data"
                                :key="item.id_kategori"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-tags text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ item.id_kategori }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ item.nama }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full border border-blue-200 bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700"
                                    >
                                        {{ item.produk_count }} produk
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-600">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/kategori/${item.id_kategori}`)"
                                            variant="outline"
                                            size="xs"
                                            icon="fas fa-eye"
                                            custom-class="rounded-lg p-2"
                                            title="Lihat Detail"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/kategori/${item.id_kategori}/edit`)"
                                            variant="secondary"
                                            size="xs"
                                            icon="fas fa-edit"
                                            custom-class="rounded-lg p-2"
                                            title="Edit"
                                        />
                                        <BaseButton
                                            @click="confirmDelete(item)"
                                            variant="danger"
                                            size="xs"
                                            icon="fas fa-trash"
                                            custom-class="rounded-lg p-2"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="kategori.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-tags mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada kategori</h3>
                    <p class="mb-4 text-emerald-600">Mulai dengan menambahkan kategori pertama</p>
                    <BaseButton @click="$inertia.visit('/admin/kategori/create')" variant="primary" icon="fas fa-plus"> Tambah Kategori </BaseButton>
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
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus kategori ini?</p>
                    </div>
                </div>

                <div v-if="deleteTarget" class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-emerald-800">{{ deleteTarget.nama }}</div>
                        <div class="text-emerald-600">ID: {{ deleteTarget.id_kategori }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <BaseButton @click="showDeleteModal = false" variant="secondary"> Batal </BaseButton>
                    <BaseButton @click="deleteKategori" variant="danger"> Hapus </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
