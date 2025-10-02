<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import { useNotifications } from '@/composables/useNotifications';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Product {
    id_produk: string;
    nama_produk: string;
    deskripsi?: string;
    harga: number;
    stok: number;
    kategori?: string;
    barcode?: string;
    status: string;
}

interface CartItem {
    id_produk: string;
    nama_produk: string;
    harga: number;
    jumlah: number;
    subtotal: number;
}

interface Props {
    products: Product[];
}

const props = defineProps<Props>();

// Notifications
const { success, error } = useNotifications();

// Menu items dengan active state
const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pos');

// Cart state
const cart = ref<CartItem[]>([]);
const searchQuery = ref('');
const selectedCategory = ref('');
const showPaymentModal = ref(false);
const showMidtransModal = ref(false);
const snapToken = ref('');

// Customer info
const customerForm = useForm({
    nama_pelanggan: '',
    nomor_hp_pelanggan: '',
    metode_pembayaran: 'tunai',
    bayar: 0,
});

// Computed properties
const categories = computed(() => {
    const cats = [...new Set(props.products.map(p => p.kategori).filter(Boolean))];
    return cats;
});

const filteredProducts = computed(() => {
    return props.products.filter(product => {
        const matchesSearch = product.nama_produk.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            product.barcode?.includes(searchQuery.value);
        const matchesCategory = !selectedCategory.value || product.kategori === selectedCategory.value;
        return matchesSearch && matchesCategory && product.status === 'aktif';
    });
});

const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + item.subtotal, 0);
});

const cartItemCount = computed(() => {
    return cart.value.reduce((total, item) => total + item.jumlah, 0);
});

const pajak = computed(() => {
    return Math.round(cartTotal.value * 0.11);
});

const grandTotal = computed(() => {
    return cartTotal.value + pajak.value;
});

const kembalian = computed(() => {
    if (customerForm.metode_pembayaran === 'tunai' && customerForm.bayar > 0) {
        return customerForm.bayar - grandTotal.value;
    }
    return 0;
});

// Methods
function addToCart(product: Product) {
    const existingItem = cart.value.find(item => item.id_produk === product.id_produk);
    
    if (existingItem) {
        if (existingItem.jumlah < product.stok) {
            existingItem.jumlah++;
            existingItem.subtotal = existingItem.harga * existingItem.jumlah;
            success('Produk ditambahkan', `${product.nama_produk} berhasil ditambahkan ke keranjang`);
        } else {
            error('Stok tidak mencukupi', `Stok ${product.nama_produk} hanya tersisa ${product.stok}`);
        }
    } else {
        cart.value.push({
            id_produk: product.id_produk,
            nama_produk: product.nama_produk,
            harga: product.harga,
            jumlah: 1,
            subtotal: product.harga,
        });
        success('Produk ditambahkan', `${product.nama_produk} berhasil ditambahkan ke keranjang`);
    }
}

function updateQuantity(index: number, quantity: number) {
    if (quantity <= 0) {
        cart.value.splice(index, 1);
    } else {
        const item = cart.value[index];
        const product = props.products.find(p => p.id_produk === item.id_produk);
        
        if (product && quantity <= product.stok) {
            item.jumlah = quantity;
            item.subtotal = item.harga * quantity;
        } else {
            error('Stok tidak mencukupi', `Stok ${item.nama_produk} hanya tersisa ${product?.stok || 0}`);
        }
    }
}

function removeFromCart(index: number) {
    cart.value.splice(index, 1);
}

function clearCart() {
    cart.value = [];
}

function openPaymentModal() {
    if (cart.value.length === 0) {
        error('Keranjang kosong', 'Tambahkan produk ke keranjang terlebih dahulu');
        return;
    }
    showPaymentModal.value = true;
}

function closePaymentModal() {
    showPaymentModal.value = false;
    customerForm.reset();
}

