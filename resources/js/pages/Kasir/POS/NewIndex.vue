<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import BaseButton from '@/components/BaseButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { useNotifications } from '@/composables/useNotifications';

interface Kategori {
    id_kategori: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    harga: number;
    stok: number;
    unit: string;
    pack_unit: string;
    pack_size: number;
    harga_pack?: number;
    kategori: Kategori;
    formatted_price: string;
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
    unit: string;
    pack_size: number;
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
const pajakRate = ref<number>(10); // 10%

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
        filtered = filtered.filter(p => p.kategori.id_kategori === selectedKategori.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            p.nama.toLowerCase().includes(query) ||
            p.id_produk.includes(query)
        );
    }

    return filtered;
});

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.subtotal, 0);
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
    const existingItemIndex = cart.value.findIndex(
        item => item.id_produk === produk.id_produk && item.mode_qty === mode
    );

    const hargaSatuan = mode === 'pack' ? (produk.harga_pack || produk.harga * produk.pack_size) : produk.harga;

    if (existingItemIndex !== -1) {
        const item = cart.value[existingItemIndex];
        const maxQty = mode === 'pack' ? Math.floor(produk.stok / produk.pack_size) : produk.stok;
        
        if (item.jumlah < maxQty) {
            item.jumlah++;
            item.subtotal = item.jumlah * item.harga_satuan - (item.diskon_item || 0);
        } else {
            addNotification({
                type: 'warning',
                title: 'Stok tidak cukup!'
            });
        }
    } else {
        const maxQty = mode === 'pack' ? Math.floor(produk.stok / produk.pack_size) : produk.stok;
        
        if (maxQty > 0) {
            cart.value.push({
                id_produk: produk.id_produk,
                nama: produk.nama,
                harga_satuan: hargaSatuan,
                jumlah: 1,
                mode_qty: mode,
                subtotal: hargaSatuan,
                stok: produk.stok,
                unit: produk.unit,
                pack_size: produk.pack_size,
                diskon_item: 0,
            });
        } else {
            addNotification({
                type: 'warning',
                title: 'Produk tidak tersedia!'
            });
        }
    }
}

