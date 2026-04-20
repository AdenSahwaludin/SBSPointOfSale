<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Kasir {
    id_pengguna: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    satuan: string;
    sku: string;
}

interface GoodsInDetail {
    id_detail_pemesanan_barang: number;
    id_pemesanan_barang: number;
    id_produk: string;
    jumlah_dipesan: number;
    produk: Produk;
}

interface GoodsReceived {
    id_penerimaan_barang: number;
    id_pemesanan_barang: number;
    id_detail_pemesanan_barang: number;
    jumlah_diterima: number;
    jumlah_rusak: number;
    created_at: string;
    kasir: Kasir;
}

interface DetailSummary {
    total_diterima: number;
    total_rusak: number;
    total_good: number;
    receiving_batches: GoodsReceived[];
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
    detailSummaries: Record<number, DetailSummary>;
}

const props = defineProps<Props>();
const expandedDetails = ref<Record<number, boolean>>({});

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
            return 'fas fa-box-open';
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
            return 'Diterima Sepenuhnya';
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

function toggleDetailExpand(detailId: number) {
    expandedDetails.value[detailId] = !expandedDetails.value[detailId];
}

function getDetailStatusIcon(detail: GoodsInDetail, summary: DetailSummary | undefined) {
    if (!summary) return 'fas fa-circle-xmark text-gray-400';
    if (summary.total_diterima + summary.total_rusak >= detail.jumlah_dipesan) {
        return 'fas fa-check-circle text-green-500';
    } else if (summary.total_diterima + summary.total_rusak > 0) {
        return 'fas fa-minus-circle text-amber-500';
    } else {
        return 'fas fa-circle-xmark text-gray-400';
    }
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

            <!-- Items Table with Receiving Summary -->
            <div class="card-emerald overflow-hidden">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Daftar Item & Penerimaan</h3>
                <div class="space-y-4">
                    <div
                        v-for="detail in po?.details || []"
                        :key="detail.id_detail_pemesanan_barang"
                        class="overflow-hidden rounded-lg border border-emerald-200"
                    >
                        <!-- Detail Header -->
                        <div
                            class="flex cursor-pointer items-center justify-between bg-emerald-50 px-6 py-4 hover:bg-emerald-100"
                            @click="toggleDetailExpand(detail.id_detail_pemesanan_barang)"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div class="flex-shrink-0">
                                    <i :class="['text-lg', getDetailStatusIcon(detail, detailSummaries[detail.id_detail_pemesanan_barang])]"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-emerald-900">{{ detail.produk.nama }}</p>
                                    <p class="text-sm text-emerald-600">SKU: {{ detail.produk.sku }} | Satuan: {{ detail.produk.satuan }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-emerald-700">
                                        Dipesan: <span class="font-semibold">{{ detail.jumlah_dipesan }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <i
                                    :class="[
                                        'fas transition-transform',
                                        expandedDetails[detail.id_detail_pemesanan_barang] ? 'fa-chevron-up' : 'fa-chevron-down',
                                    ]"
                                ></i>
                            </div>
                        </div>

                        <!-- Receiving Batches -->
                        <div
                            v-if="
                                expandedDetails[detail.id_detail_pemesanan_barang] &&
                                detailSummaries[detail.id_detail_pemesanan_barang]?.receiving_batches.length
                            "
                            class="border-t border-emerald-200 bg-white px-6 py-4"
                        >
                            <p class="mb-3 text-sm font-semibold text-emerald-700">Detail Penerimaan:</p>
                            <div class="space-y-2">
                                <div
                                    v-for="batch in detailSummaries[detail.id_detail_pemesanan_barang].receiving_batches"
                                    :key="batch.id_penerimaan_barang"
                                    class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-2 text-sm"
                                >
                                    <div class="flex flex-col gap-1">
                                        <div class="font-medium text-emerald-800">{{ formatDate(batch.created_at) }}</div>
                                        <div class="text-xs text-emerald-600">Kasir: {{ batch.kasir?.nama || 'N/A' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold text-green-600">{{ batch.jumlah_diterima }} baik</span>
                                        <span class="mx-2 text-emerald-400">·</span>
                                        <span class="font-semibold" :class="batch.jumlah_rusak > 0 ? 'text-red-600' : 'text-emerald-700'"
                                            >{{ batch.jumlah_rusak }} rusak</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else-if="
                                expandedDetails[detail.id_detail_pemesanan_barang] &&
                                !detailSummaries[detail.id_detail_pemesanan_barang]?.receiving_batches.length
                            "
                            class="border-t border-emerald-200 bg-white px-6 py-4 text-center text-sm text-emerald-600"
                        >
                            Belum ada penerimaan untuk item ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Receiving Goods Summary Table (for reference) -->
            <div v-if="po?.receivedGoods && po.receivedGoods.length > 0" class="card-emerald overflow-hidden">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Riwayat Semua Penerimaan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Terima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Diterima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Rusak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kasir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-200">
                            <tr v-for="received in po.receivedGoods" :key="received.id_penerimaan_barang" class="hover:bg-emerald-50">
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ formatDate(received.created_at) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-green-600">{{ received.jumlah_diterima }}</td>
                                <td class="px-6 py-4 text-sm font-medium" :class="received.jumlah_rusak > 0 ? 'text-red-600' : 'text-emerald-700'">
                                    {{ received.jumlah_rusak }}
                                </td>
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ received.kasir?.nama || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
