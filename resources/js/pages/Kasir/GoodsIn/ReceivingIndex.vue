<script setup lang="ts">
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

interface GoodsInData {
    id_pemesanan_barang: number;
    nomor_po: string;
    status: string;
    tanggal_approval: string;
    kasir?: {
        name: string;
    };
    details?: Array<{
        id_goods_in_detail: number;
        jumlah_dipesan: number;
        jumlah_diterima: number;
        produk?: {
            nama: string;
        };
    }>;
    receivedGoods?: Array<{
        id_goods_received: number;
    }>;
}

interface Props {
    approvedPOs?: GoodsInData[];
}

const props = defineProps<Props>();

const page = usePage();

onMounted(() => {
    console.log('Page props:', page.props);
    console.log('ApprovedPOs:', props.approvedPOs);
});

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in-receiving');
const hasApprovedPOs = computed(() => Array.isArray(props.approvedPOs) && props.approvedPOs.length > 0);
const safeApprovedPOs = computed(() => props.approvedPOs ?? []);
</script>

<template>
    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="min-h-screen bg-gradient-to-br from-emerald-50 to-white p-8">
            <div class="mx-auto max-w-6xl">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-emerald-950">Penerimaan Barang</h1>
                    <p class="mt-2 text-emerald-700">Catat barang yang sudah diterima dari PO yang disetujui</p>
                </div>

                <!-- Empty State -->
                <div v-if="!hasApprovedPOs" class="rounded-lg border border-dashed border-emerald-300 bg-emerald-50 px-6 py-12 text-center">
                    <div class="mb-4 text-4xl">📦</div>
                    <h3 class="text-lg font-semibold text-emerald-900">Tidak ada PO yang sedang diproses</h3>
                    <p class="mt-2 text-emerald-700">Semua PO yang disetujui telah selesai atau tidak ada PO yang menunggu penerimaan barang.</p>
                </div>

                <!-- PO List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="po in safeApprovedPOs"
                        :key="po.id_pemesanan_barang"
                        class="rounded-lg border border-emerald-200 bg-white p-6 shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-semibold text-emerald-950">{{ po.nomor_po }}</h3>
                                    <span
                                        v-if="po.details"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800"
                                    >
                                        {{ po.details.length }} item
                                    </span>
                                    <span
                                        v-if="po.receivedGoods && po.receivedGoods.length > 0"
                                        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800"
                                    >
                                        {{ po.receivedGoods.length }} dicatat
                                    </span>
                                </div>

                                <!-- Items Summary -->
                                <div v-if="po.details" class="mt-3 space-y-1 text-sm text-emerald-700">
                                    <div v-for="detail in po.details" :key="detail.id_goods_in_detail" class="flex items-center justify-between">
                                        <span class="truncate">{{ detail.produk?.nama }}</span>
                                        <span class="ml-2 whitespace-nowrap">
                                            <span class="font-semibold">{{ detail.jumlah_diterima }}</span> / {{ detail.jumlah_dipesan }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Meta Info -->
                                <div class="mt-4 flex gap-4 text-xs text-emerald-600">
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ new Date(po.tanggal_approval).toLocaleDateString('id-ID') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <Link
                                :href="`/kasir/goods-in/${po.id_pemesanan_barang}/receiving`"
                                class="ml-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:outline-none"
                            >
                                <i class="fas fa-arrow-right"></i>
                                Catat Barang
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