function updateQuantity(index: number, quantity: number) {
    const item = cart.value[index];
    const maxQty = item.mode_qty === 'pack' ? Math.floor(item.stok / item.pack_size) : item.stok;
    
    if (quantity <= 0) {
        removeFromCart(index);
    } else if (quantity <= maxQty) {
        item.jumlah = quantity;
        item.subtotal = item.jumlah * item.harga_satuan - (item.diskon_item || 0);
    } else {
        addNotification({
            type: 'warning',
            title: 'Jumlah melebihi stok yang tersedia!'
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
        const produk = props.produk.find(p => p.id_produk === barcodeInput.value);
        if (produk) {
            addToCart(produk);
            barcodeInput.value = '';
        } else {
            addNotification({
                type: 'error',
                title: 'Produk dengan barcode tersebut tidak ditemukan!'
            });
        }
    }
}

function processTransaction() {
    if (cart.value.length === 0) {
        addNotification({
            type: 'warning',
            title: 'Keranjang kosong!'
        });
        return;
    }

    if (metodeBayar.value === 'TUNAI' && jumlahBayar.value < total.value) {
        addNotification({
            type: 'warning',
            title: 'Jumlah bayar kurang!'
        });
        return;
    }

    // Update form data
    transactionForm.id_pelanggan = selectedPelanggan.value;
    transactionForm.items = cart.value;
    transactionForm.metode_bayar = metodeBayar.value;
    transactionForm.subtotal = subtotal.value;
    transactionForm.diskon = diskon.value;
    transactionForm.pajak = pajak.value;
    transactionForm.total = total.value;
    transactionForm.jumlah_bayar = jumlahBayar.value;

    transactionForm.post('/kasir/pos-new', {
        onSuccess: (response) => {
            addNotification({
                type: 'success',
                title: 'Transaksi berhasil disimpan!'
            });
            clearCart();
            // Reset form
            selectedPelanggan.value = 'P001';
            metodeBayar.value = 'TUNAI';
            diskonGlobal.value = 0;
        },
        onError: (errors) => {
            addNotification({
                type: 'error',
                title: 'Gagal menyimpan transaksi!'
            });
        }
    });
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

const kasirMenuItems = [
    {
        name: 'Dashboard',
        href: '/kasir',
        icon: 'fas fa-tachometer-alt',
    },
    {
        name: 'Point of Sale',
        href: '/kasir/pos-new',
        icon: 'fas fa-cash-register',
        active: true,
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-receipt',
        children: [
            { name: 'Riwayat Transaksi', href: '/kasir/transactions', icon: 'fas fa-history' },
            { name: 'Transaksi Hari Ini', href: '/kasir/transactions/today', icon: 'fas fa-calendar-day' },
        ],
    },
    {
        name: 'Profile',
        href: '/kasir/profile',
        icon: 'fas fa-user-circle',
    },
];
</script>

<template>
    <Head title="Point of Sale - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <template #header> Point of Sale </template>

        <div class="flex gap-6 h-[calc(100vh-8rem)]">
            <!-- Left Panel - Products -->
            <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Controls -->
                <div class="p-6 border-b border-gray-100">
                    <!-- Barcode Scanner -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-barcode mr-2"></i>Scan Barcode (F1)
                        </label>
                        <input
                            id="barcode-input"
                            v-model="barcodeInput"
                            @keyup.enter="handleBarcodeInput"
                            type="text"
                            placeholder="Scan atau ketik barcode..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Search and Filter -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari produk..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                        </div>
                        <select
                            v-model="selectedKategori"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option :value="null">Semua Kategori</option>
                            <option
                                v-for="kategori in props.kategori"
                                :key="kategori.id_kategori"
                                :value="kategori.id_kategori"
                            >
                                {{ kategori.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="produk in filteredProduk"
                            :key="produk.id_produk"
                            class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors cursor-pointer border-2 border-transparent hover:border-emerald-200"
                            @click="addToCart(produk)"
                        >
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">{{ produk.id_produk }}</div>
                                <h3 class="font-medium text-gray-900 mb-2 line-clamp-2">{{ produk.nama }}</h3>
                                <p class="text-lg font-bold text-emerald-600 mb-2">{{ formatCurrency(produk.harga) }}</p>
                                <p class="text-xs text-gray-500 mb-2">Stok: {{ produk.stok }} {{ produk.unit }}</p>
                                
                                <!-- Unit/Pack buttons -->
                                <div class="flex gap-1">
                                    <button
                                        @click.stop="addToCart(produk, 'unit')"
                                        class="flex-1 bg-emerald-100 text-emerald-700 text-xs py-1 px-2 rounded hover:bg-emerald-200"
                                    >
                                        + Unit
                                    </button>
                                    <button
                                        v-if="produk.pack_size > 1"
                                        @click.stop="addToCart(produk, 'pack')"
                                        class="flex-1 bg-blue-100 text-blue-700 text-xs py-1 px-2 rounded hover:bg-blue-200"
                                    >
                                        + Pack
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart & Checkout -->
            <div class="w-96 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Cart Header -->
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Keranjang Belanja</h2>
                    
                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pelanggan</label>
                        <select
                            v-model="selectedPelanggan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option
                                v-for="pelanggan in props.pelanggan"
                                :key="pelanggan.id_pelanggan"
                                :value="pelanggan.id_pelanggan"
                            >
                                {{ pelanggan.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div v-if="cart.length === 0" class="text-center text-gray-500 py-8">
                        <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                        <p>Keranjang kosong</p>
                    </div>
                    
                    <div v-else class="space-y-3">
                        <div
                            v-for="(item, index) in cart"
                            :key="`${item.id_produk}-${item.mode_qty}`"
                            class="bg-gray-50 rounded-lg p-3"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-sm text-gray-900 line-clamp-2">{{ item.nama }}</h4>
                                <button
                                    @click="removeFromCart(index)"
                                    class="text-red-500 hover:text-red-700 text-xs"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-gray-500">
                                    {{ formatCurrency(item.harga_satuan) }} / {{ item.mode_qty }}
                                    <span v-if="item.mode_qty === 'pack'">({{ item.pack_size }} {{ item.unit }})</span>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="updateQuantity(index, item.jumlah - 1)"
                                        class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs"
                                    >
                                        -
                                    </button>
                                    <span class="text-sm font-medium w-8 text-center">{{ item.jumlah }}</span>
                                    <button
                                        @click="updateQuantity(index, item.jumlah + 1)"
                                        class="w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs"
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
                <div class="p-6 border-t border-gray-100 space-y-4">
                    <!-- Discount -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Diskon (%)</span>
                        <input
                            v-model.number="diskonGlobal"
                            type="number"
                            min="0"
                            max="100"
                            class="w-20 px-2 py-1 text-right border border-gray-300 rounded text-sm"
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
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total</span>
                            <span class="text-emerald-600">{{ formatCurrency(total) }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Bayar</label>
                        <select
                            v-model="metodeBayar"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option
                                v-for="(label, value) in props.metodeBayar"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Cash Payment -->
                    <div v-if="metodeBayar === 'TUNAI'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                        <input
                            v-model.number="jumlahBayar"
                            type="number"
                            :min="total"
                            step="1000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
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
                        
                        <BaseButton
                            @click="clearCart"
                            variant="outline"
                            class="w-full"
                        >
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