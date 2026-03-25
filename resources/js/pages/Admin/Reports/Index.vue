<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import ExportDropdown from '@/components/ExportDropdown.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement, ArcElement);

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

interface SalesTrend {
    date: string;
    count: number;
    revenue: number;
}

interface PaymentMethod {
    method: string;
    count: number;
    total: number;
}

interface StatusDistribution {
    status: string;
    count: number;
}

interface TopProduct {
    nama: string;
    total_qty: number;
    total_revenue: number;
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
        total_pendapatan: number;
        total_lunas: number;
        total_menunggu: number;
        total_batal: number;
    };
    salesTrend: SalesTrend[];
    paymentMethods: PaymentMethod[];
    statusDistribution: StatusDistribution[];
    topProducts: TopProduct[];
    filters: {
        start_date: string | null;
        end_date: string | null;
        status: string | null;
    };
}

const props = defineProps<Props>();
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/reports');
const page = usePage();

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const selectedStatus = ref(props.filters.status || 'all');
const perPage = ref(String(props.transaksi?.meta?.per_page || 15));

// Computed export URLs
const exportPdfUrl = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (selectedStatus.value !== 'all') params.append('status', selectedStatus.value);
    return `/admin/reports/export/pdf?${params.toString()}`;
});

const exportCsvUrl = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (selectedStatus.value !== 'all') params.append('status', selectedStatus.value);
    return `/admin/reports/export/csv?${params.toString()}`;
});

function handleFilter() {
    router.get(
        '/admin/reports',
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            per_page: perPage.value,
        },
        { preserveScroll: true },
    );
}

function resetFilters() {
    startDate.value = '';
    endDate.value = '';
    selectedStatus.value = 'all';
    router.get('/admin/reports');
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

// Charts Data
const salesTrendChartData = computed(() => ({
    labels: props.salesTrend.map((item) => new Date(item.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })),
    datasets: [
        {
            label: 'Pendapatan (Rp)',
            backgroundColor: 'rgba(16, 185, 129, 0.2)', // emerald-500
            borderColor: '#10b981',
            borderWidth: 2,
            pointBackgroundColor: '#059669',
            pointRadius: 4,
            fill: true,
            data: props.salesTrend.map((item) => item.revenue),
        },
        {
            label: 'Jumlah Transaksi',
            backgroundColor: 'rgba(139, 92, 246, 0.2)', // purple-500
            borderColor: '#8b5cf6',
            borderWidth: 2,
            type: 'line',
            yAxisID: 'y1',
            data: props.salesTrend.map((item) => item.count),
        },
    ],
}));

const salesTrendChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index' as const,
        intersect: false,
    },
    scales: {
        y: {
            type: 'linear' as const,
            display: true,
            position: 'left' as const,
            title: { display: true, text: 'Pendapatan' },
        },
        y1: {
            type: 'linear' as const,
            display: true,
            position: 'right' as const,
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Jumlah Transaksi' },
        },
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: function (context: any) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.datasetIndex === 0) {
                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                    } else {
                        label += context.raw;
                    }
                    return label;
                },
            },
        },
    },
};

const statusChartData = computed(() => {
    const statusMap: Record<string, string> = {
        LUNAS: '#10b981',
        MENUNGGU: '#f59e0b',
        BATAL: '#ef4444',
    };

    return {
        labels: props.statusDistribution.map((item) => item.status),
        datasets: [
            {
                backgroundColor: props.statusDistribution.map((item) => statusMap[item.status] || '#9ca3af'),
                data: props.statusDistribution.map((item) => item.count),
            },
        ],
    };
});

const paymentMethodsChartData = computed(() => ({
    labels: props.paymentMethods.map((item) => item.method),
    datasets: [
        {
            label: 'Total Nilai (Rp)',
            backgroundColor: '#8b5cf6', // purple-500
            data: props.paymentMethods.map((item) => item.total),
        },
    ],
}));
</script>