function processPayment() {
    if (customerForm.metode_pembayaran === 'tunai' && kembalian.value < 0) {
        error('Jumlah bayar kurang', `Kurang Rp ${Math.abs(kembalian.value).toLocaleString('id-ID')}`);
        return;
    }

    const items = cart.value.map(item => ({
        id_produk: item.id_produk,
        jumlah: item.jumlah,
    }));

    customerForm.transform(data => ({
        ...data,
        items: items,
    })).post('/kasir/pos', {
        onSuccess: (page) => {
            // Check if there's a response in the flash data
            const flash = (page.props as any).flash;
            if (flash && flash.response) {
                const response = flash.response;
                
                if (response.snap_token) {
                    snapToken.value = response.snap_token;
                    showMidtransModal.value = true;
                    closePaymentModal();
                } else {
                    // Cash payment success
                    success('Pembayaran berhasil!', `Kembalian: Rp ${response.kembalian?.toLocaleString('id-ID') || 0}`);
                    clearCart();
                    closePaymentModal();
                }
            } else {
                // Fallback success message
                success('Pembayaran berhasil!');
                clearCart();
                closePaymentModal();
            }
        },
        onError: (errors) => {
            console.error('Payment error:', errors);
            error('Pembayaran gagal', 'Terjadi kesalahan saat memproses pembayaran');
        },
    });
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

// Watch for bayar input changes
watch(() => customerForm.bayar, (newValue) => {
    if (typeof newValue === 'string') {
        const numericValue = (newValue as string).replace(/\D/g, '');
        customerForm.bayar = parseInt(numericValue) || 0;
    }
});

// Load Midtrans script
function loadMidtransScript() {
    if (document.getElementById('midtrans-script')) return;
    
    const script = document.createElement('script');
    script.id = 'midtrans-script';
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', 'SB-Mid-client-9sRAHfQPS1pJlnqe');
    document.head.appendChild(script);
}

function openMidtransPayment() {
    if (!snapToken.value) return;
    
    loadMidtransScript();
    
    setTimeout(() => {
        // @ts-ignore
        window.snap.pay(snapToken.value, {
            onSuccess: function(result: any) {
                success('Pembayaran berhasil!', 'Transaksi telah selesai');
                clearCart();
                showMidtransModal.value = false;
                snapToken.value = '';
            },
            onPending: function(result: any) {
                success('Pembayaran pending', 'Silakan cek status pembayaran');
                showMidtransModal.value = false;
            },
            onError: function(result: any) {
                error('Pembayaran gagal', 'Terjadi kesalahan dalam pembayaran');
                showMidtransModal.value = false;
            },
            onClose: function() {
                showMidtransModal.value = false;
            }
        });
    }, 1000);
}

// Keyboard shortcuts
function handleKeyboard(event: KeyboardEvent) {
    // F1 - Focus search
    if (event.key === 'F1') {
        event.preventDefault();
        const searchInput = document.querySelector('input[type="text"]') as HTMLInputElement;
        searchInput?.focus();
    }
    
    // F2 - Open payment modal
    if (event.key === 'F2') {
        event.preventDefault();
        openPaymentModal();
    }
    
    // F3 - Clear cart
    if (event.key === 'F3') {
        event.preventDefault();
        if (cart.value.length > 0) {
            clearCart();
            success('Keranjang dikosongkan');
        }
    }
    
    // Enter - Add first product to cart when searching
    if (event.key === 'Enter' && searchQuery.value && filteredProducts.value.length > 0) {
        addToCart(filteredProducts.value[0]);
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyboard);
});
</script>

