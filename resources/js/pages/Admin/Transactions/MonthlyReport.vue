<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

interface Props {
    bulan: number;
    tahun: number;
    bulan_display: string;
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
}

const props = defineProps<Props>();
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/reports/monthly');
const selectedMonth = ref(String(props.bulan).padStart(2, '0'));
const selectedYear = ref(String(props.tahun));

function handleDateChange() {
    router.get(`/admin/transactions/laporan-bulanan?bulan=${selectedMonth.value}&tahun=${selectedYear.value}`);
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function getMaxDailyTotal(): number {
    return props.dailyData.length > 0 ? Math.max(...props.dailyData.map((d) => d.total)) : 0;
}

const months = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => String(currentYear - i));
</script>

<template>
    <Head title="Laporan Bulanan - Transaksi Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">Laporan Bulanan</h1>
                    <p class="text-emerald-600">{{ bulan_display }}</p>
                </div>
                <BaseButton @click="router.visit('/admin/transactions')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Month & Year Picker -->
            <div class="flex max-w-xl items-end gap-3">
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Bulan</label>
                    <select
                        v-model="selectedMonth"
                        @change="handleDateChange"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                        <option v-for="month in months" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Tahun</label>
                    <select
                        v-model="selectedYear"
                        @change="handleDateChange"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
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
                    <div v-if="dailyData.length > 0" class="max-h-64 space-y-2 overflow-y-auto">
                        <div v-for="day in dailyData" :key="day.tanggal" class="flex items-center gap-3">
                            <div class="w-12 text-xs font-medium text-gray-600">{{ day.hari }}</div>
                            <div class="flex-1">
                                <div class="h-6 rounded-lg bg-emerald-100">
                                    <div
                                        class="h-full rounded-lg bg-emerald-500 transition-all"
                                        :style="`width: ${getMaxDailyTotal() > 0 ? (day.total / getMaxDailyTotal()) * 100 : 0}%`"
                                    ></div>
                                </div>
                            </div>
                            <div class="w-16 text-right">
                                <p class="text-xs font-medium text-gray-600">{{ formatCurrency(day.total) }}</p>
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
        </div>
    </BaseLayout>
</template>
