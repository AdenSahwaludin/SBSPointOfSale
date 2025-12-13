<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Kasir {
    id_pengguna: number;
    name: string;
}

interface ProdukDetail {
    id_produk: string;
    nama: string;
}

interface GoodsInDetail {
    id_goods_in_detail: number;
    id_goods_in: number;
    id_produk: string;
    qty_request: number;
    produk: ProdukDetail;
}

interface PoHistory {
    id_goods_in: number;
    nomor_po: string;
    status: string;
    tanggal_request: string;
    tanggal_approval?: string;
    id_kasir: number;
    kasir: Kasir;
    details: GoodsInDetail[];
}

interface Props {
    po: PoHistory;
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/goods-in-history');

function goBack() {
    router.visit('/admin/goods-in-history');
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
    return props.po.details.reduce((sum, detail) => sum + detail.qty_request, 0);
}

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
            return 'fas fa-inbox';
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

function getStatusDescription(status: string) {
    switch (status) {
        case 'approved':
            return 'Permohonan telah disetujui dan menunggu penerimaan barang';
        case 'rejected':
            return 'Permohonan telah ditolak oleh supervisor';
        case 'received':
            return 'Semua barang telah diterima dengan lengkap';
        case 'partial_received':
            return 'Sebagian barang telah diterima, sebagian masih dalam proses';
        case 'draft':
            return 'Permohonan masih dalam draf';
        default:
            return 'Status tidak diketahui';
    }
}
</script>

<template>
    <Head :title="`Detail PO ${po.nomor_po} - Admin`" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Back Button -->
            <div class="flex items-center gap-2">
                <BaseButton @click="goBack" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Header -->
            <div class="card-emerald">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-emerald-800">{{ po.nomor_po }}</h1>
                        <p class="text-emerald-600">Detail Purchase Order</p>
                    </div>
                    <span :class="['inline-flex rounded-full border px-4 py-2 text-sm font-semibold', getStatusBadgeClass(po.status)]">
                        <i :class="['mr-2', getStatusIcon(po.status)]"></i>
                        {{ getStatusLabel(po.status) }}
                    </span>
                </div>
            </div>

            <!-- Status Info -->
            <div class="card-emerald">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <i class="fas fa-info-circle text-emerald-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-emerald-800">{{ getStatusLabel(po.status) }}</h3>
                        <p class="text-sm text-emerald-600">{{ getStatusDescription(po.status) }}</p>
                    </div>
                </div>
            </div>

            <!-- PO Information Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Nomor PO -->
                <div class="card-emerald">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-emerald-600">Nomor PO</h4>
                            <p class="mt-1 text-lg font-semibold text-emerald-800">{{ po.nomor_po }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <i class="fas fa-file-invoice text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Kasir -->
                <div class="card-emerald">
                    <div>
                        <h4 class="text-sm font-medium text-emerald-600">Kasir / Peminta</h4>
                        <p class="mt-1 text-lg font-semibold text-emerald-800">{{ po.kasir.name }}</p>
                        <p class="mt-0.5 text-sm text-emerald-600">ID: {{ po.kasir.id_pengguna }}</p>
                    </div>
                </div>

                <!-- Total Items -->
                <div class="card-emerald">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-emerald-600">Total Item</h4>
                            <p class="mt-1 text-lg font-semibold text-emerald-800">{{ po.details.length }}</p>
                            <p class="mt-0.5 text-sm text-emerald-600">Total qty: {{ getTotalQuantity() }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <i class="fas fa-boxes text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Request -->
                <div class="card-emerald">
                    <div>
                        <h4 class="text-sm font-medium text-emerald-600">Tanggal Request</h4>
                        <p class="mt-1 text-lg font-semibold text-emerald-800">{{ formatDate(po.tanggal_request) }}</p>
                    </div>
                </div>

                <!-- Tanggal Approval -->
                <div v-if="po.tanggal_approval" class="card-emerald">
                    <div>
                        <h4 class="text-sm font-medium text-emerald-600">Tanggal Approval</h4>
                        <p class="mt-1 text-lg font-semibold text-emerald-800">{{ formatDate(po.tanggal_approval) }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card-emerald overflow-hidden">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Daftar Item</h3>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">ID Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama Produk</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Qty Request</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white">
                            <tr
                                v-for="(detail, index) in po.details"
                                :key="detail.id_goods_in_detail"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ index + 1 }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ detail.id_produk }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ detail.produk.nama }}</div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                                        {{ detail.qty_request }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t border-emerald-200 bg-emerald-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-emerald-800">Total Quantity:</td>
                                <td class="px-6 py-3 text-right">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                                        {{ getTotalQuantity() }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
