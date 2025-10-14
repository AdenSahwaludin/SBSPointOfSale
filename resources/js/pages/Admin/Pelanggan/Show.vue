<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

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

interface Props {
    pelanggan: Pelanggan;
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pelanggan');

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
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
</script>

<template>
    <Head :title="`Detail Pelanggan ${pelanggan.nama} - Admin`" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Pelanggan</h1>
                    <p class="text-emerald-600">Informasi lengkap pelanggan {{ pelanggan.nama }}</p>
                </div>
                <div class="flex gap-3">
                    <BaseButton @click="$inertia.visit('/admin/pelanggan')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
                    <BaseButton @click="$inertia.visit(`/admin/pelanggan/${pelanggan.id_pelanggan}/edit`)" variant="primary" icon="fas fa-edit">
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
                                @click="$inertia.visit(`/admin/pelanggan/${pelanggan.id_pelanggan}/edit`)"
                                variant="primary"
                                icon="fas fa-edit"
                                custom-class="w-full justify-center"
                            >
                                Edit Pelanggan
                            </BaseButton>

                            <BaseButton variant="secondary" icon="fas fa-history" custom-class="w-full justify-center" disabled>
                                Riwayat Transaksi
                            </BaseButton>

                            <BaseButton variant="secondary" icon="fas fa-print" custom-class="w-full justify-center" disabled>
                                Cetak Info
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
