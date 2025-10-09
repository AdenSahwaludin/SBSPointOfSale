<template>
    <AdminLayout>
        <Head :title="`Detail Produk - ${produk.nama}`" />

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Detail Produk</h2>
                            <div class="space-x-2">
                                <Link
                                    :href="route('admin.produk.edit', produk.id_produk)"
                                    class="rounded bg-yellow-500 px-4 py-2 font-bold text-white hover:bg-yellow-700"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('admin.produk.index')"
                                    class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"
                                >
                                    Kembali
                                </Link>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <!-- Product Image and Basic Info -->
                            <div class="lg:col-span-1">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <!-- Product Image -->
                                    <div class="mb-4">
                                        <img
                                            v-if="produk.gambar"
                                            :src="`/storage/${produk.gambar}`"
                                            :alt="produk.nama"
                                            class="h-64 w-full rounded-lg object-cover"
                                        />
                                        <div v-else class="flex h-64 w-full items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-600">
                                            <span class="text-lg text-gray-500">No Image Available</span>
                                        </div>
                                    </div>

                                    <!-- Stock Status -->
                                    <div class="text-center">
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': produk.stok > produk.batas_stok,
                                                'bg-yellow-100 text-yellow-800': produk.stok <= produk.batas_stok && produk.stok > 0,
                                                'bg-red-100 text-red-800': produk.stok === 0,
                                            }"
                                            class="rounded-lg px-4 py-2 text-lg font-medium"
                                        >
                                            {{ produk.stok === 0 ? 'STOK HABIS' : produk.stok <= produk.batas_stok ? 'STOK MENIPIS' : 'STOK AMAN' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div class="lg:col-span-2">
                                <div class="space-y-6">
                                    <!-- Basic Information -->
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Dasar</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400"
                                                    >ID Produk (Barcode)</label
                                                >
                                                <p class="font-mono text-lg">{{ produk.id_produk }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Nama Produk</label>
                                                <p class="text-lg font-medium">{{ produk.nama }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Nomor BPOM</label>
                                                <p class="text-lg">{{ produk.nomor_bpom || '-' }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Kategori</label>
                                                <p class="text-lg">
                                                    <span v-if="produk.kategori" class="rounded-full bg-blue-100 px-3 py-1 text-blue-800">
                                                        {{ produk.kategori.nama }}
                                                    </span>
                                                    <span v-else>-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Information -->
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Harga</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Biaya Produk</label>
                                                <p class="text-lg font-medium text-gray-600">{{ formatPrice(produk.biaya_produk) }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Harga Jual</label>
                                                <p class="text-lg font-bold text-green-600">{{ formatPrice(produk.harga) }}</p>
                                            </div>
                                            <div v-if="produk.harga_pack">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Harga Pack</label>
                                                <p class="text-lg font-medium text-green-600">{{ formatPrice(produk.harga_pack) }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Margin</label>
                                                <p class="text-lg font-medium" :class="margin >= 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ formatPrice(margin) }} ({{ marginPercentage }}%)
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stock Information -->
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Stok</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Stok Saat Ini</label>
                                                <p class="text-2xl font-bold text-blue-600">{{ produk.stok }} {{ produk.satuan }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Batas Minimum</label>
                                                <p class="text-lg">{{ produk.batas_stok }} {{ produk.satuan }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Satuan</label>
                                                <p class="text-lg">{{ produk.satuan }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pack Information -->
                                    <div v-if="produk.satuan_pack || produk.isi_per_pack" class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Pack</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div v-if="produk.satuan_pack">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Satuan Pack</label>
                                                <p class="text-lg">{{ produk.satuan_pack }}</p>
                                            </div>
                                            <div v-if="produk.isi_per_pack">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Isi per Pack</label>
                                                <p class="text-lg">{{ produk.isi_per_pack }} {{ produk.satuan }}</p>
                                            </div>
                                            <div v-if="produk.harga_pack">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Harga per Pack</label>
                                                <p class="text-lg font-medium text-green-600">{{ formatPrice(produk.harga_pack) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discount Information -->
                                    <div
                                        v-if="produk.min_beli_diskon || produk.harga_diskon_unit || produk.harga_diskon_pack"
                                        class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700"
                                    >
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Diskon</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div v-if="produk.min_beli_diskon">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400"
                                                    >Minimum Beli untuk Diskon</label
                                                >
                                                <p class="text-lg">{{ produk.min_beli_diskon }} {{ produk.satuan }}</p>
                                            </div>
                                            <div v-if="produk.harga_diskon_unit">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400"
                                                    >Harga Diskon Unit</label
                                                >
                                                <p class="text-lg font-medium text-orange-600">{{ formatPrice(produk.harga_diskon_unit) }}</p>
                                            </div>
                                            <div v-if="produk.harga_diskon_pack">
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400"
                                                    >Harga Diskon Pack</label
                                                >
                                                <p class="text-lg font-medium text-orange-600">{{ formatPrice(produk.harga_diskon_pack) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timestamps -->
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <h3 class="mb-4 text-lg font-semibold">Informasi Sistem</h3>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Dibuat pada</label>
                                                <p class="text-lg">{{ formatDate(produk.created_at) }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-400">Diperbarui pada</label>
                                                <p class="text-lg">{{ formatDate(produk.updated_at) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div v-if="produk.transaksi_detail && produk.transaksi_detail.length > 0" class="mt-8">
                            <h3 class="mb-4 text-lg font-semibold">Riwayat Transaksi</h3>
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full rounded-lg bg-white dark:bg-gray-800">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-600">
                                                <th class="px-4 py-2 text-left">Tanggal</th>
                                                <th class="px-4 py-2 text-left">No. Transaksi</th>
                                                <th class="px-4 py-2 text-left">Jumlah</th>
                                                <th class="px-4 py-2 text-left">Harga Satuan</th>
                                                <th class="px-4 py-2 text-left">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="detail in produk.transaksi_detail.slice(0, 10)"
                                                :key="detail.id"
                                                class="border-b dark:border-gray-600"
                                            >
                                                <td class="px-4 py-2">{{ formatDate(detail.transaksi.tanggal_transaksi) }}</td>
                                                <td class="px-4 py-2 font-mono">{{ detail.transaksi.nomor_transaksi }}</td>
                                                <td class="px-4 py-2">{{ detail.jumlah }} {{ detail.satuan }}</td>
                                                <td class="px-4 py-2">{{ formatPrice(detail.harga_satuan) }}</td>
                                                <td class="px-4 py-2 font-medium">{{ formatPrice(detail.subtotal) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="produk.transaksi_detail.length > 10" class="mt-4 text-center">
                                    <p class="text-gray-600 dark:text-gray-400">
                                        Menampilkan 10 transaksi terakhir dari {{ produk.transaksi_detail.length }} total transaksi
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="mt-8">
                            <h3 class="mb-4 text-lg font-semibold">Statistik Produk</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div class="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                                    <p class="text-2xl font-bold text-blue-600">{{ totalTransactions }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Transaksi</p>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                                    <p class="text-2xl font-bold text-green-600">{{ totalSold }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Terjual</p>
                                </div>
                                <div class="rounded-lg bg-yellow-50 p-4 text-center dark:bg-yellow-900/20">
                                    <p class="text-2xl font-bold text-yellow-600">{{ formatPrice(totalRevenue) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Pendapatan</p>
                                </div>
                                <div class="rounded-lg bg-purple-50 p-4 text-center dark:bg-purple-900/20">
                                    <p class="text-2xl font-bold text-purple-600">{{ formatPrice(estimatedValue) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Nilai Stok</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    produk: Object,
});

// Computed properties
const margin = computed(() => {
    return props.produk.harga - props.produk.biaya_produk;
});

const marginPercentage = computed(() => {
    if (props.produk.biaya_produk === 0) return 0;
    return ((margin.value / props.produk.biaya_produk) * 100).toFixed(1);
});

const totalTransactions = computed(() => {
    return props.produk.transaksi_detail ? props.produk.transaksi_detail.length : 0;
});

const totalSold = computed(() => {
    if (!props.produk.transaksi_detail) return 0;
    return props.produk.transaksi_detail.reduce((total, detail) => total + detail.jumlah, 0);
});

const totalRevenue = computed(() => {
    if (!props.produk.transaksi_detail) return 0;
    return props.produk.transaksi_detail.reduce((total, detail) => total + detail.subtotal, 0);
});

const estimatedValue = computed(() => {
    return props.produk.stok * props.produk.biaya_produk;
});

// Methods
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
