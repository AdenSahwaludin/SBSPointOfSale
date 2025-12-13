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
    kota: string | null;
    alamat: string | null;
    aktif: boolean;
    trust_score: number;
    credit_limit: number;
    created_at: string;
    updated_at: string;
}

interface Transaksi {
    nomor_transaksi: string;
    id_pelanggan: string;
    tanggal: string;
    status_pembayaran: string;
    total: number;
    metode_bayar: string;
}

interface Props {
    pelanggan: Pelanggan;
    transaksi: Transaksi[];
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/customers');
const showTransactionHistory = ref(false);

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function viewTransactionDetail(nomorTransaksi: string) {
    router.visit(`/kasir/transactions/${nomorTransaksi}`);
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

function getTrustScoreColor(score: number) {
    if (score >= 80) return 'text-green-700 bg-green-100 border-green-200';
    if (score >= 60) return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    return 'text-red-700 bg-red-100 border-red-200';
}

function getTrustScoreLabel(score: number) {
    if (score >= 80) return 'Excellent';
    if (score >= 60) return 'Good';
    if (score >= 40) return 'Fair';
    return 'Poor';
}

function getStatusColor(status: string) {
    const statusLower = status.toLowerCase();
    if (statusLower === 'lunas') return 'text-green-700 bg-green-100 border-green-200';
    if (statusLower === 'menunggu') return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    if (statusLower === 'batal') return 'text-red-700 bg-red-100 border-red-200';
    return 'text-gray-700 bg-gray-100 border-gray-200';
}
</script>

<template>
    <Head :title="`Detail Pelanggan ${pelanggan.nama} - Kasir`" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Pelanggan</h1>
                    <p class="text-emerald-600">Informasi lengkap pelanggan {{ pelanggan.nama }}</p>
                </div>
                <div class="flex gap-3">
                    <BaseButton @click="$inertia.visit('/kasir/customers')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                    <BaseButton @click="$inertia.visit(`/kasir/customers/${pelanggan.id_pelanggan}/edit`)" variant="primary" icon="fas fa-edit">
                        Edit
                    </BaseButton>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Customer Info -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Basic Information -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">ID Pelanggan</label>
                                <div class="flex items-center">
                                    <div class="mr-3 h-10 w-10 flex-shrink-0">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                            <i class="fas fa-user text-emerald-600"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-medium text-emerald-800">{{ pelanggan.id_pelanggan }}</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Nama Lengkap</label>
                                <div class="text-lg font-medium text-emerald-800">{{ pelanggan.nama }}</div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Email</label>
                                <div class="text-emerald-800">
                                    <span v-if="pelanggan.email">{{ pelanggan.email }}</span>
                                    <span v-else class="text-gray-400 italic">Tidak ada</span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Telepon</label>
                                <div class="text-emerald-800">
                                    <span v-if="pelanggan.telepon">{{ pelanggan.telepon }}</span>
                                    <span v-else class="text-gray-400 italic">Tidak ada</span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Kota</label>
                                <div class="text-emerald-800">
                                    <span v-if="pelanggan.kota">{{ pelanggan.kota }}</span>
                                    <span v-else class="text-gray-400 italic">Tidak ada</span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Status</label>
                                <span
                                    :class="
                                        pelanggan.aktif ? 'border-green-200 bg-green-100 text-green-800' : 'border-red-200 bg-red-100 text-red-800'
                                    "
                                    class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                                >
                                    {{ pelanggan.aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>

                        <!-- Address -->
                        <div v-if="pelanggan.alamat" class="mt-6">
                            <label class="mb-1 block text-sm font-medium text-emerald-700">Alamat</label>
                            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-800">
                                {{ pelanggan.alamat }}
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi Sistem</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Terdaftar Sejak</label>
                                <div class="text-emerald-800">{{ formatDate(pelanggan.created_at) }}</div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Terakhir Diperbarui</label>
                                <div class="text-emerald-800">{{ formatDate(pelanggan.updated_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Stats -->
                <div class="space-y-6">
                    <!-- Trust Score -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Trust Score</h3>
                        <div class="text-center">
                            <div
                                class="mb-2 text-4xl font-bold"
                                :class="getTrustScoreColor(pelanggan.trust_score).replace('bg-', 'text-').replace('-100', '-600')"
                            >
                                {{ pelanggan.trust_score }}
                            </div>
                            <div class="mb-3 text-lg text-emerald-600">/ 100</div>
                            <span
                                :class="getTrustScoreColor(pelanggan.trust_score)"
                                class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                            >
                                {{ getTrustScoreLabel(pelanggan.trust_score) }}
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="h-2 w-full rounded-full bg-gray-200">
                                <div
                                    class="h-2 rounded-full transition-all duration-300"
                                    :class="
                                        pelanggan.trust_score >= 80 ? 'bg-green-500' : pelanggan.trust_score >= 60 ? 'bg-yellow-500' : 'bg-red-500'
                                    "
                                    :style="`width: ${pelanggan.trust_score}%`"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Credit Limit -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Credit Limit</h3>
                        <div class="text-center">
                            <div class="mb-2 text-2xl font-bold text-emerald-800">
                                {{ formatCurrency(pelanggan.credit_limit) }}
                            </div>
                            <div class="text-sm text-emerald-600">
                                {{ pelanggan.credit_limit > 0 ? 'Kredit tersedia' : 'Cash only' }}
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <BaseButton
                                @click="$inertia.visit(`/kasir/customers/${pelanggan.id_pelanggan}/edit`)"
                                variant="primary"
                                icon="fas fa-edit"
                                custom-class="w-full justify-center"
                            >
                                Edit Pelanggan
                            </BaseButton>

                            <BaseButton
                                @click="showTransactionHistory = !showTransactionHistory"
                                variant="secondary"
                                icon="fas fa-history"
                                custom-class="w-full justify-center"
                            >
                                Riwayat Transaksi
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div v-if="showTransactionHistory" class="space-y-6">
                <div class="card-emerald">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-emerald-800">Riwayat Transaksi (10 Terbaru)</h3>
                        <BaseButton
                            @click="showTransactionHistory = false"
                            variant="secondary"
                            icon="fas fa-times"
                            custom-class="px-3 py-1 text-sm"
                        />
                    </div>

                    <div v-if="transaksi.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b-2 border-emerald-200 bg-emerald-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-emerald-800">No. Transaksi</th>
                                    <th class="px-4 py-3 text-left font-semibold text-emerald-800">Tanggal</th>
                                    <th class="px-4 py-3 text-left font-semibold text-emerald-800">Total</th>
                                    <th class="px-4 py-3 text-left font-semibold text-emerald-800">Metode Bayar</th>
                                    <th class="px-4 py-3 text-left font-semibold text-emerald-800">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100">
                                <tr
                                    v-for="trans in transaksi"
                                    :key="trans.nomor_transaksi"
                                    class="cursor-pointer transition-colors hover:bg-emerald-50"
                                    @click="viewTransactionDetail(trans.nomor_transaksi)"
                                >
                                    <td class="px-4 py-3 text-emerald-700">
                                        <span class="font-medium text-emerald-600 hover:text-emerald-800">{{ trans.nomor_transaksi }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-emerald-700">{{ formatDate(trans.tanggal) }}</td>
                                    <td class="px-4 py-3 font-medium text-emerald-800">{{ formatCurrency(trans.total) }}</td>
                                    <td class="px-4 py-3 text-emerald-700">{{ trans.metode_bayar }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="getStatusColor(trans.status_pembayaran)"
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
                        <i class="fas fa-inbox mb-3 text-4xl text-emerald-300"></i>
                        <p class="text-emerald-600">Belum ada transaksi</p>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
