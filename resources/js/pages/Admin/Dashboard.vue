<script lang="ts" setup>
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface SalesStats {
    today: { total: number; count: number };
    this_month: { total: number; count: number };
    last_month: { total: number };
    growth: number;
}

interface StockStats {
    total_products: number;
    low_stock: number;
    out_of_stock: number;
    total_items: number;
}

interface CreditStats {
    active_contracts: number;
    total_receivable: number;
    overdue_count: number;
    this_month_collection: number;
}

interface CustomerStats {
    total: number;
    active: number;
    high_trust: number;
    new_this_month: number;
}

interface TopProduct {
    nama: string;
    total_terjual: number;
    total_revenue: number;
}

interface Transaction {
    nomor_transaksi: string;
    tanggal: string;
    total: number;
    status_pembayaran: string;
    metode_bayar: string;
    pelanggan: { nama: string };
    kasir: { nama: string };
}

interface Payment {
    jatuh_tempo: string;
    jumlah_tagihan: number;
    jumlah_dibayar: number;
    status: string;
    kontrak_kredit: {
        pelanggan: { nama: string };
    };
}

interface LowStockProduct {
    nama: string;
    stok: number;
    satuan: string;
    kategori: { nama: string };
}

interface ChartData {
    date: string;
    amount: number;
    count: number;
}

const props = defineProps<{
    salesStats: SalesStats;
    stockStats: StockStats;
    creditStats: CreditStats;
    customerStats: CustomerStats;
    topProducts: TopProduct[];
    recentTransactions: Transaction[];
    upcomingPayments: Payment[];
    lowStockProducts: LowStockProduct[];
    salesChart: ChartData[];
}>();

// Computed
const maxChartValue = computed(() => {
    return Math.max(...props.salesChart.map((d) => d.amount), 1);
});

