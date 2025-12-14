<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Kasir {
    id_pengguna: string;
    nama: string;
}

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
}

interface Transaksi {
    nomor_transaksi: string;
    id_pelanggan: string;
    id_kasir: string;
    tanggal: string;
    total: number;
    metode_bayar: string;
    status_pembayaran: string;
    pelanggan: Pelanggan;
    kasir: Kasir;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    transaksi: {
        data: Transaksi[];
        links: PaginationLink[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            per_page: number;
            to: number;
            total: number;
        };
    };
    stats: {
        total_transaksi: number;
        total_lunas: number;
        total_menunggu: number;
        total_batal: number;
        total_nilai: number;
    };
    filters: {
        search: string | null;
        status: string | null;
        metode_bayar: string | null;
        start_date: string | null;
        end_date: string | null;
    };
}

const props = defineProps<Props>();
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/transactions');

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedMethode = ref(props.filters.metode_bayar || 'all');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const perPage = ref(String(props.transaksi?.meta?.per_page || 15));

function handleSearch() {
    router.get(
        '/admin/transactions',
        {
            search: searchInput.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            metode_bayar: selectedMethode.value !== 'all' ? selectedMethode.value : undefined,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: perPage.value,
        },
        { preserveScroll: true },
    );
}

function resetFilters() {
    searchInput.value = '';
    selectedStatus.value = 'all';
    selectedMethode.value = 'all';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/transactions');
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function getStatusBadgeClass(status: string): string {
    const statusLower = status.toLowerCase();
    if (statusLower === 'lunas') return 'text-green-700 bg-green-100 border-green-200';
    if (statusLower === 'menunggu') return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    if (statusLower === 'batal') return 'text-red-700 bg-red-100 border-red-200';
    return 'text-gray-700 bg-gray-100 border-gray-200';
}

function formatDateTime(dateString: string): string {
    return (
        new Date(dateString).toLocaleDateString('id-ID') +
        ' ' +
        new Date(dateString).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
        })
    );
}
</script>

<template>
    <Head title="Semua Transaksi - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">Semua Transaksi</h1>
                    <p class="text-emerald-600">Kelola semua transaksi di sistem</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <BaseButton @click="router.visit('/admin/transactions/laporan-mingguan')" variant="secondary" icon="fas fa-calendar-week">
                        Laporan Mingguan
                    </BaseButton>
                    <BaseButton @click="router.visit('/admin/transactions/laporan-harian')" variant="secondary" icon="fas fa-calendar-day">
                        Laporan Harian
                    </BaseButton>
                    <BaseButton @click="router.visit('/admin/transactions/laporan-bulanan')" variant="secondary" icon="fas fa-calendar-alt">
                        Laporan Bulanan
                    </BaseButton>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-medium text-emerald-700">Total Transaksi</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800">{{ stats.total_transaksi }}</p>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                    <p class="text-xs font-medium text-green-700">Lunas</p>
                    <p class="mt-2 text-2xl font-bold text-green-800">{{ stats.total_lunas }}</p>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                    <p class="text-xs font-medium text-yellow-700">Menunggu</p>
                    <p class="mt-2 text-2xl font-bold text-yellow-800">{{ stats.total_menunggu }}</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-xs font-medium text-red-700">Batal</p>
                    <p class="mt-2 text-2xl font-bold text-red-800">{{ stats.total_batal }}</p>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                    <p class="text-xs font-medium text-purple-700">Total Nilai</p>
                    <p class="mt-2 text-lg font-bold text-purple-800">{{ formatCurrency(stats.total_nilai) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Filter & Pencarian</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Cari Transaksi</label>
                        <input
                            v-model="searchInput"
                            type="text"
                            placeholder="No. Transaksi atau Pelanggan"
                            @keyup.enter="handleSearch"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="selectedStatus"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="all">Semua Status</option>
                            <option value="LUNAS">Lunas</option>
                            <option value="MENUNGGU">Menunggu</option>
                            <option value="BATAL">Batal</option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Metode Bayar</label>
                        <select
                            v-model="selectedMethode"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="all">Semua Metode</option>
                            <option value="TUNAI">Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="TRANSFER BCA">Transfer BCA</option>
                            <option value="KREDIT">Kredit</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 flex gap-2">
                    <BaseButton @click="handleSearch" variant="primary" icon="fas fa-search"> Cari </BaseButton>
                    <BaseButton @click="resetFilters" variant="secondary" icon="fas fa-redo"> Reset </BaseButton>
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Transaksi ({{ transaksi?.meta?.total || 0 }})</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Per Halaman:</label>
                        <select
                            v-model="perPage"
                            @change="handleSearch"
                            class="rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                <div v-if="transaksi.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b-2 border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">No. Transaksi</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal & Jam</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Pelanggan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kasir</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Metode</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="trans in transaksi.data" :key="trans.nomor_transaksi" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-emerald-600">{{ trans.nomor_transaksi }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ formatDateTime(trans.tanggal) }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ trans.pelanggan.nama }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ trans.kasir.nama }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ trans.metode_bayar }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ formatCurrency(trans.total) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="getStatusBadgeClass(trans.status_pembayaran)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ trans.status_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Link
                                        :href="`/admin/transactions/${trans.nomor_transaksi}`"
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-200"
                                    >
                                        <i class="fas fa-eye"></i> Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-8 text-center">
                    <p class="text-gray-500">Tidak ada transaksi ditemukan</p>
                </div>

                <!-- Pagination -->
                <div v-if="transaksi?.meta?.last_page && transaksi.meta.last_page > 1" class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Menampilkan {{ transaksi?.meta?.from || 0 }} hingga {{ transaksi?.meta?.to || 0 }} dari
                        {{ transaksi?.meta?.total || 0 }} transaksi
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in transaksi?.links || []"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : link.url
                                      ? 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                      : 'cursor-not-allowed bg-gray-100 text-gray-400',
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
