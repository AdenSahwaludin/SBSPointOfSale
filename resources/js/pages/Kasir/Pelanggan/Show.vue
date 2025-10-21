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
    alamat: string | null;
    trust_score: number;
    credit_limit: number;
    created_at: string;
}

interface Props {
    pelanggan: Pelanggan;
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/customers');
const showDeleteModal = ref(false);

function confirmDelete() {
    showDeleteModal.value = true;
}

function deletePelanggan() {
    router.delete(`/kasir/customers/${props.pelanggan.id_pelanggan}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
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
</script>

<template>
    <Head title="Detail Pelanggan - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Pelanggan</h1>
                    <p class="text-emerald-600">{{ pelanggan.nama }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/customers')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Main Info Card -->
            <div class="card-emerald">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600">
                        <i class="fas fa-user text-2xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-emerald-800">{{ pelanggan.nama }}</h2>
                        <p class="text-emerald-600">ID: {{ pelanggan.id_pelanggan }}</p>
                    </div>
                </div>

                <div class="border-t border-emerald-100 pt-6">
                    <!-- Contact Information -->
                    <div class="mb-6">
                        <h3 class="mb-3 font-medium text-emerald-700">Informasi Kontak</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="rounded-lg bg-emerald-50 p-3">
                                <div class="text-xs tracking-wider text-emerald-600 uppercase">Email</div>
                                <div class="mt-1 text-sm text-emerald-800">
                                    {{ pelanggan.email || '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-emerald-50 p-3">
                                <div class="text-xs tracking-wider text-emerald-600 uppercase">Telepon</div>
                                <div class="mt-1 text-sm text-emerald-800">
                                    {{ pelanggan.telepon || '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-6">
                        <h3 class="mb-3 font-medium text-emerald-700">Alamat</h3>
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <div class="text-sm text-emerald-800">
                                {{ pelanggan.alamat || '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Trust Score & Credit Limit -->
                    <div class="mb-6">
                        <h3 class="mb-3 font-medium text-emerald-700">Informasi Kredit (Read-only)</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="rounded-lg bg-emerald-50 p-3">
                                <div class="text-xs tracking-wider text-emerald-600 uppercase">Trust Score</div>
                                <div class="mt-2">
                                    <span
                                        :class="getTrustScoreColor(pelanggan.trust_score)"
                                        class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                                    >
                                        {{ pelanggan.trust_score }}/100
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-emerald-600">Diatur oleh Admin</p>
                            </div>
                            <div class="rounded-lg bg-emerald-50 p-3">
                                <div class="text-xs tracking-wider text-emerald-600 uppercase">Credit Limit</div>
                                <div class="mt-1 text-sm font-medium text-emerald-800">
                                    {{ formatCurrency(pelanggan.credit_limit) }}
                                </div>
                                <p class="mt-2 text-xs text-emerald-600">Diatur oleh Admin</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg bg-gray-50 p-3">
                        <div class="text-xs tracking-wider text-gray-600 uppercase">Tanggal Daftar</div>
                        <div class="mt-1 text-sm text-gray-800">
                            {{ formatDate(pelanggan.created_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <BaseButton @click="$inertia.visit(`/kasir/customers/${pelanggan.id_pelanggan}/edit`)" variant="primary" icon="fas fa-edit">
                    Edit Pelanggan
                </BaseButton>
                <BaseButton @click="confirmDelete" variant="danger" icon="fas fa-trash"> Hapus </BaseButton>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[2px]">
            <div class="shadow-emerald-xl mx-4 max-w-md rounded-lg bg-white-emerald p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-emerald-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus pelanggan ini?</p>
                    </div>
                </div>

                <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-emerald-800">{{ pelanggan.nama }}</div>
                        <div class="text-emerald-600">ID: {{ pelanggan.id_pelanggan }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <BaseButton @click="showDeleteModal = false" variant="secondary"> Batal </BaseButton>
                    <BaseButton @click="deletePelanggan" variant="danger"> Hapus </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
