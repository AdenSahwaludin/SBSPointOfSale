<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import ExportDropdown from '@/components/ExportDropdown.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface TopPerformer {
    nama: string;
    total: number;
    count: number;
}

interface DailyData {
    tanggal: string;
    hari: string;
    total: number;
    count: number;
}

interface PaymentMethod {
    metode: string;
    total: number;
    count: number;
}

interface Transaksi {
    nomor_transaksi: string;
    id_pelanggan: string;
    id_kasir: string;
    tanggal: string;
    total: number;
    metode_bayar: string;
    status_pembayaran: string;
    pelanggan: { nama: string };
    kasir: { nama: string };
}

interface Props {
    start_date: string;
    end_date: string;
    week_display: string;
    stats: {
        total_transaksi: number;
        total_lunas: number;
        total_menunggu: number;
        total_batal: number;
        total_nilai: number;
        total_item: number;
    };
    paymentMethods: PaymentMethod[];
    dailyData: DailyData[];
    topKasir: TopPerformer[];
    topPelanggan: TopPerformer[];
    transaksi: {
        data: Transaksi[];
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    insights: {
        aov: number;
        growth: number;
        last_period_value: number;
        dominant_method: string;
        best_day: string;
        top_customer: string;
    };
}

const props = defineProps<Props>();
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/reports/weekly');
const selectedStartDate = ref(props.start_date);
const selectedEndDate = ref(props.end_date);

const exportPdfUrl = computed(() => `/admin/reports/weekly/export/pdf?start_date=${selectedStartDate.value}`);
const exportCsvUrl = computed(() => `/admin/reports/weekly/export/csv?start_date=${selectedStartDate.value}`);

const perPage = ref(String(props.transaksi?.per_page || 15));

function handleDateChange() {
    router.get(`/admin/transactions/laporan-mingguan`, {
        start_date: selectedStartDate.value,
        per_page: perPage.value
    });
}

function handlePerPageChange() {
    router.get(`/admin/transactions/laporan-mingguan`, {
        start_date: selectedStartDate.value,
        per_page: perPage.value,
        page: 1
    }, { preserveState: true });
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.round(amount));
}

function getStatusBadgeClass(status: string): string {
    const statusLower = status.toLowerCase();
    if (statusLower === 'lunas') return 'text-green-700 bg-green-100 border-green-200';
    if (statusLower === 'menunggu') return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    if (statusLower === 'batal') return 'text-red-700 bg-red-100 border-red-200';
    return 'text-gray-700 bg-gray-100 border-gray-200';
}

function getMaxDailyTotal(): number {
    return props.dailyData.length > 0 ? Math.max(...props.dailyData.map((d) => d.total)) : 0;
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
    <Head title="Laporan Mingguan - Transaksi Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">Laporan Mingguan</h1>
                    <p class="text-emerald-600">{{ week_display }}</p>
                </div>
                <div class="flex gap-2">
                    <ExportDropdown :pdf-url="exportPdfUrl" :csv-url="exportCsvUrl" />
                    <BaseButton @click="router.visit('/admin/reports')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                </div>
            </div>

            <!-- Date Picker -->
            <div class="flex max-w-sm items-end gap-3">
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Pilih Minggu</label>
                    <input
                        v-model="selectedStartDate"
                        type="week"
                        @change="handleDateChange"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    />
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
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
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <p class="text-xs font-medium text-blue-700">Total Item</p>
                    <p class="mt-2 text-2xl font-bold text-blue-800">{{ stats.total_item }}</p>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                    <p class="text-xs font-medium text-purple-700">Total Nilai</p>
                    <p class="mt-2 text-lg font-bold text-purple-800">{{ formatCurrency(stats.total_nilai) }}</p>
                </div>
            </div>

            <!-- Insights Section -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-indigo-500"></i>
                        <p class="text-xs font-medium text-indigo-700">Average Order Value</p>
                    </div>
                    <p class="mt-2 text-xl font-bold text-indigo-800">{{ formatCurrency(insights.aov) }}</p>
                </div>
                <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-line text-teal-500"></i>
                        <p class="text-xs font-medium text-teal-700">Pertumbuhan (vs Minggu Lalu)</p>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <p class="text-xl font-bold" :class="insights.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            <i :class="insights.growth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1 text-sm"></i>
                            {{ Math.abs(insights.growth) }}%
                        </p>
                    </div>
                </div>
                <div class="rounded-lg border border-orange-200 bg-orange-50 p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-credit-card text-orange-500"></i>
                        <p class="text-xs font-medium text-orange-700">Metode Dominan</p>
                    </div>
                    <p class="mt-2 text-xl font-bold text-orange-800">{{ insights.dominant_method }}</p>
                </div>
                <div class="rounded-lg border border-pink-200 bg-pink-50 p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-pink-500"></i>
                        <p class="text-xs font-medium text-pink-700">Hari Terbaik</p>
                    </div>
                    <p class="mt-2 text-xl font-bold text-pink-800">{{ insights.best_day }}</p>
                </div>
            </div>

            <!-- Payment Methods & Daily Trend -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Payment Methods -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Metode Pembayaran</h3>
                    <div v-if="paymentMethods.length > 0" class="space-y-3">
                        <div
                            v-for="method in paymentMethods"
                            :key="method.metode"
                            class="flex items-center justify-between rounded-lg border border-gray-100 p-3"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ method.metode }}</p>
                                <p class="text-xs text-gray-500">{{ method.count }} transaksi</p>
                            </div>
                            <p class="text-lg font-bold text-emerald-600">{{ formatCurrency(method.total) }}</p>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-gray-500">Tidak ada transaksi</p>
                    </div>
                </div>

                <!-- Daily Trend -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Tren Penjualan Harian</h3>
                    <div v-if="dailyData.length > 0" class="space-y-2">
                        <div v-for="day in dailyData" :key="day.tanggal" class="flex items-center gap-3">
                            <div class="w-16 text-xs font-medium text-gray-600">{{ day.hari }}</div>
                            <div class="flex-1">
                                <div class="h-6 rounded-lg bg-emerald-100">
                                    <div
                                        class="h-full rounded-lg bg-emerald-500 transition-all"
                                        :style="`width: ${getMaxDailyTotal() > 0 ? (day.total / getMaxDailyTotal()) * 100 : 0}%`"
                                    ></div>
                                </div>
                            </div>
                            <div class="w-20 shrink-0">
    <p class="whitespace-nowrap text-xs font-medium text-gray-600">{{ formatCurrency(day.total) }}</p>
