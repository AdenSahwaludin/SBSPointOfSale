<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    telepon: string | null;
}

interface Kasir {
    id_pengguna: string;
    nama: string;
}

interface Pembayaran {
    id_pembayaran: string;
    id_pelanggan: string;
    jumlah: number;
    metode: string;
    keterangan: string | null;
    tanggal: string;
    created_at: string;
    pelanggan: Pelanggan;
    kasir: Kasir | null;
}

interface Props {
    pembayaran: Pembayaran;
}

const props = defineProps<Props>();
const { formatCurrency } = useCurrencyFormat();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pembayaran-kredit');

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMetode(metode: string) {
    const methods: Record<string, string> = {
        tunai: 'Tunai',
        transfer: 'Transfer Bank',
        cek: 'Cek',
    };
    return methods[metode] || metode;
}

function printReceipt() {
    window.print();
}
</script>

<template>
    <Head title="Bukti Pembayaran Kredit - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Bukti Pembayaran Kredit</h1>
                    <p class="text-emerald-600">{{ pembayaran.pelanggan.nama }}</p>
                </div>
                <div class="flex gap-3">
                    <BaseButton @click="printReceipt" variant="secondary" icon="fas fa-print"> Cetak </BaseButton>
                    <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" variant="secondary" icon="fas fa-arrow-left">
                        Kembali
                    </BaseButton>
                </div>
            </div>

            <!-- Receipt Content -->
            <div class="card-emerald">
                <div class="print-content space-y-6">
                    <!-- Header -->
                    <div class="border-b border-emerald-300 pb-4">
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-emerald-800">SBS - Sari Bumi Sakti</h2>
                            <p class="text-sm text-emerald-600">Bukti Pembayaran Kredit</p>
                            <p class="text-xs text-gray-500">No. Bukti: {{ pembayaran.id_pembayaran }}</p>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <h3 class="mb-2 font-bold text-emerald-800">Informasi Pelanggan</h3>
                            <div class="space-y-1 text-sm text-emerald-700">
                                <div><strong>ID:</strong> {{ pembayaran.pelanggan.id_pelanggan }}</div>
                                <div><strong>Nama:</strong> {{ pembayaran.pelanggan.nama }}</div>
                                <div v-if="pembayaran.pelanggan.email"><strong>Email:</strong> {{ pembayaran.pelanggan.email }}</div>
                                <div v-if="pembayaran.pelanggan.telepon"><strong>Telepon:</strong> {{ pembayaran.pelanggan.telepon }}</div>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-2 font-bold text-emerald-800">Informasi Pembayaran</h3>
                            <div class="space-y-1 text-sm text-emerald-700">
                                <div><strong>Tanggal:</strong> {{ formatDate(pembayaran.tanggal) }}</div>
                                <div><strong>Metode:</strong> {{ formatMetode(pembayaran.metode) }}</div>
                                <div v-if="pembayaran.kasir"><strong>Kasir:</strong> {{ pembayaran.kasir.nama }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="border-t border-b border-emerald-300 py-4">
                        <div class="mb-3 flex justify-between">
                            <span class="text-emerald-700">Jumlah Pembayaran:</span>
                            <span class="font-bold text-emerald-800">{{ formatCurrency(pembayaran.jumlah) }}</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="pembayaran.keterangan" class="rounded-lg bg-emerald-50 p-3">
                        <h3 class="mb-2 font-bold text-emerald-800">Keterangan</h3>
                        <p class="text-sm whitespace-pre-wrap text-emerald-700">{{ pembayaran.keterangan }}</p>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-emerald-300 pt-4 text-center text-xs text-gray-600">
                        <p>Bukti pembayaran ini sah dan dapat digunakan sebagai referensi</p>
                        <p class="mt-1">{{ new Date().toLocaleString('id-ID') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <BaseButton
                    @click="$inertia.visit(`/kasir/pembayaran-kredit/${pembayaran.id_pelanggan}/history`)"
                    variant="secondary"
                    icon="fas fa-history"
                >
                    Lihat Riwayat Pembayaran
                </BaseButton>
                <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" variant="primary" icon="fas fa-arrow-left">
                    Kembali ke Daftar
                </BaseButton>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped>
@media print {
    :deep(.no-print) {
        display: none;
    }

    .print-content {
        background: white;
        padding: 0;
    }
}
</style>
