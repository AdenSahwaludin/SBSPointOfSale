<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    telepon: string | null;
    saldo_kredit: number;
    credit_limit: number;
}

interface PaginatedData {
    data: Pelanggan[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

interface Props {
    pelanggan: PaginatedData;
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pembayaran-kredit');
const searchQuery = ref(props.filters.search || '');

function handleSearch() {
    router.get('/kasir/pembayaran-kredit', { search: searchQuery.value });
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

function getRemainingCredit(saldo: number, limit: number) {
    return limit - saldo;
}

function getStatusColor(saldo: number, limit: number) {
    const remaining = limit - saldo;
    const percentage = (remaining / limit) * 100;

    if (percentage >= 75) return 'text-green-700 bg-green-100 border-green-200';
    if (percentage >= 50) return 'text-blue-700 bg-blue-100 border-blue-200';
    if (percentage >= 25) return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    return 'text-red-700 bg-red-100 border-red-200';
}
</script>

<template>
    <Head title="Pembayaran Kredit - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Pembayaran Kredit</h1>
                    <p class="text-emerald-600">Kelola pembayaran kredit pelanggan</p>
                </div>
            </div>

            <!-- Search -->
            <div class="card-emerald">
                <div class="flex gap-3">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, ID, atau email pelanggan..."
                        class="flex-1 rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        @keyup.enter="handleSearch"
                    />
                    <BaseButton @click="handleSearch" variant="primary" icon="fas fa-search"> Cari </BaseButton>
                </div>
            </div>

            <!-- Customers with Credit Balance -->
            <div class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">ID Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Saldo Kredit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Limit Kredit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="item in pelanggan.data"
                                :key="item.id_pelanggan"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ item.id_pelanggan }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ item.nama }}</div>
                                    <div v-if="item.email" class="text-xs text-emerald-600">{{ item.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ formatCurrency(item.saldo_kredit) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ formatCurrency(item.credit_limit) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusColor(item.saldo_kredit, item.credit_limit)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ formatCurrency(getRemainingCredit(item.saldo_kredit, item.credit_limit)) }} digunakan
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/kasir/pembayaran-kredit/create/${item.id_pelanggan}`)"
                                            variant="primary"
                                            icon="fas fa-plus"
                                            custom-class="rounded-lg p-2"
                                            title="Bayar Kredit"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/kasir/pembayaran-kredit/${item.id_pelanggan}/history`)"
                                            variant="secondary"
                                            icon="fas fa-history"
                                            custom-class="rounded-lg p-2"
                                            title="Riwayat Pembayaran"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="pelanggan.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-credit-card mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada pelanggan dengan kredit aktif</h3>
                    <p class="text-emerald-600">Pelanggan dengan status kredit aktif akan muncul di sini</p>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
