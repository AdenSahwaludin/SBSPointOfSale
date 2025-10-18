<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="$emit('cancel')"
        >
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-xl">
                <!-- Header -->
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white p-6">
                    <h3 class="text-xl font-bold text-gray-900">Konfirmasi Transaksi</h3>
                    <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="space-y-6 p-6">
                    <!-- Summary Info -->
                    <div class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-4">
                        <div>
                            <p class="text-xs text-gray-600">Pelanggan</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ transaction.pelanggan_nama }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Metode Bayar</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ transaction.metode_bayar }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Total Items</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ transaction.items.length }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Total</p>
                            <p class="mt-1 font-semibold text-emerald-600">{{ formatCurrency(transaction.total) }}</p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div>
                        <h4 class="mb-3 text-lg font-semibold text-gray-900">Daftar Item</h4>
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Produk</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Harga</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="item in transaction.items" :key="item.id_produk">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div class="font-medium">{{ item.nama }}</div>
                                            <div class="text-xs text-gray-500">{{ item.satuan }}/{{ item.mode_qty }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                                            {{ item.jumlah }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900">
                                            {{ formatCurrency(item.harga_satuan) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(item.subtotal) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary Calculation -->
                    <div class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(transaction.subtotal) }}</span>
                        </div>
                        <div v-if="transaction.diskon > 0" class="flex justify-between text-sm">
                            <span class="text-gray-600">Diskon</span>
                            <span class="font-medium text-red-600">-{{ formatCurrency(transaction.diskon) }}</span>
                        </div>
                        <div v-if="transaction.pajak > 0" class="flex justify-between text-sm">
                            <span class="text-gray-600">Pajak</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(transaction.pajak) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-300 pt-3 text-base font-bold">
                            <span class="text-gray-900">Total Bayar</span>
                            <span class="text-emerald-600">{{ formatCurrency(transaction.total) }}</span>
                        </div>
                        <div v-if="transaction.metode_bayar === 'TUNAI' && transaction.jumlah_bayar" class="flex justify-between border-t border-gray-300 pt-3 text-sm">
                            <span class="text-gray-600">Jumlah Bayar</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(transaction.jumlah_bayar) }}</span>
                        </div>
                        <div
                            v-if="transaction.metode_bayar === 'TUNAI' && transaction.jumlah_bayar && kembalian > 0"
                            class="flex justify-between border-t border-gray-300 pt-3 text-sm"
                        >
                            <span class="text-gray-600">Kembalian</span>
                            <span class="font-bold text-green-600">{{ formatCurrency(kembalian) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 border-t border-gray-200 p-6">
                    <BaseButton @click="$emit('cancel')" variant="outline" class="flex-1">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </BaseButton>
                    <BaseButton @click="handlePrint" variant="secondary" class="flex-1">
                        <i class="fas fa-print mr-2"></i>
                        Cetak Struk
                    </BaseButton>
                    <BaseButton @click="$emit('confirm')" variant="primary" class="flex-1">
                        <i class="fas fa-check mr-2"></i>
                        Selesaikan
                    </BaseButton>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import BaseButton from '@/components/BaseButton.vue';
import { computed } from 'vue';

interface CartItem {
    id_produk: string;
    nama: string;
    harga_satuan: number;
    jumlah: number;
    mode_qty: 'unit' | 'pack';
    subtotal: number;
    stok: number;
    satuan: string;
    isi_per_pack: number;
}

interface TransactionData {
    pelanggan_nama: string;
    metode_bayar: string;
    items: CartItem[];
    subtotal: number;
    diskon: number;
    pajak: number;
    total: number;
    jumlah_bayar?: number;
}

interface Props {
    show: boolean;
    transaction: TransactionData;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
    print: [];
}>();

const kembalian = computed(() => {
    if (props.transaction.metode_bayar === 'TUNAI' && props.transaction.jumlah_bayar) {
        return props.transaction.jumlah_bayar - props.transaction.total;
    }
    return 0;
});

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function handlePrint() {
    emit('print');
}
</script>

<style scoped>
/* Additional styles if needed */
</style>