</div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-gray-500">Tidak ada transaksi</p>
                    </div>
                </div>
            </div>

            <!-- Top Kasir & Top Customers -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top Kasir -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Top 5 Kasir</h3>
                    <div v-if="topKasir.length > 0" class="space-y-3">
                        <div
                            v-for="(kasir, index) in topKasir"
                            :key="kasir.nama"
                            class="flex items-center gap-4 rounded-lg border border-gray-100 p-4"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">
                                {{ index + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900">{{ kasir.nama }}</p>
                                <p class="text-xs text-gray-500">{{ kasir.count }} transaksi</p>
                            </div>
                            <p class="text-lg font-bold whitespace-nowrap text-emerald-600">{{ formatCurrency(kasir.total) }}</p>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-gray-500">Tidak ada data kasir</p>
                    </div>
                </div>

                <!-- Top Customers -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Top 5 Pelanggan</h3>
                    <div v-if="topPelanggan.length > 0" class="space-y-3">
                        <div
                            v-for="(pelanggan, index) in topPelanggan"
                            :key="pelanggan.nama"
                            class="flex items-center gap-4 rounded-lg border border-gray-100 p-4"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                                {{ index + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900">{{ pelanggan.nama }}</p>
                                <p class="text-xs text-gray-500">{{ pelanggan.count }} transaksi</p>
                            </div>
                            <p class="text-lg font-bold whitespace-nowrap text-blue-600">{{ formatCurrency(pelanggan.total) }}</p>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-gray-500">Tidak ada data pelanggan</p>
                    </div>
                </div>
            </div>

            <!-- Transaction List -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Transaksi ({{ transaksi.total }})</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Per Halaman:</label>
                        <select
                            v-model="perPage"
                            @change="handlePerPageChange"
                            class="rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
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
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-8 text-center">
                    <p class="text-gray-500">Tidak ada transaksi pada minggu ini</p>
                </div>

                <!-- Pagination -->
                <div v-if="transaksi.last_page > 1" class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-gray-100 pt-6 md:flex-row">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold">{{ transaksi.from || 0 }}</span> hingga <span class="font-semibold">{{ transaksi.to || 0 }}</span> dari
                        <span class="font-semibold text-emerald-600">{{ transaksi.total || 0 }}</span> transaksi
                    </p>
                    <div class="flex flex-wrap items-center gap-1">
                        <Link
                            v-for="(link, index) in transaksi.links"
                            :key="index"
                            :href="link.url || '#'"
                            :class="[
                                'inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                                link.active
                                    ? 'bg-emerald-600 text-white shadow-md'
                                    : link.url
                                      ? 'bg-white border border-gray-300 text-gray-700 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50'
                                      : 'cursor-not-allowed bg-gray-50 border border-gray-200 text-gray-400',
                                index === 0 || index === transaksi.links.length - 1 ? 'px-4' : '',
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
