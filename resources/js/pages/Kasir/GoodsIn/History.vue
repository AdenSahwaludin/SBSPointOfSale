<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Kasir {
    id_pengguna: number;
    name: string;
}

interface ProdukDetail {
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
    produk: ProdukDetail;
}

interface PoHistory {
    id_pemesanan_barang: number;
    nomor_po: string;
    status: string;
    tanggal_request: string;
    tanggal_approval?: string;
    id_kasir: number;
    kasir: Kasir;
    details: GoodsInDetail[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    current_page: number;
    data: PoHistory[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    pos: Pagination;
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

function goToPage(url: string) {
    router.get(url);
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

function getTotalItems(details: GoodsInDetail[]) {
    return details.reduce((sum, detail) => sum + detail.jumlah_dipesan, 0);
}
</script>

<template>
    <Head title="Riwayat PO - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Riwayat Purchase Order</h1>
                    <p class="text-emerald-600">Lihat riwayat semua PO yang telah dibuat</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="pos.data.length === 0" class="card-emerald py-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                        <i class="fas fa-inbox text-3xl text-emerald-600"></i>
                    </div>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-emerald-800">Tidak Ada Riwayat PO</h3>
                <p class="text-emerald-600">Belum ada Purchase Order yang diproses</p>
            </div>

            <!-- Table -->
            <div v-else class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nomor PO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-200">
                            <tr v-for="po in pos.data" :key="po.id_pemesanan_barang" class="hover:bg-emerald-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-900">{{ po.nomor_po }}</td>
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ formatDate(po.tanggal_request) }}</td>
                                <td class="px-6 py-4 text-sm text-emerald-700">{{ getTotalItems(po.details) }} item</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="['inline-flex rounded-full border px-3 py-1 text-xs font-semibold', getStatusBadgeClass(po.status)]"
                                    >
                                        <i :class="['mr-2', getStatusIcon(po.status)]"></i>
                                        {{ getStatusLabel(po.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <BaseButton
                                        @click="$inertia.visit(`/kasir/goods-in-history/${po.id_pemesanan_barang}`)"
                                        variant="outline"
                                        size="sm"
                                        icon="fas fa-eye"
                                    >
                                        Detail
                                    </BaseButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pos.data.length > 0" class="flex items-center justify-between gap-4">
                <div class="text-sm text-emerald-600">Menampilkan {{ pos.from }} hingga {{ pos.to }} dari {{ pos.total }} PO</div>
                <div class="flex gap-2">
                    <button
                        @click="goToPage(pos.prev_page_url)"
                        :disabled="!pos.prev_page_url"
                        :class="[
                            'rounded-lg px-3 py-1 text-sm',
                            pos.prev_page_url
                                ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100'
                                : 'cursor-not-allowed bg-gray-100 text-gray-400',
                        ]"
                    >
                        Previous
                    </button>

                    <button
                        v-for="(link, index) in pos.links.slice(1, -1)"
                        :key="index"
                        @click="goToPage(link.url)"
                        :class="[
                            'rounded-lg px-3 py-1 text-sm',
                            link.active ? 'bg-emerald-600 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100',
                        ]"
                        v-html="link.label"
                    ></button>

                    <button
                        @click="goToPage(pos.next_page_url)"
                        :disabled="!pos.next_page_url"
                        :class="[
                            'rounded-lg px-3 py-1 text-sm',
                            pos.next_page_url
                                ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100'
                                : 'cursor-not-allowed bg-gray-100 text-gray-400',
                        ]"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
