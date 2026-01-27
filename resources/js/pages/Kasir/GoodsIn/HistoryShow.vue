<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Kasir {
    id_pengguna: number;
    name: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    satuan: string;
    sku: string;
}

interface GoodsInDetail {
    id_goods_in_detail: number;
    id_pemesanan_barang: number;
    id_produk: string;
    jumlah_dipesan: number;
    produk: Produk;
}

interface GoodsReceived {
    id_penerimaan_barang: number;
    id_pemesanan_barang: number;
    jumlah_diterima: number;
    jumlah_rusak: number;
    tanggal_terima: string;
}

interface GoodsIn {
    id_pemesanan_barang: number;
    nomor_po: string;
    status: string;
    tanggal_request: string;
    tanggal_approval?: string;
    catatan_approval?: string;
    id_kasir: number;
    kasir: Kasir;
    details: GoodsInDetail[];
    receivedGoods: GoodsReceived[];
}

interface Props {
    po: GoodsIn;
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in-history');

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'received':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'partial_received':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'draft':
            return 'bg-gray-100 text-gray-800 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

function getStatusIcon(status: string) {
    switch (status) {
        case 'approved':
            return 'fas fa-check-circle';
        case 'rejected':
            return 'fas fa-times-circle';
        case 'received':
            return 'fas fa-box';
        case 'partial_received':
            return 'fas fa-boxes';
        case 'draft':
            return 'fas fa-ban';
        default:
            return 'fas fa-file-invoice';
    }
}

function getStatusLabel(status: string) {
    switch (status) {
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'received':
            return 'Diterima';
        case 'partial_received':
            return 'Diterima Sebagian';
        case 'draft':
            return 'Draf';
        default:
            return status;
    }
}

function goBack() {
    window.history.back();
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

function getTotalQuantity() {
    return props.po?.details?.reduce((sum, detail) => sum + detail.jumlah_dipesan, 0) || 0;
}

function getTotalReceived() {
    return props.po?.receivedGoods?.reduce((sum, received) => sum + received.jumlah_diterima, 0) || 0;
}
</script>

<template>
    <Head :title="`Detail PO ${po.nomor_po} - Kasir`" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Purchase Order</h1>
                    <p class="text-emerald-600">{{ po.nomor_po }}</p>
                </div>
                <BaseButton @click="goBack" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- PO Information -->
            <div class="card-emerald">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi PO</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Nomor PO</label>
                        <div class="flex items-center">
                            <div class="mr-3 h-10 w-10 flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                    <i class="fas fa-file-invoice text-emerald-600"></i>
                                </div>
                            </div>
                            <div class="text-lg font-medium text-emerald-800">{{ po.nomor_po }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Status</label>
                        <span :class="['inline-flex rounded-full border px-4 py-2 text-sm font-semibold', getStatusBadgeClass(po.status)]">
                            <i :class="['mr-2', getStatusIcon(po.status)]"></i>
                            {{ getStatusLabel(po.status) }}
                        </span>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Tanggal Request</label>
                        <p class="text-emerald-800">{{ formatDate(po.tanggal_request) }}</p>
                    </div>
                    <div v-if="po.tanggal_approval">
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Tanggal Approval</label>
                        <p class="text-emerald-800">{{ formatDate(po.tanggal_approval) }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Total Item</label>
                        <p class="text-2xl font-bold text-emerald-800">{{ getTotalQuantity() }}</p>
                    </div>
                    <div v-if="po?.receivedGoods && po.receivedGoods.length > 0">
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Jumlah Diterima</label>
                        <p class="text-2xl font-bold text-green-600">{{ getTotalReceived() }}</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Approval -->
            <div v-if="po.catatan_approval" class="card-emerald">
                <h3 class="mb-3 text-lg font-semibold text-emerald-800">Catatan Approval</h3>
                <p class="rounded-lg bg-emerald-50 p-4 whitespace-pre-wrap text-emerald-800">{{ po.catatan_approval }}</p>
            </div>

            <!-- Items Table -->
            <div class="card-emerald overflow-hidden">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Daftar Item</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Dipesan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-200">
                            <tr v-for="detail in po?.details || []" :key="detail.id_goods_in_detail" class="hover:bg-emerald-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-900">{{ detail.produk.nama }}</td>
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ detail.produk.sku }}</td>
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ detail.produk.satuan }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-emerald-800">{{ detail.jumlah_dipesan }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Received Goods Table -->
            <div v-if="po?.receivedGoods && po.receivedGoods.length > 0" class="card-emerald overflow-hidden">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Barang yang Diterima</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Terima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Diterima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Rusak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-200">
                            <tr v-for="received in po.receivedGoods" :key="received.id_penerimaan_barang" class="hover:bg-emerald-50">
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ formatDate(received.tanggal_terima) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-green-600">{{ received.jumlah_diterima }}</td>
                                <td class="px-6 py-4 text-sm font-medium" :class="received.jumlah_rusak > 0 ? 'text-red-600' : 'text-emerald-700'">
                                    {{ received.jumlah_rusak }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
