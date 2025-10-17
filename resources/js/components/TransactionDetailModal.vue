<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
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
    mode_qty: string;
    subtotal: number;
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
    total: number;
    metode_bayar: string;
    status_pembayaran: string;
    pelanggan: Pelanggan;
    kasir: Kasir;
    detail: TransaksiDetail[];
}

interface Props {
    show: boolean;
    transaksi: Transaksi | null;
    showPrintButton?: boolean;
}

interface Emits {
    (e: 'close'): void;
    (e: 'print', transaksi: Transaksi): void;
}

const props = withDefaults(defineProps<Props>(), {
    showPrintButton: false,
});

const emit = defineEmits<Emits>();

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusBadgeClass(status: string): string {
    switch (status) {
        case 'LUNAS':
            return 'bg-green-100 text-green-800';
        case 'MENUNGGU':
            return 'bg-yellow-100 text-yellow-800';
        case 'BATAL':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function handleClose() {
    emit('close');
}

function handlePrint() {
    if (props.transaksi) {
        emit('print', props.transaksi);
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show && transaksi"
            class="modal-bg bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
            @click.self="handleClose"
        >
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white shadow-xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-900">Detail Transaksi</h3>
                    <button @click="handleClose" class="text-gray-400 transition-colors hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Transaction Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Nomor Transaksi</p>
                                <p class="mt-1 text-base font-semibold text-gray-900">{{ transaksi.nomor_transaksi }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Tanggal</p>
                                <p class="mt-1 text-base text-gray-900">{{ formatDate(transaksi.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pelanggan</p>
                                <p class="mt-1 text-base text-gray-900">{{ transaksi.pelanggan.nama }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Kasir</p>
                                <p class="mt-1 text-base text-gray-900">{{ transaksi.kasir.nama }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Metode Bayar</p>
                                <p class="mt-1 text-base text-gray-900">{{ transaksi.metode_bayar }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Status</p>
                                <span
                                    :class="[
                                        'mt-1 inline-flex rounded-full px-3 py-1 text-sm font-semibold',
                                        getStatusBadgeClass(transaksi.status_pembayaran),
                                    ]"
                                >
                                    {{ transaksi.status_pembayaran }}
                                </span>
                            </div>
                        </div>

                        <!-- Items -->
                        <div>
                            <h4 class="mb-3 text-lg font-semibold text-gray-900">Items</h4>
                            <div class="overflow-hidden rounded-lg border border-gray-200">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Produk</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Harga</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Qty</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="item in transaksi.detail" :key="item.id_detail">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ item.nama_produk }}</td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(item.harga_satuan) }}</td>
                                            <td class="px-4 py-3 text-center text-sm text-gray-900">{{ item.jumlah }} {{ item.mode_qty }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                                {{ formatCurrency(item.subtotal) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(transaksi.subtotal) }}</span>
                                </div>
                                <div v-if="transaksi.diskon > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600">Diskon</span>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(transaksi.diskon) }}</span>
                                </div>
                                <div v-if="transaksi.pajak > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600">Pajak</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(transaksi.pajak) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-300 pt-2 text-base">
                                    <span class="font-semibold text-gray-900">Total</span>
                                    <span class="font-bold text-emerald-600">{{ formatCurrency(transaksi.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-2 border-t border-gray-200 p-6">
                    <BaseButton v-if="showPrintButton" @click="handlePrint" variant="primary">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </BaseButton>
                    <BaseButton @click="handleClose" variant="outline"> Tutup </BaseButton>
                </div>
            </div>
        </div>
    </Teleport>
</template>