<template>
    <Head title="Laporan - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header & Actions -->
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">Ringkasan Laporan</h1>
                    <p class="text-emerald-600">Dashboard ringkasan dan statistik performa penjualan</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <ExportDropdown :pdf-url="exportPdfUrl" :csv-url="exportCsvUrl" />
                    <BaseButton @click="router.visit('/admin/reports/daily')" variant="secondary" icon="fas fa-calendar-day"> Harian </BaseButton>
                    <BaseButton @click="router.visit('/admin/reports/weekly')" variant="secondary" icon="fas fa-calendar-week"> Mingguan </BaseButton>
                    <BaseButton @click="router.visit('/admin/reports/monthly')" variant="secondary" icon="fas fa-calendar-alt"> Bulanan </BaseButton>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col items-end gap-4 md:flex-row">
                    <div class="w-full flex-1">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Dari Tanggal</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>
                    <div class="w-full flex-1">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Sampai Tanggal</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>
                    <div class="w-full flex-1">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Status Pembayaran</label>
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
                    <div class="flex w-full gap-2 md:w-auto">
                        <BaseButton @click="handleFilter" variant="primary" icon="fas fa-filter" class="flex-1 justify-center md:flex-none">
                            Terapkan
                        </BaseButton>
                        <BaseButton @click="resetFilters" variant="secondary" icon="fas fa-redo" class="flex-1 justify-center md:flex-none">
                            Reset
                        </BaseButton>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
                    <div class="mb-2 flex items-start justify-between">
                        <p class="text-sm font-medium text-emerald-700">Total Transaksi</p>
                        <i class="fas fa-shopping-cart text-emerald-400"></i>
                    </div>
                    <p class="text-3xl font-bold text-emerald-900">{{ stats.total_transaksi }}</p>
                </div>
                <div class="rounded-xl border border-purple-100 bg-gradient-to-br from-purple-50 to-white p-5 shadow-sm">
                    <div class="mb-2 flex items-start justify-between">
                        <p class="text-sm font-medium text-purple-700">Pendapatan</p>
                        <i class="fas fa-money-bill-wave text-purple-400"></i>
                    </div>
                    <p class="text-xl font-bold text-purple-900 md:text-2xl">{{ formatCurrency(stats.total_pendapatan) }}</p>
                </div>
                <div class="rounded-xl border border-green-100 bg-gradient-to-br from-green-50 to-white p-5 shadow-sm">
                    <div class="mb-2 flex items-start justify-between">
                        <p class="text-sm font-medium text-green-700">Lunas</p>
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-green-900">{{ stats.total_lunas }}</p>
                </div>
                <div class="rounded-xl border border-yellow-100 bg-gradient-to-br from-yellow-50 to-white p-5 shadow-sm">
                    <div class="mb-2 flex items-start justify-between">
                        <p class="text-sm font-medium text-yellow-700">Menunggu</p>
                        <i class="fas fa-clock text-yellow-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-yellow-900">{{ stats.total_menunggu }}</p>
                </div>
                <div class="rounded-xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-5 shadow-sm">
                    <div class="mb-2 flex items-start justify-between">
                        <p class="text-sm font-medium text-red-700">Batal</p>
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-red-900">{{ stats.total_batal }}</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Tren Penjualan -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="mb-4 font-semibold text-gray-800">Tren Penjualan</h3>
                    <div class="h-72 w-full">
                        <Line v-if="salesTrend.length > 0" :data="salesTrendChartData" :options="salesTrendChartOptions" />
                        <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada data tren untuk periode ini</div>
                    </div>
                </div>

                <!-- Status Transaksi -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-800">Status Transaksi</h3>
                    <div class="flex h-64 w-full items-center justify-center">
                        <Doughnut
                            v-if="statusDistribution.length > 0"
                            :data="statusChartData"
                            :options="{ responsive: true, maintainAspectRatio: false }"
                        />
                        <div v-else class="text-gray-400">Tidak ada data</div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-800">Metode Pembayaran (Lunas)</h3>
                    <div class="h-64 w-full">
                        <Bar
                            v-if="paymentMethods.length > 0"
                            :data="paymentMethodsChartData"
                            :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }"
                        />
                        <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada data</div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="mb-4 font-semibold text-gray-800">Produk Paling Laris (Lunas)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Nama Produk</th>
                                    <th class="px-4 py-2 text-right font-medium">Terjual (Qty)</th>
                                    <th class="px-4 py-2 text-right font-medium">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-gray-700">
                                <tr v-for="(product, idx) in topProducts" :key="idx" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ product.nama }}</td>
                                    <td class="px-4 py-3 text-right">{{ product.total_qty }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-emerald-600">{{ formatCurrency(product.total_revenue) }}</td>
                                </tr>
                                <tr v-if="topProducts.length === 0">
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">Tidak ada data produk terjual pada periode ini</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Transaksi - Collapsible or Less Dominant -->
            <details class="group rounded-xl border border-gray-200 bg-white shadow-sm" open>
                <summary class="flex cursor-pointer items-center justify-between p-5 font-semibold text-gray-800 marker:content-none">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-list text-gray-400"></i>
                        Data Transaksi Lengkap ({{ transaksi?.meta?.total || 0 }})
                    </span>
                    <span class="transition group-open:rotate-180">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </span>
                </summary>

                <div class="border-t border-gray-100 p-5">
                    <div class="mb-4 flex justify-end">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Tampilkan:</label>
                            <select
                                v-model="perPage"
                                @change="handleFilter"
                                class="rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="transaksi.data.length > 0" class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">No. Transaksi</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kasir</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="trans in transaksi.data" :key="trans.nomor_transaksi" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-emerald-600">{{ trans.nomor_transaksi }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ formatDateTime(trans.tanggal) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ trans.kasir.nama }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ formatCurrency(trans.total) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            :class="getStatusBadgeClass(trans.status_pembayaran)"
                                            class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold"
                                        >
                                            {{ trans.status_pembayaran }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Link
                                            :href="`/admin/transactions/${trans.nomor_transaksi}`"
                                            class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                        >
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-lg bg-gray-50 py-8 text-center">
                        <p class="text-gray-500">Tidak ada data transaksi</p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="transaksi?.meta?.last_page && transaksi.meta.last_page > 1"
                        class="mt-4 flex flex-col items-center justify-between gap-4 sm:flex-row"
                    >
                        <p class="text-xs text-gray-500">
                            Menampilkan {{ transaksi?.meta?.from || 0 }} - {{ transaksi?.meta?.to || 0 }} dari {{ transaksi?.meta?.total || 0 }}
                        </p>
                        <div class="flex flex-wrap justify-center gap-1">
                            <template v-for="(link, i) in transaksi?.links || []" :key="i">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'rounded border px-3 py-1 text-sm transition-colors',
                                        link.active
                                            ? 'border-emerald-200 bg-emerald-50 font-medium text-emerald-700'
                                            : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50',
                                    ]"
                                    v-html="link.label"
                                ></Link>
                                <span
                                    v-else
                                    class="rounded border border-gray-200 bg-gray-50 px-3 py-1 text-sm text-gray-400"
                                    v-html="link.label"
                                    aria-disabled="true"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </details>
        </div>
    </BaseLayout>
</template>
