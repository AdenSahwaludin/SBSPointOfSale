<script lang="ts" setup>
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface TodayStats {
    total_sales: number;
    total_count: number;
    items_sold: number;
    pending_count: number;
}

interface PaymentMethod {
    metode: string;
    jumlah: number;
    total: number;
}

interface Transaction {
    nomor_transaksi: string;
    tanggal: string;
    total: number;
    status_pembayaran: string;
    metode_bayar: string;
    pelanggan: { nama: string };
}

interface TopProduct {
    nama: string;
    total_terjual: number;
    total_revenue: number;
}

interface LowStockProduct {
    nama: string;
    stok: number;
    satuan: string;
    kategori: { nama: string };
}

interface SalesByHour {
    hour: string;
    amount: number;
    count: number;
}

interface WeekComparison {
    this_week: { total: number; count: number };
    last_week: { total: number; count: number };
    growth: number;
}

const props = defineProps<{
    todayStats: TodayStats;
    paymentMethods: PaymentMethod[];
    recentTransactions: Transaction[];
    topProducts: TopProduct[];
    lowStockAlerts: LowStockProduct[];
    salesByHour: SalesByHour[];
    weekComparison: WeekComparison;
}>();

// Computed
const maxHourValue = computed(() => {
    return Math.max(...props.salesByHour.map((d) => d.amount), 1);
});

const avgTransaction = computed(() => {
    if (props.todayStats.total_count === 0) return 0;
    return props.todayStats.total_sales / props.todayStats.total_count;
});

// Methods
function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString('id-ID', {
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
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getPaymentMethodLabel(metode: string): string {
    const labels: Record<string, string> = {
        TUNAI: 'Tunai',
        QRIS: 'QRIS',
        TRANSFER_BCA: 'Transfer BCA',
        KREDIT: 'Kredit',
    };
    return labels[metode] || metode;
}

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir');
</script>

<template>
    <BaseLayout title="Kasir Dashboard - Sari Bumi Sakti" :menuItems="kasirMenuItems" userRole="kasir">
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Performa dan aktivitas hari ini</p>
            </div>
        </template>

        <Head title="Dashboard - Kasir" />

        <div class="space-y-6">
            <!-- Today Stats -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Total Penjualan</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(todayStats.total_sales) }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ todayStats.total_count }} transaksi</p>
                        </div>
                        <div class="rounded-full bg-blue-50 p-3">
                            <i class="fas fa-money-bill-wave text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Rata-rata Transaksi</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(avgTransaction) }}</p>
                            <p class="mt-1 text-xs text-gray-500">per transaksi</p>
                        </div>
                        <div class="rounded-full bg-emerald-50 p-3">
                            <i class="fas fa-calculator text-2xl text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Item Terjual</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ todayStats.items_sold }}</p>
                            <p class="mt-1 text-xs text-gray-500">total item</p>
                        </div>
                        <div class="rounded-full bg-purple-50 p-3">
                            <i class="fas fa-box-open text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Menunggu Pembayaran</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ todayStats.pending_count }}</p>
                            <p class="mt-1 text-xs text-gray-500">transaksi</p>
                        </div>
                        <div class="rounded-full bg-orange-50 p-3">
                            <i class="fas fa-clock text-2xl text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week Comparison -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Perbandingan Minggu Ini vs Minggu Lalu</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-gray-600">Minggu Ini</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(weekComparison.this_week.total) }}</p>
                        <p class="text-xs text-gray-500">{{ weekComparison.this_week.count }} transaksi</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Minggu Lalu</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(weekComparison.last_week.total) }}</p>
                        <p class="text-xs text-gray-500">{{ weekComparison.last_week.count }} transaksi</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Pertumbuhan</p>
                        <p class="mt-1 flex items-center text-2xl font-bold" :class="weekComparison.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            <i :class="['fas mr-2', weekComparison.growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
                            {{ Math.abs(weekComparison.growth).toFixed(1) }}%
                        </p>
                        <p class="text-xs text-gray-500">dari minggu lalu</p>
                    </div>
                </div>
            </div>

            <!-- Hourly Sales Chart -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Penjualan Per Jam (24 Jam Terakhir)</h2>
                </div>
                <div class="p-6">
                    <div class="flex h-64 items-end justify-between gap-1">
                        <div v-for="(data, index) in salesByHour" :key="index" class="flex flex-1 flex-col items-center gap-1">
                            <div class="flex w-full flex-col items-center">
                                <span v-if="data.amount > 0" class="mb-1 text-xs font-medium text-gray-900">
                                    {{ formatCurrency(data.amount).replace('Rp ', '').substring(0, 4) }}
                                </span>
                                <div class="relative w-full bg-gray-100">
                                    <div
                                        :style="{ height: `${Math.max((data.amount / maxHourValue) * 200, 2)}px` }"
                                        class="w-full bg-emerald-600 transition-all hover:bg-emerald-700"
                                    ></div>
                                </div>
                            </div>
                            <span class="text-xs text-gray-600">{{ data.hour }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Payment Methods -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Metode Pembayaran Hari Ini</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="paymentMethods.length === 0" class="py-8 text-center text-gray-500">
                            <i class="fas fa-credit-card mb-2 text-3xl"></i>
                            <p class="text-sm">Belum ada transaksi</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="method in paymentMethods" :key="method.metode" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                        <i class="fas fa-credit-card text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ getPaymentMethodLabel(method.metode) }}</p>
                                        <p class="text-xs text-gray-500">{{ method.jumlah }} transaksi</p>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(method.total) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Produk Terlaris Hari Ini</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="topProducts.length === 0" class="py-8 text-center text-gray-500">
                            <i class="fas fa-box-open mb-2 text-3xl"></i>
                            <p class="text-sm">Belum ada penjualan</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="(product, index) in topProducts" :key="index" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600"
                                    >
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
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Transactions -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h2>
                            <Link href="/kasir/transactions/today" class="text-sm text-blue-600 hover:text-blue-800">
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
                                    <p class="text-xs text-gray-600">{{ trx.pelanggan.nama }} • {{ formatTime(trx.tanggal) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(trx.total) }}</p>
                                    <span
                                        :class="['inline-block rounded-full px-2 py-0.5 text-xs font-medium', getStatusClass(trx.status_pembayaran)]"
                                    >
                                        {{ trx.status_pembayaran }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Peringatan Stok Rendah</h2>
                            <Link href="/kasir/produk" class="text-sm text-blue-600 hover:text-blue-800">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="lowStockAlerts.length === 0" class="py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle mb-2 text-3xl text-green-600"></i>
                            <p class="text-sm">Semua stok aman</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="product in lowStockAlerts"
                                :key="product.nama"
                                class="flex items-center justify-between border-l-4 border-orange-500 bg-orange-50 p-3"
                            >
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
        </div>
    </BaseLayout>
</template>
