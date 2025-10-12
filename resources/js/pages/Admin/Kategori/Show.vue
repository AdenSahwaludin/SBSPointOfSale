<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Produk {
    id_produk: string;
    nama: string;
    sku: string;
    harga: number;
    stok: number;
}

interface Kategori {
    id_kategori: string;
    nama: string;
    created_at: string;
    updated_at: string;
    produk: Produk[];
}

interface Props {
    kategori: Kategori;
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/kategori');

function confirmDelete() {
    if (props.kategori.produk.length > 0) {
        return;
    }
    showDeleteModal.value = true;
}

function deleteKategori() {
    router.delete(`/admin/kategori/${props.kategori.id_kategori}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

function getStockBadgeClass(stok: number) {
    if (stok > 10) return 'bg-green-100 text-green-700 border-green-200';
    if (stok > 0) return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    return 'bg-red-100 text-red-700 border-red-200';
}
</script>

<template>
    <Head title="Detail Kategori - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Kategori</h1>
                    <p class="text-emerald-600">Informasi lengkap kategori {{ kategori.nama }}</p>
                </div>
                <div class="flex gap-2">
                    <BaseButton @click="$inertia.visit('/admin/kategori')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                    <BaseButton @click="$inertia.visit(`/admin/kategori/${kategori.id_kategori}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit
                    </BaseButton>
                    <BaseButton
                        @click="confirmDelete"
                        variant="danger"
                        icon="fas fa-trash"
                        :disabled="kategori.produk.length > 0"
                        :title="kategori.produk.length > 0 ? 'Tidak dapat menghapus kategori yang memiliki produk' : 'Hapus kategori'"
                    >
                        Hapus
                    </BaseButton>
                </div>
            </div>

            <!-- Category Information -->
            <div class="card-emerald">
                <h2 class="mb-4 text-lg font-medium text-emerald-800">Informasi Kategori</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <span class="text-sm font-medium text-emerald-600">ID Kategori:</span>
                        <div class="text-lg font-semibold text-emerald-800">{{ kategori.id_kategori }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-emerald-600">Nama Kategori:</span>
                        <div class="text-lg font-semibold text-emerald-800">{{ kategori.nama }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-emerald-600">Total Produk:</span>
                        <div class="text-lg font-semibold text-emerald-800">
                            <span
                                class="inline-flex items-center rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700"
                            >
                                {{ kategori.produk.length }} produk
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-emerald-600">Tanggal Dibuat:</span>
                        <div class="text-sm text-emerald-800">{{ formatDate(kategori.created_at) }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-emerald-600">Terakhir Diperbarui:</span>
                        <div class="text-sm text-emerald-800">{{ formatDate(kategori.updated_at) }}</div>
                    </div>
                </div>
            </div>

            <!-- Products in Category -->
            <div class="card-emerald">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-emerald-800">Produk dalam Kategori ({{ kategori.produk.length }})</h2>
                    <BaseButton @click="$inertia.visit('/admin/produk/create')" variant="primary" icon="fas fa-plus"> Tambah Produk </BaseButton>
                </div>

                <div v-if="kategori.produk.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Stok</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="produk in kategori.produk"
                                :key="produk.id_produk"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-box text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ produk.nama }}</div>
                                            <div class="text-sm text-emerald-500">ID: {{ produk.id_produk }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-800">
                                    {{ produk.sku }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-800">Rp {{ formatCurrency(produk.harga) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStockBadgeClass(produk.stok)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ produk.stok }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/produk/${produk.id_produk}`)"
                                            variant="outline"
                                            size="xs"
                                            icon="fas fa-eye"
                                            custom-class="rounded-lg p-2"
                                            title="Lihat Detail"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/produk/${produk.id_produk}/edit`)"
                                            variant="secondary"
                                            size="xs"
                                            icon="fas fa-edit"
                                            custom-class="rounded-lg p-2"
                                            title="Edit"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="py-12 text-center">
                    <i class="fas fa-box mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada produk</h3>
                    <p class="mb-4 text-emerald-600">Kategori ini belum memiliki produk. Mulai dengan menambahkan produk pertama.</p>
                    <BaseButton @click="$inertia.visit('/admin/produk/create')" variant="primary" icon="fas fa-plus"> Tambah Produk </BaseButton>
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
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus kategori "{{ kategori.nama }}"?</p>
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
