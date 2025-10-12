<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Kategori {
    id_kategori: string;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    harga: number;
    stok: number;
    satuan: string;
    kategori?: Kategori;
    created_at: string;
    updated_at: string;
}

interface Props {
    produk: Produk;
}

const props = defineProps<Props>();

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/produk');

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleString('id-ID');
}

function getStockBadgeClass(stok: number) {
    if (stok > 10) return 'bg-green-100 text-green-700 border-green-200';
    if (stok > 0) return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    return 'bg-red-100 text-red-700 border-red-200';
}
</script>

<template>
    <Head title="Detail Produk - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Produk</h1>
                    <p class="text-emerald-600">Informasi lengkap produk</p>
                </div>
                <div class="flex gap-3">
                    <BaseButton @click="$inertia.visit(`/admin/produk/${produk.id_produk}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit
                    </BaseButton>
                    <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                </div>
            </div>

            <!-- Product Profile Card -->
            <div class="shadow-emerald overflow-hidden rounded-lg border border-emerald-200 bg-white-emerald">
                <!-- Header Profile -->
                <div class="bg-emerald-gradient-subtle-2 px-6 py-8">
                    <div class="flex items-center gap-6">
                        <div class="shadow-emerald flex h-20 w-20 items-center justify-center rounded-full bg-white-emerald">
                            <i class="fas fa-box text-3xl text-emerald-600"></i>
                        </div>
                        <div class="text-white">
                            <h2 class="text-2xl font-bold drop-shadow-sm">{{ produk.nama }}</h2>
                            <p class="mb-2 text-emerald-100 drop-shadow-sm">{{ produk.id_produk }}</p>
                            <span
                                v-if="produk.kategori"
                                class="shadow-emerald-sm inline-flex rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700"
                            >
                                {{ produk.kategori.nama }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="bg-white-emerald p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-emerald-200 pb-2 text-lg font-semibold text-emerald-800">Informasi Dasar</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                        <i class="fas fa-barcode text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">ID Produk</p>
                                        <p class="font-medium text-emerald-800">{{ produk.id_produk }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-200">
                                        <i class="fas fa-box text-emerald-700"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Nama Produk</p>
                                        <p class="font-medium text-emerald-800">{{ produk.nama }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-300">
                                        <i class="fas fa-tags text-emerald-800"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Kategori</p>
                                        <p class="font-medium text-emerald-800">{{ produk.kategori?.nama || 'Tidak ada kategori' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Stock Information -->
                        <div class="space-y-4">
                            <h3 class="border-b border-emerald-200 pb-2 text-lg font-semibold text-emerald-800">Harga & Stok</h3>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                        <i class="fas fa-money-bill text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Harga</p>
                                        <p class="font-medium text-emerald-800">{{ formatPrice(produk.harga) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-200">
                                        <i class="fas fa-cubes text-emerald-700"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Stok</p>
                                        <div class="flex items-center gap-2">
                                            <span
                                                :class="getStockBadgeClass(produk.stok)"
                                                class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ produk.stok }} {{ produk.satuan }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-300">
                                        <i class="fas fa-balance-scale text-emerald-800"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-emerald-500">Satuan</p>
                                        <p class="font-medium text-emerald-800">{{ produk.satuan }}</p>
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
                    <BaseButton @click="$inertia.visit(`/admin/produk/${produk.id_produk}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit Produk
                    </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
