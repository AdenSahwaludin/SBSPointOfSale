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
}

interface Kasir {
    id_pengguna: string;
    nama: string;
}

interface TransaksiDetail {
    id_detail: number;
    nama_produk: string;
    harga_satuan: number;
    jumlah: number;
    subtotal: number;
}

interface JadwalAngsuran {
    id_angsuran: number;
    id_kontrak: number;
    periode_ke: number;
    jatuh_tempo: string;
    jumlah_tagihan: number;
    jumlah_dibayar: number;
    status: string;
    paid_at: string | null;
}

interface KontrakKredit {
    id_kontrak: number;
    nomor_kontrak: string;
    id_pelanggan: string;
    nomor_transaksi: string;
    mulai_kontrak: string;
    tenor_bulan: number;
    pokok_pinjaman: number;
    dp: number;
    bunga_persen: number;
    cicilan_bulanan: number;
    status: string;
    jadwalAngsuran: JadwalAngsuran[];
}

interface Transaksi {
    nomor_transaksi: string;
    id_pelanggan: string;
    id_kasir: string;
    tanggal: string;
    total_item: number;
    subtotal: number;
    diskon: number;
    pajak: number;
    biaya_pengiriman: number;
    total: number;
    metode_bayar: string;
    status_pembayaran: string;
    jenis_transaksi: string;
    pelanggan: Pelanggan;
    kasir: Kasir;
    detail: TransaksiDetail[];
    kontrakKredit: KontrakKredit | null;
}

