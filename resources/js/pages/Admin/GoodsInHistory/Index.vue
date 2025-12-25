<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    poHistory: PaginatedData<PoHistory>;
    filters: {
        search?: string;
        status?: string;
        start_date?: string;
        end_date?: string;
    };
    availableStatuses: string[];
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/goods-in-history');

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

function performSearch() {
    router.get('/admin/goods-in-history', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    });
}

function clearFilters() {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/goods-in-history');
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

function formatShortDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function getTotalItems(details: GoodsInDetail[]) {
    return details.reduce((sum, detail) => sum + detail.jumlah_dipesan, 0);
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
</script>

<template>
    <Head title="Riwayat PO - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Riwayat Purchase Order</h1>
                    <p class="text-emerald-600">Pantau semua PO yang telah diproses lengkap dengan status akhirnya</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-emerald space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Cari PO atau Kasir</label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Nomor PO atau nama kasir..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            />
                            <i class="fas fa-search absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="selectedStatus"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="all">Semua Status</option>
                            <option v-for="status in availableStatuses" :key="status" :value="status">
                                {{ getStatusLabel(status) }}
                            </option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2">
                    <BaseButton @click="performSearch" variant="primary" icon="fas fa-search"> Cari </BaseButton>
                    <BaseButton @click="clearFilters" variant="secondary" icon="fas fa-times"> Reset </BaseButton>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="poHistory.data.length === 0" class="card-emerald py-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                        <i class="fas fa-inbox text-3xl text-emerald-600"></i>
                    </div>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-emerald-800">Tidak Ada Riwayat PO</h3>
                <p class="text-emerald-600">Belum ada Purchase Order yang selesai diproses sesuai dengan filter yang dipilih</p>
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
                                v-for="po in poHistory.data"
                                :key="po.id_pemesanan_barang"
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
                                    <div class="text-sm text-emerald-800">{{ formatShortDate(po.tanggal_request) }}</div>
                                    <div class="text-xs text-emerald-600">{{ formatDate(po.tanggal_request).split(', ')[1] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">
                                        {{ po.details.length }} item{{ po.details.length > 1 ? 's' : '' }}
                                    </div>
                                    <div class="text-sm text-emerald-600">Total qty: {{ getTotalItems(po.details) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="['inline-flex rounded-full border px-3 py-1 text-xs font-semibold', getStatusBadgeClass(po.status)]"
                                    >
                                        <i :class="['mr-1.5', getStatusIcon(po.status)]"></i>
                                        {{ getStatusLabel(po.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <BaseButton
                                        @click="$inertia.visit(`/admin/goods-in-history/${po.id_pemesanan_barang}`)"
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

            <!-- Pagination -->
            <div v-if="poHistory.data.length > 0" class="flex items-center justify-between">
                <div class="text-sm text-gray-600">Menampilkan {{ poHistory.from }} hingga {{ poHistory.to }} dari {{ poHistory.total }} hasil</div>
                <div class="flex gap-1">
                    <button
                        v-for="link in poHistory.links"
                        :key="link.label"
                        @click="link.url && goToPage(link.url)"
                        :disabled="!link.url"
                        :class="[
                            'rounded border px-3 py-2 text-sm font-medium transition-colors',
                            link.active
                                ? 'border-emerald-500 bg-emerald-500 text-white'
                                : link.url
                                  ? 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                  : 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400',
                        ]"
                        v-html="link.label"
                    ></button>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
