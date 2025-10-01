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
        <div class="mb-8 rounded-2xl bg-emerald-gradient-hero p-6 text-white shadow-emerald-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="mb-2 text-2xl font-bold drop-shadow-sm">Selamat bekerja, {{ user?.nama }}! 🌿</h2>
                    <p class="text-emerald-100">Siap melayani pelanggan hari ini</p>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <p class="text-sm text-emerald-100">Shift hari ini</p>
                        <p class="font-medium drop-shadow-sm">
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
                class="block transform rounded-2xl p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-emerald-xl emerald-transition emerald-hover-scale"
            >
                <div
                    :class="{
                        'bg-emerald-gradient-primary': action.color === 'emerald',
                        'bg-emerald-gradient-secondary': action.color === 'blue',
                        'bg-emerald-gradient-accent': action.color === 'purple',
                    }"
                    class="rounded-2xl p-6 text-white shadow-emerald"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="mb-2 text-xl font-bold drop-shadow-sm">{{ action.title }}</h3>
                            <p class="opacity-90 drop-shadow-sm">{{ action.description }}</p>
                        </div>
                        <div class="ml-4">
                            <i :class="action.icon" class="text-3xl opacity-80 drop-shadow-sm"></i>
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
                class="card-emerald transition-all duration-200 hover:shadow-emerald-lg hover:scale-105 emerald-transition"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="mb-1 text-sm font-medium text-emerald-600">{{ stat.title }}</p>
                        <p class="mb-2 text-2xl font-bold text-emerald-800">{{ stat.value }}</p>
                        <div class="flex items-center">
                            <span
                                :class="{
                                    'text-emerald-600': stat.changeType === 'increase',
                                    'text-red-600': stat.changeType === 'decrease',
                                }"
                                class="text-sm font-medium"
                            >
                                {{ stat.change }}
                            </span>
                        </div>
                    </div>
                    <div
                        :class="{
                            'bg-emerald-100 text-emerald-600': stat.color === 'emerald',
                            'bg-emerald-200 text-emerald-700': stat.color === 'blue',
                            'bg-emerald-300 text-emerald-800': stat.color === 'purple',
                            'bg-emerald-400 text-white': stat.color === 'indigo',
                        }"
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
            <div class="card-emerald lg:col-span-2">
                <div class="border-b border-emerald-100 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-emerald-800">Transaksi Saya Hari Ini</h3>
                        <a href="/kasir/transactions" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 emerald-transition"> Lihat Semua </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="transaction in myTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between rounded-xl bg-emerald-50 p-4 transition-all hover:bg-emerald-100 hover:scale-105 emerald-transition"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-emerald-800">{{ transaction.id }}</p>
                                    <p class="text-sm text-emerald-600">{{ transaction.items }} item • {{ transaction.waktu }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-emerald-800">{{ transaction.total }}</p>
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">
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
                <div class="card-emerald">
                    <h3 class="mb-4 text-lg font-semibold text-emerald-800">Performa Hari Ini</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-emerald-600">Target Transaksi</span>
                            <span class="text-sm font-medium text-emerald-700">28/30</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-emerald-100">
                            <div class="h-2 rounded-full bg-emerald-gradient-primary" style="width: 93%"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-emerald-600">Target Penjualan</span>
                            <span class="text-sm font-medium text-emerald-700">Rp 1.25jt/1.5jt</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-emerald-100">
                            <div class="h-2 rounded-full bg-emerald-gradient-secondary" style="width: 83%"></div>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="rounded-xl border border-emerald-200 bg-emerald-gradient-soft p-6 shadow-emerald">
                    <div class="flex items-start space-x-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-200 text-emerald-700">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h4 class="mb-2 font-semibold text-emerald-800">Tips Hari Ini</h4>
                            <p class="text-sm text-emerald-700">
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
