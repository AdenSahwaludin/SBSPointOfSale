<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import ExportDropdown from '@/components/ExportDropdown.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

interface PaymentMethod {
    metode: string;
    total: number;
    count: number;
}

interface HourlyData {
    jam: string;
    total: number;
    count: number;
}

interface Props {
    tanggal: string;
    tanggal_display: string;
    stats: {
        total_transaksi: number;
        total_lunas: number;
        total_menunggu: number;
        total_batal: number;
        total_nilai: number;
        total_item: number;
    };
    paymentMethods: PaymentMethod[];
    hourlyData: HourlyData[];
    transaksi: Transaksi[];
    insights: {
        aov: number;
        growth: number;
        last_period_value: number;
        dominant_method: string;
        best_time: string;
        top_customer: string;
    };
}

const props = defineProps<Props>();
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/reports/daily');
const selectedDate = ref(props.tanggal);

const exportPdfUrl = computed(() => `/admin/reports/daily/export/pdf?tanggal=${selectedDate.value}`);
const exportCsvUrl = computed(() => `/admin/reports/daily/export/csv?tanggal=${selectedDate.value}`);

function handleDateChange() {
    router.get(`/admin/transactions/laporan-harian?tanggal=${selectedDate.value}`);
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
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Laporan Harian - Transaksi Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">Laporan Harian</h1>
                    <p class="text-emerald-600">{{ tanggal_display }}</p>
                </div>
                <div class="flex gap-2">
                    <ExportDropdown :pdf-url="exportPdfUrl" :csv-url="exportCsvUrl" />
                    <BaseButton @click="router.visit('/admin/reports')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                </div>
            </div>

            <!-- Date Picker -->
            <div class="flex items-end gap-3">
                <div class="max-w-sm flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Pilih Tanggal</label>
                    <input
                        v-model="selectedDate"
                        type="date"
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
                        <p class="text-xs font-medium text-teal-700">Pertumbuhan (vs Kemarin)</p>
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
                        <i class="fas fa-clock text-pink-500"></i>
                        <p class="text-xs font-medium text-pink-700">Jam Tersibuk</p>
                    </div>
                    <p class="mt-2 text-xl font-bold text-pink-800">{{ insights.best_time }}</p>
                </div>
            </div>

            <!-- Payment Methods & Hourly Breakdown -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Payment Methods Breakdown -->
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

                <!-- Hourly Breakdown -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Penjualan Per Jam</h3>
                    <div v-if="hourlyData.length > 0" class="space-y-2">
                        <div v-for="hour in hourlyData" :key="hour.jam" class="flex items-center gap-3">
                            <div class="w-12 text-sm font-medium text-gray-600">{{ hour.jam }}</div>
                            <div class="flex-1">
                                <div class="h-8 rounded-lg bg-emerald-100">
                                    <div
                                        class="h-full rounded-lg bg-emerald-500 transition-all"
                                        :style="`width: ${hourlyData.length > 0 ? (hour.total / Math.max(...hourlyData.map((h) => h.total))) * 100 : 0}%`"
                                    ></div>
                                </div>
                            </div>
                            <div class="w-20 text-right">
                                <p class="text-xs font-medium text-gray-600">{{ formatCurrency(hour.total) }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-gray-500">Tidak ada transaksi</p>
                    </div>
                </div>
            </div>

            <!-- Transaction List -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Daftar Transaksi</h3>
                <div v-if="transaksi.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b-2 border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">No. Transaksi</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jam</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Pelanggan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kasir</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Metode</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="trans in transaksi" :key="trans.nomor_transaksi" class="hover:bg-gray-50">
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
                    <p class="text-gray-500">Tidak ada transaksi pada tanggal ini</p>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