interface Props {
    transaksi: Transaksi;
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

function getStatusColor(status: string) {
    const statusLower = status.toLowerCase();
    if (statusLower === 'lunas') return 'text-green-700 bg-green-100 border-green-200';
    if (statusLower === 'menunggu') return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    if (statusLower === 'batal') return 'text-red-700 bg-red-100 border-red-200';
    return 'text-gray-700 bg-gray-100 border-gray-200';
}

function getAngsuranStatusColor(status: string) {
    const statusLower = status.toLowerCase();
    if (statusLower === 'lunas') return 'text-green-700 bg-green-100 border-green-200';
    if (statusLower === 'belum') return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    if (statusLower === 'tertunggak') return 'text-red-700 bg-red-100 border-red-200';
    return 'text-gray-700 bg-gray-100 border-gray-200';
}
</script>

<template>
    <Head :title="`Detail Transaksi ${transaksi.nomor_transaksi}`" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Transaksi</h1>
                    <p class="text-emerald-600">{{ transaksi.nomor_transaksi }}</p>
                </div>
                <BaseButton
                    @click="$inertia.visit(`/admin/pelanggan/${transaksi.pelanggan.id_pelanggan}`)"
                    variant="secondary"
                    icon="fas fa-arrow-left"
                >
                    Kembali ke Pelanggan
                </BaseButton>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Transaction Details -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Transaction Info -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi Transaksi</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">No. Transaksi</label>
                                <div class="text-emerald-800">{{ transaksi.nomor_transaksi }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Tanggal</label>
                                <div class="text-emerald-800">{{ formatDate(transaksi.tanggal) }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Tipe Transaksi</label>
                                <div class="text-emerald-800">{{ transaksi.jenis_transaksi }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Metode Bayar</label>
                                <div class="text-emerald-800">{{ transaksi.metode_bayar }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Data Pelanggan</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">ID Pelanggan</label>
                                <div class="text-emerald-800">{{ transaksi.pelanggan.id_pelanggan }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Nama Pelanggan</label>
                                <div class="text-emerald-800">{{ transaksi.pelanggan.nama }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Email</label>
                                <div class="text-emerald-800">{{ transaksi.pelanggan.email || '-' }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-emerald-700">Telepon</label>
                                <div class="text-emerald-800">{{ transaksi.pelanggan.telepon || '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Item Transaksi</h3>
                        <div v-if="transaksi.detail.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b-2 border-emerald-200 bg-emerald-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-emerald-800">Produk</th>
                                        <th class="px-4 py-3 text-right font-semibold text-emerald-800">Jumlah</th>
                                        <th class="px-4 py-3 text-right font-semibold text-emerald-800">Harga</th>
                                        <th class="px-4 py-3 text-right font-semibold text-emerald-800">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100">
                                    <tr v-for="item in transaksi.detail" :key="item.id_detail">
                                        <td class="px-4 py-3 text-emerald-700">{{ item.nama_produk }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">{{ item.jumlah }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">{{ formatCurrency(item.harga_satuan) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-emerald-800">{{ formatCurrency(item.subtotal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-emerald-600">Tidak ada item</p>
                        </div>
                    </div>

                    <!-- Installments (for KREDIT transactions) -->
                    <div v-if="transaksi.jenis_transaksi === 'KREDIT' && transaksi.kontrakKredit" class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Jadwal Angsuran</h3>
                        <div class="mb-4 space-y-2 rounded-lg bg-emerald-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">Nomor Kontrak</label>
                                    <p class="text-emerald-800">{{ transaksi.kontrakKredit.nomor_kontrak }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">Tenor</label>
                                    <p class="text-emerald-800">{{ transaksi.kontrakKredit.tenor_bulan }} bulan</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">Pokok Pinjaman</label>
                                    <p class="text-emerald-800">{{ formatCurrency(transaksi.kontrakKredit.pokok_pinjaman) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">Cicilan Bulanan</label>
                                    <p class="text-emerald-800">{{ formatCurrency(transaksi.kontrakKredit.cicilan_bulanan) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">DP</label>
                                    <p class="text-emerald-800">{{ formatCurrency(transaksi.kontrakKredit.dp) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-emerald-700">Bunga</label>
                                    <p class="text-emerald-800">{{ transaksi.kontrakKredit.bunga_persen }}%</p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="transaksi.kontrakKredit.jadwalAngsuran && transaksi.kontrakKredit.jadwalAngsuran.length > 0"
                            class="overflow-x-auto"
                        >
                            <table class="w-full text-sm">
                                <thead class="border-b-2 border-emerald-200 bg-emerald-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-emerald-800">Periode</th>
                                        <th class="px-4 py-3 text-left font-semibold text-emerald-800">Jatuh Tempo</th>
                                        <th class="px-4 py-3 text-right font-semibold text-emerald-800">Tagihan</th>
                                        <th class="px-4 py-3 text-right font-semibold text-emerald-800">Dibayar</th>
                                        <th class="px-4 py-3 text-left font-semibold text-emerald-800">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100">
                                    <tr v-for="angsuran in transaksi.kontrakKredit.jadwalAngsuran" :key="angsuran.id_angsuran">
                                        <td class="px-4 py-3 text-emerald-700">Bulan ke-{{ angsuran.periode_ke }}</td>
                                        <td class="px-4 py-3 text-emerald-700">{{ formatDate(angsuran.jatuh_tempo) }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">{{ formatCurrency(angsuran.jumlah_tagihan) }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">{{ formatCurrency(angsuran.jumlah_dibayar) }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="getAngsuranStatusColor(angsuran.status)"
                                                class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ angsuran.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-emerald-600">Tidak ada jadwal angsuran</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Status</h3>
                        <div class="text-center">
                            <span
                                :class="getStatusColor(transaksi.status_pembayaran)"
                                class="inline-flex rounded-full border px-4 py-2 text-sm font-semibold"
                            >
                                {{ transaksi.status_pembayaran }}
                            </span>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Ringkasan</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-emerald-700">Subtotal</span>
                                <span class="font-medium text-emerald-800">{{ formatCurrency(transaksi.subtotal) }}</span>
                            </div>
                            <div v-if="transaksi.diskon > 0" class="flex justify-between">
                                <span class="text-emerald-700">Diskon</span>
                                <span class="font-medium text-emerald-800">-{{ formatCurrency(transaksi.diskon) }}</span>
                            </div>
                            <div v-if="transaksi.pajak > 0" class="flex justify-between">
                                <span class="text-emerald-700">Pajak</span>
                                <span class="font-medium text-emerald-800">{{ formatCurrency(transaksi.pajak) }}</span>
                            </div>
                            <div v-if="transaksi.biaya_pengiriman > 0" class="flex justify-between">
                                <span class="text-emerald-700">Biaya Pengiriman</span>
                                <span class="font-medium text-emerald-800">{{ formatCurrency(transaksi.biaya_pengiriman) }}</span>
                            </div>
                            <div class="border-t-2 border-emerald-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-emerald-800">Total</span>
                                    <span class="text-lg font-bold text-emerald-800">{{ formatCurrency(transaksi.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kasir Info -->
                    <div class="card-emerald">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Kasir</h3>
                        <div class="text-emerald-800">{{ transaksi.kasir.nama }}</div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
