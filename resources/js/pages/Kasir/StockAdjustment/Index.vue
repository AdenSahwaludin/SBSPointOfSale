<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

interface User {
    id_pengguna: number;
    nama: string;
}

interface Produk {
    id_produk: number;
    nama: string;
    sku: string;
    satuan: string;
}

interface StockAdjustment {
    id_adjustment: number;
    id_produk: number;
    tipe: string;
    qty_adjustment: number;
    alasan: string;
    tanggal_adjustment: string;
    produk: Produk;
    pengguna: User;
}

interface Props {
    adjustments: StockAdjustment[];
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/stock-adjustment');

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getTypeBadgeClass(type: string): string {
    const classes: Record<string, string> = {
        retur_pelanggan: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        retur_gudang: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        koreksi_plus: 'bg-blue-100 text-blue-700 border-blue-200',
        koreksi_minus: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        penyesuaian_opname: 'bg-purple-100 text-purple-700 border-purple-200',
        expired: 'bg-red-100 text-red-700 border-red-200',
        rusak: 'bg-orange-100 text-orange-700 border-orange-200',
    };
    return classes[type] || 'bg-gray-100 text-gray-700 border-gray-200';
}

function getTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        retur_pelanggan: 'Retur dari Pelanggan',
        retur_gudang: 'Retur ke Gudang',
        koreksi_plus: 'Koreksi Plus',
        koreksi_minus: 'Koreksi Minus',
        penyesuaian_opname: 'Penyesuaian Opname',
        expired: 'Barang Expired',
        rusak: 'Barang Rusak',
    };
    return labels[type] || type;
}

function getQtyClass(qty: number): string {
    return qty >= 0 ? 'text-emerald-600 font-semibold' : 'text-red-600 font-semibold';
}

function formatQty(qty: number): string {
    return qty >= 0 ? `+${qty}` : `${qty}`;
}
</script>

<template>
    <Head title="Penyesuaian Stok - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Penyesuaian Stok</h1>
                    <p class="text-emerald-600">Kelola adjustment stok barang</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/stock-adjustment/create')" variant="primary" icon="fas fa-plus">
                    Buat Adjustment
                </BaseButton>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-emerald-100 bg-white shadow-sm">
                <!-- Desktop View -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full">
                        <thead class="border-b border-emerald-100 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-700 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-700 uppercase">Tipe Adjustment</th>
                                <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-emerald-700 uppercase">Qty Adjustment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-700 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-700 uppercase">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-700 uppercase">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-50 bg-white">
                            <tr v-if="adjustments.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox mb-2 text-4xl text-gray-300"></i>
                                    <p>Belum ada data penyesuaian stok</p>
                                </td>
                            </tr>
                            <tr v-for="adjustment in adjustments" :key="adjustment.id_adjustment" class="transition-colors hover:bg-emerald-50/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ adjustment.produk.nama }}</div>
                                        <div class="text-xs text-gray-500">{{ adjustment.produk.sku }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getTypeBadgeClass(adjustment.tipe)"
                                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ getTypeLabel(adjustment.tipe) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="getQtyClass(adjustment.qty_adjustment)" class="text-sm">
                                        {{ formatQty(adjustment.qty_adjustment) }} {{ adjustment.produk.satuan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(adjustment.tanggal_adjustment) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ adjustment.pengguna.nama }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs truncate text-sm text-gray-700" :title="adjustment.alasan">
                                        {{ adjustment.alasan || '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View -->
                <div class="md:hidden">
                    <div v-if="adjustments.length === 0" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox mb-2 text-4xl text-gray-300"></i>
                        <p>Belum ada data penyesuaian stok</p>
                    </div>
                    <div v-else class="divide-y divide-emerald-50">
                        <div v-for="adjustment in adjustments" :key="adjustment.id_adjustment" class="p-4 transition-colors hover:bg-emerald-50/50">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ adjustment.produk.nama }}</div>
                                        <div class="text-xs text-gray-500">{{ adjustment.produk.sku }}</div>
                                    </div>
                                    <span
                                        :class="getTypeBadgeClass(adjustment.tipe)"
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium whitespace-nowrap"
                                    >
                                        {{ getTypeLabel(adjustment.tipe) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Qty Adjustment:</span>
                                    <span :class="getQtyClass(adjustment.qty_adjustment)">
                                        {{ formatQty(adjustment.qty_adjustment) }} {{ adjustment.produk.satuan }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Tanggal:</span>
                                    <span class="text-gray-900">{{ formatDate(adjustment.tanggal_adjustment) }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Pengguna:</span>
                                    <span class="text-gray-900">{{ adjustment.pengguna.nama }}</span>
                                </div>

                                <div v-if="adjustment.alasan" class="text-sm">
                                    <span class="text-gray-500">Alasan:</span>
                                    <p class="mt-1 text-gray-700">{{ adjustment.alasan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Button for Mobile -->
            <div class="fixed right-6 bottom-6 md:hidden">
                <BaseButton
                    @click="$inertia.visit('/kasir/stock-adjustment/create')"
                    variant="primary"
                    icon="fas fa-plus"
                    class="rounded-full shadow-lg"
                >
                    Buat Adjustment
                </BaseButton>
            </div>
        </div>
    </BaseLayout>
</template>
