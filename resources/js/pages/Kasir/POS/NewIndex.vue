<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import { useNotifications } from '@/composables/useNotifications';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface Kategori {
    id_kategori: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    harga: number;              // harga per unit/pieces
    stok: number;
    satuan: string;             // pcs, karton, pack
    isi_per_pack: number;       // berapa pcs dalam 1 karton/pack
    kategori: Kategori;
}

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email?: string;
    telepon?: string;
}

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
    diskon_item?: number;
}

const props = defineProps<{
    produk: Produk[];
    kategori: Kategori[];
    pelanggan: Pelanggan[];
    metodeBayar: Record<string, string>;
}>();

const { addNotification } = useNotifications();

// State management
const selectedKategori = ref<number | null>(null);
const searchQuery = ref('');
const barcodeInput = ref('');
const cart = ref<CartItem[]>([]);
const selectedPelanggan = ref<string>('P001'); // Default to 'Umum'
const metodeBayar = ref<string>('TUNAI');
const jumlahBayar = ref<number>(0);
const diskonGlobal = ref<number>(0);
const pajakRate = ref<number>(0);

// Form
const transactionForm = useForm({
    id_pelanggan: 'P001',
    items: [] as any[],
    metode_bayar: 'TUNAI',
    subtotal: 0,
    diskon: 0,
    pajak: 0,
    total: 0,
    jumlah_bayar: 0,
});

// Computed
const filteredProduk = computed(() => {
    let filtered = props.produk;

    if (selectedKategori.value) {
        filtered = filtered.filter((p) => p.kategori.id_kategori === selectedKategori.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter((p) => p.nama.toLowerCase().includes(query) || p.id_produk.includes(query));
    }

    return filtered;
});

const subtotal = computed(() => {
    const result = cart.value.reduce((sum, item) => sum + item.subtotal, 0);
    // Debug logging
    console.log('Subtotal calculation:', {
        cart_items: cart.value.map(item => ({
            nama: item.nama,
            harga_satuan: item.harga_satuan,
            jumlah: item.jumlah,
            subtotal: item.subtotal
        })),
        total_subtotal: result
    });
    return result;
});

const diskon = computed(() => {
    return (subtotal.value * diskonGlobal.value) / 100;
});

const pajak = computed(() => {
    return ((subtotal.value - diskon.value) * pajakRate.value) / 100;
});

const total = computed(() => {
    return subtotal.value - diskon.value + pajak.value;
});

const kembalian = computed(() => {
    if (metodeBayar.value === 'TUNAI' && jumlahBayar.value > 0) {
        return jumlahBayar.value - total.value;
    }
    return 0;
});

const totalItems = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.jumlah, 0);
});

// Methods
function addToCart(produk: Produk, mode: 'unit' | 'pack' = 'unit') {
    const existingItemIndex = cart.value.findIndex((item) => item.id_produk === produk.id_produk && item.mode_qty === mode);

    // Fix: Hitung harga satuan sesuai database schema
    // Database hanya punya field 'harga' dan 'isi_per_pack'
    // Mode pack = harga per karton/pack (biasanya untuk grosir)
    // Mode unit = harga per pieces
    const hargaSatuan = produk.harga; // Selalu gunakan harga per unit/pieces
    
    // Debug logging
    console.log('Adding to cart:', {
        produk: produk.nama,
        mode,
        harga_produk: produk.harga,
        harga_satuan: hargaSatuan,
        isi_per_pack: produk.isi_per_pack
    });

    if (existingItemIndex !== -1) {
        const item = cart.value[existingItemIndex];
        const maxQty = mode === 'pack' ? Math.floor(produk.stok / produk.isi_per_pack) : produk.stok;

        if (item.jumlah < maxQty) {
            item.jumlah++;
            // Fix: Subtotal = jumlah × harga_satuan (sudah benar)
            item.subtotal = item.jumlah * item.harga_satuan - (item.diskon_item || 0);
        } else {
            addNotification({
                type: 'warning',
                title: 'Stok tidak cukup!',
            });
        }
    } else {
        const maxQty = mode === 'pack' ? Math.floor(produk.stok / produk.isi_per_pack) : produk.stok;

        if (maxQty > 0) {
            cart.value.push({
                id_produk: produk.id_produk,
                nama: produk.nama,
                harga_satuan: hargaSatuan,
                jumlah: 1,
                mode_qty: mode,
                // Fix: Subtotal = harga_satuan (sudah benar)
                subtotal: hargaSatuan,
                stok: produk.stok,
                satuan: produk.satuan,
                isi_per_pack: produk.isi_per_pack,
                diskon_item: 0,
            });
        } else {
            addNotification({
                type: 'warning',
                title: 'Produk tidak tersedia!',
            });
        }
    }
}

