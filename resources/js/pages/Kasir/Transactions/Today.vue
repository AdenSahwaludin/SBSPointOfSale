<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import StatsCardTab from '@/components/StatsCardTab.vue';
import TransactionDetailModal from '@/components/TransactionDetailModal.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
}

interface Kasir {
    id_pengguna: string;
    nama: string;
}

interface TransaksiDetail {
    id_detail: number;
    nama_produk: string;
    harga_satuan: number;
    jumlah: number;
    mode_qty: string;
    subtotal: number;
}

interface Transaksi {
    nomor_transaksi: string;
    id_pelanggan: string;
    id_kasir: string;
    tanggal: string;
    total_item: number;
    subtotal: number;
    diskon: number;
    pajak: number;
    total: number;
    metode_bayar: string;
    status_pembayaran: string;
    pelanggan: Pelanggan;
    kasir: Kasir;
    detail: TransaksiDetail[];
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

interface Stats {
    total_transaksi: number;
    total_lunas: number;
    total_menunggu: number;
    total_batal: number;
    total_nilai: number;
    total_item_terjual: number;
}

interface Filters {
    search?: string;
    status?: string;
    metode_bayar?: string;
}

const props = defineProps<{
    transaksi: PaginatedData<Transaksi>;
    stats: Stats;
    filters: Filters;
}>();

// Helper functions
function getDefault7DaysAgo(): string {
    const date = new Date();
    date.setDate(date.getDate() - 7);
    return date.toISOString().split('T')[0];
}

// State
const perPage = ref(props.transaksi.per_page);
const searchQuery = ref(props.filters.search || '');
const selectedMetodeBayar = ref(props.filters.metode_bayar || 'all');
const showFilters = ref(false);
const selectedTransaksi = ref<Transaksi | null>(null);
const showDetailModal = ref(false);
const activeStatsTab = ref(props.filters.status || 'total_transaksi');

// Computed
const displayedTransaksi = computed(() => props.transaksi.data);

const statsTabsData = computed(() => [
    {
        id: 'total_transaksi',
        label: 'Semua',
        value: props.stats.total_transaksi,
        icon: 'fas fa-receipt',
        activeClass: 'bg-blue-50 text-blue-700',
        iconActiveClass: 'text-blue-600',
        iconInactiveClass: 'text-gray-400',
    },
    {
        id: 'total_lunas',
        label: 'Lunas',
        value: props.stats.total_lunas,
        icon: 'fas fa-check-circle',
        activeClass: 'bg-green-50 text-green-700',
        iconActiveClass: 'text-green-600',
        iconInactiveClass: 'text-gray-400',
    },
    {
        id: 'total_menunggu',
        label: 'Belum Bayar',
        value: props.stats.total_menunggu,
        icon: 'fas fa-clock',
        activeClass: 'bg-yellow-50 text-yellow-700',
        iconActiveClass: 'text-yellow-600',
        iconInactiveClass: 'text-gray-400',
    },
    {
        id: 'total_batal',
        label: 'Batal',
        value: props.stats.total_batal,
        icon: 'fas fa-times-circle',
        activeClass: 'bg-red-50 text-red-700',
        iconActiveClass: 'text-red-600',
        iconInactiveClass: 'text-gray-400',
    },
    {
        id: 'total_nilai',
        label: 'Total Nilai',
        value: 'Rp ' + new Intl.NumberFormat('id-ID').format(props.stats.total_nilai),
        icon: 'fas fa-money-bill-wave',
        activeClass: 'bg-emerald-50 text-emerald-700',
        iconActiveClass: 'text-emerald-600',
        iconInactiveClass: 'text-gray-400',
    },
]);

// Methods
function handleSearch() {
    performSearch();
}

function performSearch() {
    const statusMap: Record<string, string | undefined> = {
        'total_transaksi': undefined,
        'total_lunas': 'LUNAS',
        'total_menunggu': 'MENUNGGU',
        'total_batal': 'BATAL',
        'total_nilai': undefined,
    };

    router.get(
        '/kasir/transactions/today',
        {
            search: searchQuery.value,
            status: statusMap[activeStatsTab.value],
            metode_bayar: selectedMetodeBayar.value !== 'all' ? selectedMetodeBayar.value : undefined,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function changePerPage() {
    const statusMap: Record<string, string | undefined> = {
        'total_transaksi': undefined,
        'total_lunas': 'LUNAS',
        'total_menunggu': 'MENUNGGU',
        'total_batal': 'BATAL',
        'total_nilai': undefined,
    };

    router.get(
        '/kasir/transactions/today',
        {
            search: searchQuery.value,
            status: statusMap[activeStatsTab.value],
            metode_bayar: selectedMetodeBayar.value !== 'all' ? selectedMetodeBayar.value : undefined,
            per_page: perPage.value,
            page: 1,
        },
        {
            preserveState: true,
        },
    );
}

function goToPage(url: string | null) {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

function clearSearch() {
    searchQuery.value = '';
    performSearch();
}

function clearFilters() {
    selectedMetodeBayar.value = 'all';
    activeStatsTab.value = 'total_transaksi';
    performSearch();
}

function clearAll() {
    searchQuery.value = '';
    selectedMetodeBayar.value = 'all';
    activeStatsTab.value = 'total_transaksi';
    performSearch();
}

function viewDetail(transaksi: Transaksi) {
    selectedTransaksi.value = transaksi;
    showDetailModal.value = true;
}

function closeDetailModal() {
    showDetailModal.value = false;
    selectedTransaksi.value = null;
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function updateStatus(nomorTransaksi: string, newStatus: string) {
    router.patch(
        `/kasir/transactions/${nomorTransaksi}/status`,
        {
            status_pembayaran: newStatus,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                console.log('Status berhasil diperbarui');
            },
        },
    );
}

function getStatusBadgeClass(status: string): string {
    switch (status) {
        case 'LUNAS':
            return 'bg-green-100 text-green-800';
        case 'MENUNGGU':
            return 'bg-yellow-100 text-yellow-800';
        case 'BATAL':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/transactions/today');
</script>

<template>
    <Head title="Transaksi Hari Ini - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Transaksi Hari Ini</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Lihat semua transaksi yang dilakukan hari ini -
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Cards Tab -->
            <StatsCardTab :stats="statsTabsData" :active-tab="activeStatsTab" @update:active-tab="activeStatsTab = $event; performSearch()" />

            <!-- Search and Filters -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Search Bar -->
                        <div class="flex gap-4">
                            <div class="relative flex-1">
                                <input
                                    v-model="searchQuery"
                                    @keyup.enter="handleSearch"
                                    type="text"
                                    placeholder="Cari nomor transaksi atau nama pelanggan..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                />
                                <button
                                    v-if="searchQuery"
                                    @click="clearSearch"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                                <div v-else class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                            <BaseButton @click="handleSearch" variant="primary">
                                <i class="fas fa-search mr-2"></i>
                                Cari
                            </BaseButton>
                            <BaseButton @click="showFilters = !showFilters" variant="outline">
                                <i class="fas fa-filter mr-2"></i>
                                Filter
                                <i :class="['fas ml-2', showFilters ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                            </BaseButton>
                        </div>

                        <!-- Advanced Filters -->
                        <div v-if="showFilters" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Metode Bayar</label>
                                    <select
                                        v-model="selectedMetodeBayar"
                                        @change="performSearch"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="all">Semua Metode</option>
                                        <option value="TUNAI">Tunai</option>
                                        <option value="QRIS">QRIS</option>
                                        <option value="TRANSFER BCA">Transfer BCA</option>
                                        <option value="KREDIT">Kredit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <BaseButton @click="clearFilters" variant="outline" size="sm">
                                    <i class="fas fa-undo mr-2"></i>
                                    Reset Filter
                                </BaseButton>
                                <BaseButton @click="clearAll" variant="outline" size="sm">
                                    <i class="fas fa-times mr-2"></i>
                                    Hapus Semua
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Nomor Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Metode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="displayedTransaksi.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-receipt mb-4 text-5xl text-gray-300"></i>
                                        <p class="text-lg font-medium text-gray-900">Belum ada transaksi hari ini</p>
                                        <p class="mt-1 text-sm text-gray-500">Transaksi yang dilakukan hari ini akan muncul di sini</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="transaksi in displayedTransaksi" :key="transaksi.nomor_transaksi" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ transaksi.nomor_transaksi }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatTime(transaksi.tanggal) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ transaksi.pelanggan.nama }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ transaksi.total_item }} items</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-emerald-600">{{ formatCurrency(transaksi.total) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ transaksi.metode_bayar }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        :value="transaksi.status_pembayaran"
                                        @change="(e) => updateStatus(transaksi.nomor_transaksi, (e.target as HTMLSelectElement).value)"
                                        :class="[
                                            'cursor-pointer rounded-full border-0 px-3 py-1 text-xs font-semibold focus:ring-2 focus:ring-offset-2',
                                            getStatusBadgeClass(transaksi.status_pembayaran),
                                        ]"
                                    >
                                        <option value="LUNAS">LUNAS</option>
                                        <option value="MENUNGGU">MENUNGGU</option>
                                        <option value="BATAL">BATAL</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <button @click="viewDetail(transaksi)" class="text-emerald-600 hover:text-emerald-900">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transaksi.last_page > 1" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-700">Items per page:</span>
                            <select
                                v-model="perPage"
                                @change="changePerPage"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            >
                                <option :value="10">10</option>
                                <option :value="15">15</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-700">
                                Showing {{ transaksi.from }} to {{ transaksi.to }} of {{ transaksi.total }} entries
                            </span>
                        </div>

                        <div class="flex gap-1">
                            <button
                                @click="goToPage(transaksi.prev_page_url)"
                                :disabled="!transaksi.prev_page_url"
                                :class="[
                                    'rounded-lg px-3 py-1 text-sm',
                                    transaksi.prev_page_url
                                        ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100'
                                        : 'cursor-not-allowed bg-gray-100 text-gray-400',
                                ]"
                            >
                                Previous
                            </button>

                            <button
                                v-for="(link, index) in transaksi.links.slice(1, -1)"
                                :key="index"
                                @click="goToPage(link.url)"
                                :class="[
                                    'rounded-lg px-3 py-1 text-sm',
                                    link.active ? 'bg-emerald-600 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100',
                                ]"
                                v-html="link.label"
                            ></button>

                            <button
                                @click="goToPage(transaksi.next_page_url)"
                                :disabled="!transaksi.next_page_url"
                                :class="[
                                    'rounded-lg px-3 py-1 text-sm',
                                    transaksi.next_page_url
                                        ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100'
                                        : 'cursor-not-allowed bg-gray-100 text-gray-400',
                                ]"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <TransactionDetailModal
            :show="showDetailModal"
            :transaksi="selectedTransaksi"
            @close="closeDetailModal"
        />
    </BaseLayout>
</template>

<style scoped>
select.bg-green-100 {
    background-color: #d1fae5;
    color: #065f46;
}
select.bg-yellow-100 {
    background-color: #fef3c7;
    color: #92400e;
}
select.bg-red-100 {
    background-color: #fee2e2;
    color: #991b1b;
}
select option {
    background-color: white;
    color: black;
}
</style>
