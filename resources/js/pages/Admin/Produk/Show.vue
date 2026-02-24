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
    sku?: string;
    barcode?: string;
    isi_per_pack?: number;
    deskripsi?: string;
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
    if (stok > 10) return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (stok > 0) return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    return 'bg-red-100 text-red-700 border-red-200';
}

function getStockStatus(stok: number) {
    if (stok > 10) return 'Stok Tersedia';
    if (stok > 0) return 'Stok Rendah';
    return 'Stok Habis';
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function getPackPrice(p: Produk): number {
    const hargaPack = Number(p.harga_pack || 0);
    if (hargaPack > 0) return hargaPack;
    const unit = String(p.satuan || '').toLowerCase();
    if ((unit === 'karton' || unit === 'pack') && p.harga) return Number(p.harga);
    return 0;
}

function getPerUnitPrice(p: Produk): number {
    const unit = String(p.satuan || '').toLowerCase();
    const isi = Number(p.isi_per_pack || 0);
    const harga = Number(p.harga || 0);
    if ((unit === 'karton' || unit === 'pack') && isi > 0) {
        const packPrice = getPackPrice(p);
        return packPrice > 0 ? Math.round(packPrice / isi) : 0;
    }
    if (harga > 0) return harga;
    if (isi > 0) {
        const packPrice = getPackPrice(p);
        if (packPrice > 0) return Math.round(packPrice / isi);
    }
    return 0;
}

function hasPack(p: Produk): boolean {
    const unit = String(p.satuan || '').toLowerCase();
    const isi = Number(p.isi_per_pack || 0);
    const packPrice = getPackPrice(p);
    return Boolean(isi > 1 && packPrice > 0 && (unit === 'karton' || unit === 'pack' || p.harga_pack));
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

            <!-- Product Detail Card -->
            <div class="max-w-4xl space-y-4">
                <!-- SKU & Product Header -->
                <div class="rounded-lg border border-blue-100 bg-gradient-to-br from-blue-50 to-cyan-50 p-4">
                    <p class="text-xs font-semibold tracking-wide text-blue-600 uppercase">
                        {{ produk.sku || produk.id_produk }}
                    </p>
                    <h3 class="mt-2 text-base font-bold text-gray-900">{{ produk.nama }}</h3>
                </div>

                <!-- Basic Info Grid -->
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Kategori</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ produk.kategori?.nama || '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Satuan</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ produk.satuan }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Barcode</p>
                        <p class="mt-1 font-mono text-sm text-gray-900">{{ produk.barcode || '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Isi Per Pack</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ produk.isi_per_pack || '-' }}</p>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Harga</p>
                    <div class="mt-3 space-y-2">
                        <div v-if="hasPack(produk)" class="flex items-center justify-between">
                            <span class="text-sm text-emerald-700">Per Pack ({{ produk.isi_per_pack }})</span>
                            <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPackPrice(produk)) }}</span>
                        </div>
                        <div v-if="hasPack(produk)" class="flex items-center justify-between border-t border-emerald-200 pt-2">
                            <span class="text-sm text-emerald-700">Per Satuan</span>
                            <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(produk)) }}</span>
                        </div>
                        <div v-else class="flex items-center justify-between">
                            <span class="text-sm text-emerald-700">Per {{ produk.satuan }}</span>
                            <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(produk)) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock Section -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold text-gray-700 uppercase">Ketersediaan</p>
                    <div class="mt-3 flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ produk.stok }}</p>
                            <p class="mt-1 text-xs text-gray-600">{{ produk.satuan }} tersedia</p>
                        </div>
                        <span :class="['inline-flex rounded-full px-4 py-2 text-xs font-bold', getStockBadgeClass(produk.stok)]">
                            {{ getStockStatus(produk.stok) }}
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="produk.deskripsi" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold text-gray-700 uppercase">Deskripsi</p>
                    <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ produk.deskripsi }}</p>
                </div>

                <!-- Timestamps -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Dibuat Pada</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(produk.created_at) }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-600">Diupdate Pada</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(produk.updated_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
                <BaseButton @click="$inertia.visit(`/admin/produk/${produk.id_produk}/edit`)" variant="primary" icon="fas fa-edit">
                    Edit Produk
                </BaseButton>
                <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary" icon="fas fa-arrow-left">
                    Kembali
                </BaseButton>
            </div>
        </div>
    </BaseLayout>
</template>
