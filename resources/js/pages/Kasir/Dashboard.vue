<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const kasirMenuItems = [
    {
        name: 'Dashboard',
        href: '/kasir',
        icon: 'fas fa-tachometer-alt',
        active: true,
    },
    {
        name: 'Point of Sale',
        href: '/kasir/pos',
        icon: 'fas fa-cash-register',
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-receipt',
        children: [
            { name: 'Riwayat Transaksi', href: '/kasir/transactions', icon: 'fas fa-history' },
            { name: 'Transaksi Hari Ini', href: '/kasir/transactions/today', icon: 'fas fa-calendar-day' },
        ],
    },
    {
        name: 'Produk',
        href: '/kasir/products',
        icon: 'fas fa-boxes',
    },
    {
        name: 'Profile',
        href: '/kasir/profile',
        icon: 'fas fa-user-circle',
    },
];

const todayStats = [
    {
        title: 'Total Penjualan Saya',
        value: 'Rp 1.250.000',
        change: '+8 transaksi',
        changeType: 'increase',
        icon: 'fas fa-money-bill-wave',
        color: 'emerald',
    },
    {
        title: 'Transaksi Selesai',
        value: '28',
        change: '+5 dari kemarin',
        changeType: 'increase',
        icon: 'fas fa-check-circle',
        color: 'blue',
    },
    {
        title: 'Item Terjual',
        value: '85',
        change: '+12 item',
        changeType: 'increase',
        icon: 'fas fa-box-open',
        color: 'purple',
    },
    {
        title: 'Rata-rata per Transaksi',
        value: 'Rp 44.643',
        change: '+Rp 2.500',
        changeType: 'increase',
        icon: 'fas fa-calculator',
        color: 'indigo',
    },
];

const myTransactions = [
    { id: 'TRX-045', total: 'Rp 125.000', items: 3, waktu: '14:30', status: 'Selesai' },
    { id: 'TRX-043', total: 'Rp 75.500', items: 2, waktu: '14:15', status: 'Selesai' },
    { id: 'TRX-041', total: 'Rp 200.000', items: 5, waktu: '13:45', status: 'Selesai' },
    { id: 'TRX-038', total: 'Rp 89.000', items: 2, waktu: '13:20', status: 'Selesai' },
];

const quickActions = [
    {
        title: 'Transaksi Baru',
        description: 'Mulai transaksi penjualan',
        href: '/kasir/pos',
        icon: 'fas fa-plus-circle',
        color: 'emerald',
        primary: true,
    },
    {
        title: 'Cek Stok',
        description: 'Lihat ketersediaan produk',
        href: '/kasir/products',
        icon: 'fas fa-search',
        color: 'blue',
    },
    {
        title: 'Riwayat Transaksi',
        description: 'Lihat transaksi sebelumnya',
        href: '/kasir/transactions',
        icon: 'fas fa-history',
        color: 'purple',
    },
];
</script>

<template>
    <BaseLayout title="Kasir Dashboard - Sari Bumi Sakti" :menuItems="kasirMenuItems" userRole="kasir">
        <template #header> Dashboard Kasir </template>

        <Head title="Kasir Dashboard" />

        <!-- Welcome Section -->
        <div class="mb-8 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-600 p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="mb-2 text-2xl font-bold">Selamat bekerja, {{ user?.nama }}! 🌿</h2>
                    <p class="text-teal-100">Siap melayani pelanggan hari ini</p>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <p class="text-sm text-teal-100">Shift hari ini</p>
                        <p class="font-medium">
                            {{
                                new Date().toLocaleDateString('id-ID', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                })
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
            <a
                v-for="action in quickActions"
                :key="action.title"
                :href="action.href"
                :class="{
                    'col-span-1 md:col-span-2': action.primary,
                    'col-span-1': !action.primary,
                }"
                class="block transform rounded-2xl p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-xl"
                :style="`background: linear-gradient(135deg, var(--tw-gradient-from) 0%, var(--tw-gradient-to) 100%);`"
            >
                <div
                    :class="{
                        'bg-gradient-to-r from-emerald-400 to-teal-600': action.color === 'emerald',
                        'bg-gradient-to-r from-blue-400 to-blue-600': action.color === 'blue',
                        'bg-gradient-to-r from-purple-400 to-purple-600': action.color === 'purple',
                    }"
                    class="rounded-2xl p-6 text-white"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="mb-2 text-xl font-bold">{{ action.title }}</h3>
                            <p class="opacity-90">{{ action.description }}</p>
                        </div>
                        <div class="ml-4">
                            <i :class="action.icon" class="text-3xl opacity-80"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in todayStats"
                :key="stat.title"
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-shadow duration-200 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="mb-1 text-sm font-medium text-gray-600">{{ stat.title }}</p>
                        <p class="mb-2 text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                        <div class="flex items-center">
                            <span
                                :class="{
                                    'text-green-600': stat.changeType === 'increase',
                                    'text-red-600': stat.changeType === 'decrease',
                                }"
                                class="text-sm font-medium"
                            >
                                {{ stat.change }}
                            </span>
                        </div>
                    </div>
                    <div
                        :class="`bg-${stat.color}-100 text-${stat.color}-600`"
                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg"
                    >
                        <i :class="stat.icon" class="text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- My Transactions -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Transaksi Saya Hari Ini</h3>
                        <a href="/kasir/transactions" class="text-sm font-medium text-teal-600 hover:text-teal-700"> Lihat Semua </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="transaction in myTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between rounded-xl bg-gray-50 p-4 transition-colors hover:bg-gray-100"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ transaction.id }}</p>
                                    <p class="text-sm text-gray-600">{{ transaction.items }} item • {{ transaction.waktu }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ transaction.total }}</p>
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                    {{ transaction.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance & Tips -->
            <div class="space-y-6">
                <!-- Performance Card -->
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Performa Hari Ini</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Target Transaksi</span>
                            <span class="text-sm font-medium">28/30</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-teal-600" style="width: 93%"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Target Penjualan</span>
                            <span class="text-sm font-medium">Rp 1.25jt/1.5jt</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-emerald-600" style="width: 83%"></div>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h4 class="mb-2 font-semibold text-amber-900">Tips Hari Ini</h4>
                            <p class="text-sm text-amber-800">
                                Promosikan paket minyak kayu putih 3 botol untuk meningkatkan nilai transaksi. Pelanggan sering tertarik dengan
                                penawaran bundling!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