function updateQuantity(index: number, quantity: number) {
    const item = cart.value[index];
    const maxQty = item.mode_qty === 'pack' ? Math.floor(item.stok / item.isi_per_pack) : item.stok;

    if (quantity <= 0) {
        removeFromCart(index);
    } else if (quantity <= maxQty) {
        item.jumlah = quantity;
        // Fix: Subtotal = jumlah × harga_satuan (sudah benar)
        item.subtotal = item.jumlah * item.harga_satuan - (item.diskon_item || 0);
    } else {
        addNotification({
            type: 'warning',
            title: 'Jumlah melebihi stok yang tersedia!',
        });
    }
}

function removeFromCart(index: number) {
    cart.value.splice(index, 1);
}

function clearCart() {
    cart.value = [];
    jumlahBayar.value = 0;
}

function handleBarcodeInput() {
    if (barcodeInput.value) {
        const produk = props.produk.find((p) => p.id_produk === barcodeInput.value);
        if (produk) {
            addToCart(produk);
            barcodeInput.value = '';
        } else {
            addNotification({
                type: 'error',
                title: 'Produk dengan barcode tersebut tidak ditemukan!',
            });
        }
    }
}

function processTransaction() {
    if (cart.value.length === 0) {
        addNotification({
            type: 'warning',
            title: 'Keranjang kosong!',
        });
        return;
    }

    if (metodeBayar.value === 'TUNAI' && jumlahBayar.value < total.value) {
        addNotification({
            type: 'warning',
            title: 'Jumlah bayar kurang!',
        });
        return;
    }

    // Debug: Log payment method
    console.log('Payment method selected:', metodeBayar.value);

    // Update form data
    transactionForm.id_pelanggan = selectedPelanggan.value;
    transactionForm.items = cart.value;
    transactionForm.metode_bayar = metodeBayar.value;
    transactionForm.subtotal = subtotal.value;
    transactionForm.diskon = diskon.value;
    transactionForm.pajak = pajak.value;
    transactionForm.total = total.value;
    transactionForm.jumlah_bayar = jumlahBayar.value;

    const requestData = {
        id_pelanggan: selectedPelanggan.value,
        items: cart.value,
        metode_bayar: metodeBayar.value,
        subtotal: subtotal.value,
        diskon: diskon.value,
        pajak: pajak.value,
        total: total.value,
        jumlah_bayar: jumlahBayar.value,
    };

    console.log('Sending request data:', requestData);

    // Use axios for better response handling
    fetch('/kasir/pos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    })
        .then((response) => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then((data) => {
            console.log('Response data:', data);
            if (data.success) {
                // Handle successful transaction
                addNotification({
                    type: 'success',
                    title: 'Transaksi berhasil disimpan!',
                });
                clearCart();
                resetForm();
            } else {
                addNotification({
                    type: 'error',
                    title: data.message || 'Gagal menyimpan transaksi!',
                });
            }
        })
        .catch((error) => {
            addNotification({
                type: 'error',
                title: 'Gagal menyimpan transaksi!',
            });
            console.error('Transaction error:', error);
        })
        .finally(() => {
            transactionForm.processing = false;
        });
}

function resetForm() {
    selectedPelanggan.value = 'P001';
    metodeBayar.value = 'TUNAI';
    diskonGlobal.value = 0;
    jumlahBayar.value = 0;
}

// Format currency
function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Auto focus barcode input on mount
onMounted(() => {
    const barcodeEl = document.getElementById('barcode-input') as HTMLInputElement;
    if (barcodeEl) {
        barcodeEl.focus();
    }
});

// Keyboard shortcuts
onMounted(() => {
    const handleKeydown = (e: KeyboardEvent) => {
        // F1 - Focus barcode input
        if (e.key === 'F1') {
            e.preventDefault();
            const barcodeEl = document.getElementById('barcode-input') as HTMLInputElement;
            if (barcodeEl) {
                barcodeEl.focus();
            }
        }

        // F2 - Process transaction
        if (e.key === 'F2') {
            e.preventDefault();
            processTransaction();
        }

        // F3 - Clear cart
        if (e.key === 'F3') {
            e.preventDefault();
            clearCart();
        }
    };

    document.addEventListener('keydown', handleKeydown);

    return () => {
        document.removeEventListener('keydown', handleKeydown);
    };
});

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pos');
</script>

<template>
    <Head title="Point of Sale - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <template #header> Point of Sale </template>

        <div class="flex h-[calc(100vh-8rem)] gap-6">
            <!-- Left Panel - Products -->
            <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <!-- Header Controls -->
                <div class="border-b border-gray-100 p-6">
                    <!-- Barcode Scanner -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700"> <i class="fas fa-barcode mr-2"></i>Scan Barcode (F1) </label>
                        <input
                            id="barcode-input"
                            v-model="barcodeInput"
                            @keyup.enter="handleBarcodeInput"
                            type="text"
                            placeholder="Scan atau ketik barcode..."
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- Search and Filter -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari produk..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <select
                            v-model="selectedKategori"
                            class="rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option :value="null">Semua Kategori</option>
                            <option v-for="kategori in props.kategori" :key="kategori.id_kategori" :value="kategori.id_kategori">
                                {{ kategori.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="produk in filteredProduk"
                            :key="produk.id_produk"
                            class="cursor-pointer rounded-xl border-2 border-transparent bg-gray-50 p-4 transition-colors hover:border-emerald-200 hover:bg-gray-100"
                            @click="addToCart(produk)"
                        >
                            <div class="text-center">
                                <div class="mb-1 text-xs text-gray-500">{{ produk.id_produk }}</div>
                                <h3 class="mb-2 line-clamp-2 font-medium text-gray-900">{{ produk.nama }}</h3>
                                <p class="mb-2 text-lg font-bold text-emerald-600">{{ formatCurrency(produk.harga) }}</p>
                                <p class="mb-2 text-xs text-gray-500">Stok: {{ produk.stok }} {{ produk.satuan }}</p>

                                <!-- Unit/Pack buttons -->
                                <div class="flex gap-1">
                                    <button
                                        @click.stop="addToCart(produk, 'unit')"
                                        class="flex-1 rounded bg-emerald-100 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-200"
                                    >
                                        Unit
                                    </button>
                                    <button
                                        v-if="produk.isi_per_pack > 1"
                                        @click.stop="addToCart(produk, 'pack')"
                                        class="flex-1 rounded bg-blue-100 px-2 py-1 text-xs text-blue-700 hover:bg-blue-200"
                                    >
                                        Pack
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart & Checkout -->
            <div class="flex w-96 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <!-- Cart Header -->
                <div class="border-b border-gray-100 p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">Keranjang Belanja</h2>

                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Pelanggan</label>
                        <select
                            v-model="selectedPelanggan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="pelanggan in props.pelanggan" :key="pelanggan.id_pelanggan" :value="pelanggan.id_pelanggan">
                                {{ pelanggan.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div v-if="cart.length === 0" class="py-8 text-center text-gray-500">
                        <i class="fas fa-shopping-cart mb-4 text-4xl"></i>
                        <p>Keranjang kosong</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(item, index) in cart" :key="`${item.id_produk}-${item.mode_qty}`" class="rounded-lg bg-gray-50 p-3">
                            <div class="mb-2 flex items-start justify-between">
                                <h4 class="line-clamp-2 text-sm font-medium text-gray-900">{{ item.nama }}</h4>
                                <button @click="removeFromCart(index)" class="text-xs text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ formatCurrency(item.harga_satuan) }} / {{ item.mode_qty }}
                                    <span v-if="item.mode_qty === 'pack'">({{ item.isi_per_pack }} {{ item.satuan }})</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="updateQuantity(index, item.jumlah - 1)"
                                        class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-xs hover:bg-gray-300"
                                    >
                                        -
                                    </button>
                                    <input
                                        :value="item.jumlah"
                                        @input="updateQuantity(index, parseInt(($event.target as HTMLInputElement).value) || 0)"
                                        type="number"
                                        min="1"
                                        class="w-12 rounded border text-center text-xs"
                                    />
                                    <button
                                        @click="updateQuantity(index, item.jumlah + 1)"
                                        class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-xs hover:bg-gray-300"
                                    >
                                        +
                                    </button>
                                </div>
                                <span class="text-sm font-bold text-emerald-600">
                                    {{ formatCurrency(item.subtotal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Section -->
                <div class="space-y-4 border-t border-gray-100 p-6">
                    <!-- Discount -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Diskon (%)</span>
                        <input
                            v-model.number="diskonGlobal"
                            type="number"
                            min="0"
                            max="100"
                            class="w-20 rounded border border-gray-300 px-2 py-1 text-right text-sm"
                        />
                    </div>

                    <!-- Summary -->
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal ({{ totalItems }} item)</span>
                            <span>{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div v-if="diskon > 0" class="flex justify-between text-red-600">
                            <span>Diskon</span>
                            <span>-{{ formatCurrency(diskon) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pajak ({{ pajakRate }}%)</span>
                            <span>{{ formatCurrency(pajak) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                            <span>Total</span>
                            <span class="text-emerald-600">{{ formatCurrency(total) }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Metode Bayar</label>
                        <select
                            v-model="metodeBayar"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="(label, value) in props.metodeBayar" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Cash Payment -->
                    <div v-if="metodeBayar === 'TUNAI'">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Jumlah Bayar</label>
                        <input
                            v-model.number="jumlahBayar"
                            type="number"
                            :min="total"
                            step="1000"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            placeholder="0"
                        />
                        <div v-if="kembalian > 0" class="mt-2 text-sm">
                            <span class="text-gray-600">Kembalian: </span>
                            <span class="font-bold text-green-600">{{ formatCurrency(kembalian) }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <BaseButton
                            @click="processTransaction"
                            :disabled="cart.length === 0 || transactionForm.processing"
                            variant="primary"
                            class="w-full"
                            :loading="transactionForm.processing"
                        >
                            <i class="fas fa-credit-card mr-2"></i>
                            Bayar (F2)
                        </BaseButton>

                        <BaseButton @click="clearCart" variant="outline" class="w-full">
                            <i class="fas fa-trash mr-2"></i>
                            Clear (F3)
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
