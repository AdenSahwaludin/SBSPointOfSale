<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    saldo_kredit: number;
    credit_limit: number;
}

interface Pembayaran {
    id_pembayaran: string;
    jumlah: number;
    metode: string;
    tanggal: string;
    keterangan: string | null;
}

interface PaginatedData {
    data: Pembayaran[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

interface Props {
    pelanggan: Pelanggan;
    pembayaran: PaginatedData;
}

const props = defineProps<Props>();
const { formatCurrency } = useCurrencyFormat();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pembayaran-kredit');

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

function formatMetode(metode: string) {
    const methods: Record<string, string> = {
        tunai: 'Tunai',
        transfer: 'Transfer',
        cek: 'Cek',
    };
    return methods[metode] || metode;
}
</script>

<template>
    <Head title="Riwayat Pembayaran - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Riwayat Pembayaran Kredit</h1>
                    <p class="text-emerald-600">{{ pelanggan.nama }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Customer Summary Card -->
            <div class="card-emerald">
                <h3 class="mb-4 font-bold text-emerald-800">Ringkasan Kredit</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                        <div class="text-xs tracking-wider text-blue-600 uppercase">Limit Kredit</div>
                        <div class="mt-1 text-lg font-bold text-blue-800">{{ formatCurrency(pelanggan.credit_limit) }}</div>
                    </div>

                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                        <div class="text-xs tracking-wider text-yellow-600 uppercase">Saldo Terhutang</div>
                        <div class="mt-1 text-lg font-bold text-yellow-800">{{ formatCurrency(pelanggan.saldo_kredit) }}</div>
                    </div>

                    <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                        <div class="text-xs tracking-wider text-green-600 uppercase">Sisa Tersedia</div>
                        <div class="mt-1 text-lg font-bold text-green-800">
                            {{ formatCurrency(pelanggan.credit_limit - pelanggan.saldo_kredit) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History Table -->
            <div class="card-emerald overflow-hidden">
                <h3 class="mb-4 font-bold text-emerald-800">Daftar Pembayaran</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Metode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="item in pembayaran.data"
                                :key="item.id_pembayaran"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ formatDate(item.tanggal) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-green-700">{{ formatCurrency(item.jumlah) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700"
                                    >
                                        {{ formatMetode(item.metode) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="item.keterangan" class="text-sm text-emerald-700">
                                        {{ item.keterangan.substring(0, 50) }}{{ item.keterangan.length > 50 ? '...' : '' }}
                                    </div>
                                    <div v-else class="text-sm text-gray-400">-</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="pembayaran.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-file-invoice-dollar mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada pembayaran</h3>
                    <p class="text-emerald-600">Riwayat pembayaran akan muncul di sini</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <BaseButton @click="$inertia.visit(`/kasir/pembayaran-kredit/create/${pelanggan.id_pelanggan}`)" variant="primary" icon="fas fa-plus">
                    Tambah Pembayaran
                </BaseButton>
                <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" variant="secondary" icon="fas fa-arrow-left">
                    Kembali ke Daftar
                </BaseButton>
            </div>
        </div>
    </BaseLayout>
</template>
