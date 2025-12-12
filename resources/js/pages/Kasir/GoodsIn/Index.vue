<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface GoodsInDetail {
    id_goods_in_detail: number;
    id_produk: number;
    qty_request: number;
    qty_received: number;
    produk?: {
        nama: string;
        sku: string;
    };
}

interface GoodsIn {
    id_goods_in: number;
    nomor_po: string;
    status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'received';
    tanggal_request: string;
    tanggal_approval?: string;
    details?: GoodsInDetail[];
}

interface Props {
    pos: GoodsIn[];
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in');

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function getStatusBadgeClass(status: string) {
    const classes = {
        draft: 'bg-gray-100 text-gray-700 border-gray-200',
        submitted: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        approved: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        rejected: 'bg-red-100 text-red-700 border-red-200',
        received: 'bg-blue-100 text-blue-700 border-blue-200',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700 border-gray-200';
}

function getStatusLabel(status: string) {
    const labels = {
        draft: 'Draft',
        submitted: 'Diajukan',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        received: 'Diterima',
    };
    return labels[status as keyof typeof labels] || status;
}

function getItemsCount(goodsIn: GoodsIn) {
    return goodsIn.details?.length || 0;
}

function deletePO(goodsIn: GoodsIn) {
    if (!confirm(`Yakin ingin menghapus PO ${goodsIn.nomor_po}? Tindakan ini tidak bisa dibatalkan.`)) return;

    const form = useForm({});
    form.delete(`/kasir/goods-in/${goodsIn.id_goods_in}`);
}
</script>

<template>
    <Head title="Purchase Order - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Purchase Order (PO)</h1>
                    <p class="text-emerald-600">Kelola permintaan barang masuk</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/goods-in/create')" variant="primary" icon="fas fa-plus"> Buat PO Baru </BaseButton>
            </div>

            <!-- Info Card -->
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-xl text-emerald-600"></i>
                    <div class="text-sm text-emerald-700">
                        <p class="font-medium">Informasi Purchase Order</p>
                        <p class="mt-1">Buat permintaan pembelian barang untuk menambah stok. PO akan ditinjau dan disetujui oleh Admin.</p>
                    </div>
                </div>
            </div>

            <!-- PO List -->
            <div class="card-emerald overflow-hidden">
                <div v-if="pos.length === 0" class="p-12 text-center">
                    <i class="fas fa-inbox mb-4 text-5xl text-emerald-300"></i>
                    <p class="mb-2 text-lg font-medium text-emerald-800">Belum ada Purchase Order</p>
                    <p class="mb-6 text-emerald-600">Mulai dengan membuat PO baru untuk permintaan barang</p>
                    <BaseButton @click="$inertia.visit('/kasir/goods-in/create')" variant="primary" icon="fas fa-plus"> Buat PO Baru </BaseButton>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nomor PO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100">
                            <tr v-for="po in pos" :key="po.id_goods_in" class="transition-colors hover:bg-emerald-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-file-invoice text-emerald-600"></i>
                                        <span class="font-medium text-emerald-800">{{ po.nomor_po }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-700">{{ formatDate(po.tanggal_request) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusBadgeClass(po.status)"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium"
                                    >
                                        <i
                                            :class="{
                                                'fas fa-clock': po.status === 'submitted',
                                                'fas fa-check-circle': po.status === 'approved',
                                                'fas fa-times-circle': po.status === 'rejected',
                                                'fas fa-box': po.status === 'received',
                                            }"
                                        ></i>
                                        {{ getStatusLabel(po.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm text-emerald-700">
                                        <i class="fas fa-boxes text-emerald-500"></i>
                                        <span>{{ getItemsCount(po) }} item</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <BaseButton
                                        @click="$inertia.visit(`/kasir/goods-in/${po.id_goods_in}`)"
                                        variant="outline"
                                        size="sm"
                                        icon="fas fa-eye"
                                    >
                                        Detail
                                    </BaseButton>
                                    <button
                                        v-if="po.status === 'draft'"
                                        @click="deletePO(po)"
                                        class="ml-2 text-red-600 transition-colors hover:text-red-700"
                                        title="Hapus PO"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
