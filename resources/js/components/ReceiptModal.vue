<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="modal-bg" @click.self="closeModal">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <h3 class="text-xl font-bold text-gray-800">Transaksi Berhasil</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Receipt Content -->
                    <div ref="receiptContent" class="modal-body">
                        <!-- Store Info -->
                        <div class="text-center mb-6 border-b pb-4">
                            <h2 class="text-2xl font-bold text-gray-800">SBS POS</h2>
                            <p class="text-sm text-gray-600 mt-1">Struk Pembayaran</p>
                        </div>

                        <!-- Transaction Info -->
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. Transaksi:</span>
                                <span class="font-semibold">{{ transaction.nomor_transaksi }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span>{{ formatDate(transaction.tanggal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kasir:</span>
                                <span>{{ transaction.kasir }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pelanggan:</span>
                                <span>{{ transaction.pelanggan }}</span>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="border-t border-b py-4 mb-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Item</th>
                                        <th class="text-center py-2">Qty</th>
                                        <th class="text-right py-2">Harga</th>
                                        <th class="text-right py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in transaction.items" :key="item.id" class="border-b">
                                        <td class="py-2">{{ item.nama }}</td>
                                        <td class="text-center">{{ item.jumlah }}</td>
                                        <td class="text-right">{{ formatCurrency(item.harga_satuan) }}</td>
                                        <td class="text-right">{{ formatCurrency(item.subtotal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span>{{ formatCurrency(transaction.subtotal) }}</span>
                            </div>
                            <div v-if="transaction.diskon > 0" class="flex justify-between text-red-600">
                                <span>Diskon:</span>
                                <span>-{{ formatCurrency(transaction.diskon) }}</span>
                            </div>
                            <div v-if="transaction.pajak > 0" class="flex justify-between">
                                <span class="text-gray-600">Pajak:</span>
                                <span>{{ formatCurrency(transaction.pajak) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span>{{ formatCurrency(transaction.total) }}</span>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode Bayar:</span>
                                <span class="font-semibold">{{ transaction.metode_bayar }}</span>
                            </div>
                            <div v-if="transaction.metode_bayar === 'TUNAI'" class="flex justify-between">
                                <span class="text-gray-600">Jumlah Bayar:</span>
                                <span>{{ formatCurrency(transaction.jumlah_bayar) }}</span>
                            </div>
                            <div v-if="transaction.kembalian > 0" class="flex justify-between font-bold text-emerald-600">
                                <span>Kembalian:</span>
                                <span>{{ formatCurrency(transaction.kembalian) }}</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center mt-6 pt-4 border-t text-sm text-gray-600">
                            <p>Terima kasih atas kunjungan Anda</p>
                            <p class="text-xs mt-1">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="modal-footer">
                        <button @click="printReceipt" class="btn-print">
                            <i class="fas fa-print mr-2"></i>
                            Print
                        </button>
                        <button @click="closeModal" class="btn-ok">
                            <i class="fas fa-check mr-2"></i>
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Transaction {
    nomor_transaksi: string;
    tanggal: string;
    kasir: string;
    pelanggan: string;
    items: Array<{
        id: string;
        nama: string;
        jumlah: number;
        harga_satuan: number;
        subtotal: number;
    }>;
    subtotal: number;
    diskon: number;
    pajak: number;
    total: number;
    metode_bayar: string;
    jumlah_bayar?: number;
    kembalian: number;
}

interface Props {
    show: boolean;
    transaction: Transaction;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const receiptContent = ref<HTMLElement | null>(null);

function closeModal() {
    emit('close');
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
}

function formatDate(date: string): string {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function printReceipt() {
    if (!receiptContent.value) return;

    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Struk - ${props.transaction.nomor_transaksi}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    padding: 20px;
                    max-width: 300px;
                    margin: 0 auto;
                }
                h2 {
                    text-align: center;
                    font-size: 18px;
                    margin-bottom: 5px;
                }
                .text-center {
                    text-align: center;
                }
                .border-b {
                    border-bottom: 1px dashed #000;
                    padding-bottom: 10px;
                    margin-bottom: 10px;
                }
                .border-t {
                    border-top: 1px dashed #000;
                    padding-top: 10px;
                    margin-top: 10px;
                }
                table {
                    width: 100%;
                    margin: 10px 0;
                }
                th {
                    text-align: left;
                    padding: 5px 0;
                    border-bottom: 1px solid #000;
                }
                td {
                    padding: 3px 0;
                }
                .text-right {
                    text-align: right;
                }
                .font-bold {
                    font-weight: bold;
                }
                .space-y {
                    margin: 5px 0;
                }
                .flex {
                    display: flex;
                    justify-content: space-between;
                    margin: 3px 0;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            ${receiptContent.value.innerHTML}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>

<style scoped>
.modal-bg {
    @apply fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm;
}

.modal-content {
    @apply bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col;
}

.modal-header {
    @apply flex items-center justify-between p-6 border-b;
}

.modal-body {
    @apply p-6 overflow-y-auto flex-1;
}

.modal-footer {
    @apply flex gap-3 p-6 border-t bg-gray-50;
}

.btn-print {
    @apply flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center;
}

.btn-ok {
    @apply flex-1 px-6 py-3 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center;
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
    transition: transform 0.3s ease;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
    transform: scale(0.9);
}
</style>