<template>
    <Head title="Point of Sale - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-120px)]">
            <!-- Product List -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Search and Filter -->
                <div class="card-emerald p-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari produk atau scan barcode... (F1)"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                        <select
                            v-model="selectedCategory"
                            class="rounded-lg border border-emerald-200 bg-white-emerald px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="category in categories" :key="category" :value="category">
                                {{ category }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="bg-white-emerald rounded-lg border border-emerald-200 p-4 overflow-y-auto" style="max-height: calc(100vh - 240px);">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="product in filteredProducts"
                            :key="product.id_produk"
                            class="border border-emerald-200 rounded-lg p-3 hover:shadow-emerald transition-all cursor-pointer"
                            @click="addToCart(product)"
                        >
                            <div class="text-center">
                                <div class="w-16 h-16 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-box text-emerald-600 text-xl"></i>
                                </div>
                                <h3 class="font-medium text-emerald-800 text-sm mb-1">{{ product.nama_produk }}</h3>
                                <p class="text-emerald-600 font-bold">{{ formatCurrency(product.harga) }}</p>
                                <p class="text-xs text-emerald-500 mt-1">Stok: {{ product.stok }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="space-y-4">
                <!-- Cart Header -->
                <div class="card-emerald p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-emerald-800">Keranjang</h2>
                        <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded-full text-sm">
                            {{ cartItemCount }} item
                        </span>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="bg-white-emerald rounded-lg border border-emerald-200 p-4 overflow-y-auto" style="max-height: 300px;">
                    <div v-if="cart.length === 0" class="text-center py-8 text-emerald-600">
                        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                        <p>Keranjang kosong</p>
                    </div>
                    
                    <div v-else class="space-y-3">
                        <div
                            v-for="(item, index) in cart"
                            :key="item.id_produk"
                            class="flex items-center justify-between border-b border-emerald-100 pb-3"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium text-emerald-800 text-sm">{{ item.nama_produk }}</h4>
                                <p class="text-emerald-600 text-sm">{{ formatCurrency(item.harga) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <BaseButton
                                    @click="updateQuantity(index, item.jumlah - 1)"
                                    variant="outline"
                                    size="xs"
                                    icon="fas fa-minus"
                                />
                                <span class="w-8 text-center text-sm">{{ item.jumlah }}</span>
                                <BaseButton
                                    @click="updateQuantity(index, item.jumlah + 1)"
                                    variant="outline"
                                    size="xs"
                                    icon="fas fa-plus"
                                />
                                <BaseButton
                                    @click="removeFromCart(index)"
                                    variant="danger"
                                    size="xs"
                                    icon="fas fa-trash"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="card-emerald p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-emerald-700">Subtotal:</span>
                        <span class="font-medium">{{ formatCurrency(cartTotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-700">Pajak (11%):</span>
                        <span class="font-medium">{{ formatCurrency(pajak) }}</span>
                    </div>
                    <div class="border-t border-emerald-200 pt-3 flex justify-between text-lg font-bold">
                        <span class="text-emerald-800">Total:</span>
                        <span class="text-emerald-800">{{ formatCurrency(grandTotal) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <BaseButton
                        @click="openPaymentModal"
                        variant="primary"
                        size="lg"
                        icon="fas fa-credit-card"
                        class="w-full"
                        :disabled="cart.length === 0"
                    >
                        Bayar (F2)
                    </BaseButton>
                    <BaseButton
                        @click="clearCart"
                        variant="secondary"
                        size="lg"
                        icon="fas fa-trash"
                        class="w-full"
                        :disabled="cart.length === 0"
                    >
                        Kosongkan Keranjang (F3)
                    </BaseButton>
                </div>

                <!-- Keyboard Shortcuts Info -->
                <div class="card-emerald p-3">
                    <h4 class="text-xs font-semibold text-emerald-800 mb-2">Shortcut Keyboard:</h4>
                    <div class="text-xs text-emerald-600 space-y-1">
                        <div>F1 - Focus Pencarian</div>
                        <div>F2 - Bayar</div>
                        <div>F3 - Kosongkan Keranjang</div>
                        <div>Enter - Tambah Produk Pertama</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white-emerald rounded-lg shadow-emerald-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-bold text-emerald-800 mb-4">Pembayaran</h3>
                
                <form @submit.prevent="processPayment" class="space-y-4">
                    <!-- Customer Info -->
                    <div>
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Nama Pelanggan</label>
                        <input
                            v-model="customerForm.nama_pelanggan"
                            type="text"
                            class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="Opsional"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Nomor HP</label>
                        <input
                            v-model="customerForm.nomor_hp_pelanggan"
                            type="tel"
                            maxlength="15"
                            pattern="[0-9]*"
                            class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="Opsional"
                        />
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-emerald-700 mb-2">Metode Pembayaran</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input
                                    v-model="customerForm.metode_pembayaran"
                                    type="radio"
                                    value="tunai"
                                    class="text-emerald-600 focus:ring-emerald-500"
                                />
                                <span class="ml-2">Tunai</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="customerForm.metode_pembayaran"
                                    type="radio"
                                    value="midtrans"
                                    class="text-emerald-600 focus:ring-emerald-500"
                                />
                                <span class="ml-2">Digital Payment (Midtrans)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Cash Payment -->
                    <div v-if="customerForm.metode_pembayaran === 'tunai'">
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Jumlah Bayar</label>
                        <input
                            v-model="customerForm.bayar"
                            type="number"
                            min="0"
                            class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="0"
                        />
                        <div v-if="kembalian >= 0" class="mt-2 text-sm">
                            <span class="text-emerald-700">Kembalian: </span>
                            <span class="font-bold text-emerald-800">{{ formatCurrency(kembalian) }}</span>
                        </div>
                        <div v-else class="mt-2 text-sm text-red-600">
                            Jumlah bayar kurang: {{ formatCurrency(Math.abs(kembalian)) }}
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="border-t border-emerald-200 pt-4">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Bayar:</span>
                            <span>{{ formatCurrency(grandTotal) }}</span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <BaseButton
                            @click="closePaymentModal"
                            variant="secondary"
                            class="flex-1"
                        >
                            Batal
                        </BaseButton>
                        <BaseButton
                            type="submit"
                            variant="primary"
                            class="flex-1"
                            :loading="customerForm.processing"
                            :disabled="customerForm.processing"
                        >
                            {{ customerForm.metode_pembayaran === 'tunai' ? 'Bayar Tunai' : 'Bayar Digital' }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Midtrans Modal -->
        <div v-if="showMidtransModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white-emerald rounded-lg shadow-emerald-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-bold text-emerald-800 mb-4">Pembayaran Digital</h3>
                <p class="text-emerald-600 mb-4">Klik tombol di bawah untuk melanjutkan pembayaran melalui Midtrans.</p>
                
                <div class="flex gap-3">
                    <BaseButton
                        @click="showMidtransModal = false"
                        variant="secondary"
                        class="flex-1"
                    >
                        Batal
                    </BaseButton>
                    <BaseButton
                        @click="openMidtransPayment"
                        variant="primary"
                        class="flex-1"
                    >
                        Bayar Sekarang
                    </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>