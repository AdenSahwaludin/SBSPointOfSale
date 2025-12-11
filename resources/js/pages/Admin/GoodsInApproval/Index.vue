<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

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

interface PendingPO {
    id_goods_in: number;
    nomor_po: string;
    status: string;
    tanggal_request: string;
    id_kasir: number;
    kasir: Kasir;
    details: GoodsInDetail[];
}

interface Props {
    pendingPOs: PendingPO[];
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/goods-in-approval');

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
    return details.reduce((sum, detail) => sum + detail.qty_request, 0);
}
</script>

<template>
    <Head title="Persetujuan Barang Masuk - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Persetujuan Barang Masuk</h1>
                    <p class="text-emerald-600">Kelola permintaan Purchase Order dari kasir</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="pendingPOs.length === 0" class="card-emerald py-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                        <i class="fas fa-inbox text-3xl text-emerald-600"></i>
                    </div>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-emerald-800">Tidak Ada PO Pending</h3>
                <p class="text-emerald-600">Saat ini tidak ada Purchase Order yang menunggu persetujuan</p>
            </div>

            <!-- Table -->
            <div v-else class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nomor PO</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kasir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Total Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="po in pendingPOs"
                                :key="po.id_goods_in"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-file-invoice text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ po.nomor_po }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ po.kasir.name }}</div>
                                    <div class="text-sm text-emerald-600">ID: {{ po.kasir.id_pengguna }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ formatDate(po.tanggal_request) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">
                                        {{ po.details.length }} item{{ po.details.length > 1 ? 's' : '' }}
                                    </div>
                                    <div class="text-sm text-emerald-600">Total qty: {{ getTotalItems(po.details) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800"
                                    >
                                        <i class="fas fa-clock mr-1.5"></i>
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <BaseButton
                                        @click="$inertia.visit(`/admin/goods-in-approval/${po.id_goods_in}`)"
                                        variant="primary"
                                        size="sm"
                                        icon="fas fa-eye"
                                    >
                                        Lihat Detail
                                    </BaseButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
