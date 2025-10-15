<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const adminMenuItems = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'fas fa-tachometer-alt',
        active: true,
    },
    {
        name: 'Manajemen Data',
        icon: 'fas fa-database',
        children: [
            { name: 'Pengguna', href: '/admin/pengguna', icon: 'fas fa-users' },
            { name: 'Produk', href: '/admin/produk', icon: 'fas fa-boxes' },
            { name: 'Kategori', href: '/admin/kategori', icon: 'fas fa-tags' },
            { name: 'Pelanggan', href: '/admin/pelanggan', icon: 'fas fa-user-friends' },
            { name: 'Konversi Stok', href: '/admin/konversi-stok', icon: 'fas fa-exchange-alt' },
        ],
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-cash-register',
        children: [
            { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
            { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
            { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
        ],
    },
    {
        name: 'Laporan',
        href: '/admin/reports',
        icon: 'fas fa-chart-bar',
    },
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: 'fas fa-cog',
    },
];

const stats = [
    {
        title: 'Total Penjualan Hari Ini',
        value: 'Rp 2.450.000',
        change: '+12.5%',
        changeType: 'increase',
        icon: 'fas fa-money-bill-wave',
        color: 'emerald',
    },
    {
        title: 'Transaksi Hari Ini',
        value: '45',
        change: '+8.2%',
        changeType: 'increase',
        icon: 'fas fa-shopping-cart',
        color: 'blue',
    },
    {
        title: 'Produk Terjual',
        value: '127',
        change: '+15.3%',
        changeType: 'increase',
        icon: 'fas fa-box-open',
        color: 'purple',
    },
    {
        title: 'Stok Menipis',
        value: '12',
        change: '-3',
        changeType: 'decrease',
        icon: 'fas fa-exclamation-triangle',
        color: 'red',
    },
];

const recentTransactions = [
    { id: 'TRX-001', kasir: 'Siti Nurhaliza', total: 'Rp 125.000', waktu: '10:30' },
    { id: 'TRX-002', kasir: 'Ahmad Fauzi', total: 'Rp 75.500', waktu: '10:25' },
    { id: 'TRX-003', kasir: 'Dewi Sartika', total: 'Rp 200.000', waktu: '10:20' },
    { id: 'TRX-004', kasir: 'Siti Nurhaliza', total: 'Rp 89.000', waktu: '10:15' },
];

const topProducts = [
    { nama: 'Minyak Kayu Putih 60ml', terjual: 45, revenue: 'Rp 675.000' },
    { nama: 'Minyak Eucalyptus 30ml', terjual: 32, revenue: 'Rp 480.000' },
    { nama: 'Minyak Sereh 100ml', terjual: 28, revenue: 'Rp 420.000' },
    { nama: 'Minyak Cengkeh 50ml', terjual: 24, revenue: 'Rp 360.000' },
];
</script>

<template>
    <BaseLayout title="Admin Dashboard - Sari Bumi Sakti" :menuItems="adminMenuItems" userRole="admin">
        <template #header> Dashboard Admin </template>

        <Head title="Admin Dashboard" />

        <!-- Welcome Section -->
        <div class="mb-8 rounded-2xl bg-emerald-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="mb-2 text-2xl font-bold">Selamat datang, {{ user?.nama }}! 👋</h2>
                    <p class="text-emerald-100">Kelola bisnis minyak atsiri Sari Bumi Sakti dengan mudah</p>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <p class="text-sm text-emerald-100">Terakhir login</p>
                        <p class="font-medium">
                            {{ user?.terakhir_login ? new Date(user.terakhir_login).toLocaleString('id-ID') : 'Tidak diketahui' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.title"
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-shadow duration-200 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-600">{{ stat.title }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                        <div class="mt-2 flex items-center">
                            <span
                                :class="{
                                    'text-green-600': stat.changeType === 'increase',
                                    'text-red-600': stat.changeType === 'decrease',
                                }"
                                class="text-sm font-medium"
                            >
                                {{ stat.change }}
                            </span>
                            <span class="ml-2 text-sm text-gray-500">dari kemarin</span>
                        </div>
                    </div>
                    <div :class="`bg-${stat.color}-100 text-${stat.color}-600`" class="flex h-12 w-12 items-center justify-center rounded-lg">
                        <i :class="stat.icon" class="text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Tables Row -->
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Transactions -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
                        <a href="/admin/transactions" class="text-sm font-medium text-emerald-600 hover:text-emerald-700"> Lihat Semua </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ transaction.id }}</p>
                                <p class="text-sm text-gray-600">{{ transaction.kasir }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ transaction.total }}</p>
                                <p class="text-sm text-gray-500">{{ transaction.waktu }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Produk Terlaris</h3>
                        <a href="/admin/produk" class="text-sm font-medium text-emerald-600 hover:text-emerald-700"> Lihat Semua </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="(product, index) in topProducts" :key="product.nama" class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ product.nama }}</p>
                                    <p class="text-sm text-gray-600">{{ product.terjual }} terjual</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ product.revenue }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900">Aksi Cepat</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <a
                        href="/admin/produk"
                        class="group flex items-center rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 p-4 transition-all duration-200 hover:from-blue-100 hover:to-blue-200"
                    >
                        <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500 text-white">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <p class="font-medium text-blue-900">Tambah Produk</p>
                            <p class="text-sm text-blue-700">Produk baru</p>
                        </div>
                    </a>

                    <a
                        href="/admin/pengguna"
                        class="group flex items-center rounded-xl bg-gradient-to-r from-emerald-50 to-emerald-100 p-4 transition-all duration-200 hover:from-emerald-100 hover:to-emerald-200"
                    >
                        <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-white">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <p class="font-medium text-emerald-900">Kelola Pengguna</p>
                            <p class="text-sm text-emerald-700">Manajemen user</p>
                        </div>
                    </a>

                    <a
                        href="/admin/reports"
                        class="group flex items-center rounded-xl bg-gradient-to-r from-purple-50 to-purple-100 p-4 transition-all duration-200 hover:from-purple-100 hover:to-purple-200"
                    >
                        <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500 text-white">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <p class="font-medium text-purple-900">Laporan</p>
                            <p class="text-sm text-purple-700">Analisis data</p>
                        </div>
                    </a>

                    <a
                        href="/admin/settings"
                        class="group flex items-center rounded-xl bg-gradient-to-r from-orange-50 to-orange-100 p-4 transition-all duration-200 hover:from-orange-100 hover:to-orange-200"
                    >
                        <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500 text-white">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-orange-900">Pengaturan</p>
                            <p class="text-sm text-orange-700">Konfigurasi sistem</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