// Methods
function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function formatDateTime(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusClass(status: string): string {
    switch (status) {
        case 'LUNAS':
            return 'bg-green-100 text-green-800';
        case 'MENUNGGU':
            return 'bg-yellow-100 text-yellow-800';
        case 'LATE':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin');
</script>

<template>
    <BaseLayout title="Admin Dashboard - Sari Bumi Sakti" :menuItems="adminMenuItems" userRole="admin">
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Ringkasan bisnis dan performa toko</p>
            </div>
        </template>

        <Head title="Dashboard - Admin" />

        <div class="space-y-6">
            <!-- Sales Stats -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Penjualan Hari Ini</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(salesStats.today.total) }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ salesStats.today.count }} transaksi</p>
                        </div>
                        <div class="rounded-full bg-blue-50 p-3">
                            <i class="fas fa-shopping-cart text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Penjualan Bulan Ini</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(salesStats.this_month.total) }}</p>
                            <p class="mt-1 flex items-center text-xs">
                                <i :class="['fas mr-1', salesStats.growth >= 0 ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600']"></i>
                                <span :class="salesStats.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ Math.abs(salesStats.growth).toFixed(1) }}% vs bulan lalu
                                </span>
                            </p>
                        </div>
                        <div class="rounded-full bg-emerald-50 p-3">
                            <i class="fas fa-chart-line text-2xl text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Total Piutang</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(creditStats.total_receivable) }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ creditStats.active_contracts }} kontrak aktif</p>
                        </div>
                        <div class="rounded-full bg-orange-50 p-3">
                            <i class="fas fa-file-invoice-dollar text-2xl text-orange-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Pelanggan Aktif</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ customerStats.active }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ customerStats.high_trust }} trust score ≥ 70</p>
                        </div>
                        <div class="rounded-full bg-purple-50 p-3">
                            <i class="fas fa-users text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Stats -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Produk</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stockStats.total_products }}</p>
                        </div>
                        <i class="fas fa-boxes text-3xl text-blue-600"></i>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Item Stok</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stockStats.total_items.toLocaleString('id-ID') }}</p>
                        </div>
                        <i class="fas fa-cubes text-3xl text-emerald-600"></i>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-orange-300 bg-orange-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-orange-800">Stok Rendah</p>
                            <p class="mt-2 text-3xl font-bold text-orange-900">{{ stockStats.low_stock }}</p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-3xl text-orange-600"></i>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-red-300 bg-red-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-800">Stok Habis</p>
                            <p class="mt-2 text-3xl font-bold text-red-900">{{ stockStats.out_of_stock }}</p>
                        </div>
                        <i class="fas fa-times-circle text-3xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Penjualan 7 Hari Terakhir</h2>
                </div>
                <div class="p-6">
                    <div class="flex h-64 items-end justify-between gap-2">
                        <div v-for="(data, index) in salesChart" :key="index" class="flex flex-1 flex-col items-center gap-2">
                            <div class="flex w-full flex-col items-center">
                                <span class="mb-1 text-xs font-medium text-gray-900">{{ formatCurrency(data.amount).replace('Rp ', '') }}</span>
                                <div class="relative w-full bg-gray-100">
                                    <div
                                        :style="{ height: `${Math.max((data.amount / maxChartValue) * 200, 4)}px` }"
                                        class="w-full bg-blue-600 transition-all hover:bg-blue-700"
                                    ></div>
                                </div>
                            </div>
                            <span class="text-xs text-gray-600">{{ data.date }}</span>
                            <span class="text-xs text-gray-500">{{ data.count }}x</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top Products -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Produk Terlaris Bulan Ini</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="topProducts.length === 0" class="py-8 text-center text-gray-500">
                            <i class="fas fa-box-open mb-2 text-3xl"></i>
                            <p class="text-sm">Belum ada data penjualan</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="(product, index) in topProducts" :key="index" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ product.nama }}</p>
                                        <p class="text-xs text-gray-500">{{ product.total_terjual }} terjual</p>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-emerald-600">{{ formatCurrency(product.total_revenue) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Peringatan Stok Rendah</h2>
                            <Link href="/admin/produk" class="text-sm text-blue-600 hover:text-blue-800">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="lowStockProducts.length === 0" class="py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle mb-2 text-3xl text-green-600"></i>
                            <p class="text-sm">Semua stok aman</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="product in lowStockProducts" :key="product.nama" class="flex items-center justify-between border-l-4 border-orange-500 bg-orange-50 p-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ product.nama }}</p>
                                    <p class="text-xs text-gray-600">{{ product.kategori.nama }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-orange-600">{{ product.stok }} {{ product.satuan }}</p>
                                    <p class="text-xs text-gray-500">Tersisa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Transactions -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h2>
                            <Link href="/admin/transaksi" class="text-sm text-blue-600 hover:text-blue-800">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentTransactions.length === 0" class="p-8 text-center text-gray-500">
                            <i class="fas fa-receipt mb-2 text-3xl"></i>
                            <p class="text-sm">Belum ada transaksi</p>
                        </div>
                        <div v-for="trx in recentTransactions.slice(0, 5)" :key="trx.nomor_transaksi" class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ trx.nomor_transaksi }}</p>
                                    <p class="text-xs text-gray-600">{{ trx.pelanggan.nama }} • {{ trx.kasir.nama }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDateTime(trx.tanggal) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(trx.total) }}</p>
                                    <span :class="['inline-block rounded-full px-2 py-0.5 text-xs font-medium', getStatusClass(trx.status_pembayaran)]">
                                        {{ trx.status_pembayaran }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Payments -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Angsuran Jatuh Tempo (7 Hari)</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="upcomingPayments.length === 0" class="p-8 text-center text-gray-500">
                            <i class="fas fa-calendar-check mb-2 text-3xl text-green-600"></i>
                            <p class="text-sm">Tidak ada angsuran jatuh tempo</p>
                        </div>
                        <div v-for="payment in upcomingPayments.slice(0, 5)" :key="payment.jatuh_tempo" class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ payment.kontrak_kredit.pelanggan.nama }}</p>
                                    <p class="text-xs text-gray-600">Jatuh tempo: {{ formatDate(payment.jatuh_tempo) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(payment.jumlah_tagihan - payment.jumlah_dibayar) }}</p>
                                    <span :class="['inline-block rounded-full px-2 py-0.5 text-xs font-medium', getStatusClass(payment.status)]">
                                        {{ payment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
