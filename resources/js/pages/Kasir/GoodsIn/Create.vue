<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Product {
    id_produk: number;
    sku: string;
    nama: string;
    stok: number;
    batas_stok_minimum: number;
    jumlah_restock: number;
    satuan: string;
    kategori?: {
        nama: string;
    };
}

interface Props {
    productsBelowROP: Product[];
}

interface CartItem {
    id_produk: number;
    nama: string;
    sku: string;
    qty_request: number;
    satuan: string;
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in');
const { formatCurrency } = useCurrencyFormat();

const searchQuery = ref('');
const cart = ref<CartItem[]>([]);
const selectedProductId = ref<number | null>(null);
const selectedQty = ref<number>(1);

const form = useForm({
    items: [] as Array<{ id_produk: number; jumlah_dipesan: number }>,
});

const filteredProducts = computed(() => {
    if (!searchQuery.value) {
        return props.productsBelowROP;
    }
    const query = searchQuery.value.toLowerCase();
    return props.productsBelowROP.filter(
        (p) => p.nama.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query) || p.kategori?.nama.toLowerCase().includes(query),
    );
});

const selectedProduct = computed(() => {
    if (!selectedProductId.value) return null;
    return props.productsBelowROP.find((p) => p.id_produk === selectedProductId.value);
});

const canAddToCart = computed(() => {
    return selectedProductId.value && selectedQty.value > 0;
});

const totalItems = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.qty_request, 0);
});

function addToCart() {
    if (!selectedProduct.value || selectedQty.value <= 0) return;

    const existingItem = cart.value.find((item) => item.id_produk === selectedProduct.value!.id_produk);

    if (existingItem) {
        existingItem.qty_request += selectedQty.value;
    } else {
        cart.value.push({
            id_produk: selectedProduct.value.id_produk,
            nama: selectedProduct.value.nama,
            sku: selectedProduct.value.sku,
            qty_request: selectedQty.value,
            satuan: selectedProduct.value.satuan,
        });
    }

    // Reset selection
    selectedProductId.value = null;
    selectedQty.value = 1;
}

function removeFromCart(index: number) {
    cart.value.splice(index, 1);
}

function updateCartQty(index: number, qty: number) {
    if (qty > 0) {
        cart.value[index].qty_request = qty;
    } else {
        removeFromCart(index);
    }
}

function quickAddFromROP(product: Product) {
    const suggestedQty = product.jumlah_restock || product.batas_stok_minimum - product.stok;
    const existingItem = cart.value.find((item) => item.id_produk === product.id_produk);

    if (existingItem) {
        existingItem.qty_request += suggestedQty;
    } else {
        cart.value.push({
            id_produk: product.id_produk,
            nama: product.nama,
            sku: product.sku,
            qty_request: suggestedQty,
            satuan: product.satuan,
        });
    }
}

function submit() {
    if (cart.value.length === 0) {
        alert('Tambahkan minimal satu item ke keranjang');
        return;
    }

    form.items = cart.value.map((item) => ({
        id_produk: item.id_produk,
        jumlah_dipesan: item.qty_request,
    }));

    form.post('/kasir/goods-in', {
        onSuccess: () => {
            cart.value = [];
            form.reset();
        },
    });
}

function getStockDifference(product: Product) {
    return product.batas_stok_minimum - product.stok;
}

function getSuggestedQty(product: Product) {
    return product.jumlah_restock || getStockDifference(product);
}
</script>

<template>
    <Head title="Buat Purchase Order - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Buat Purchase Order</h1>
                    <p class="text-emerald-600">Buat permintaan pembelian barang baru</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/goods-in')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Add Item Section -->
                <div class="card-emerald">
                    <h2 class="mb-4 text-lg font-semibold text-emerald-800">Tambah Item ke PO</h2>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <!-- Product Select -->
                        <div class="lg:col-span-2">
                            <label for="product" class="mb-2 block text-sm font-medium text-emerald-700">
                                Pilih Produk <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="product"
                                v-model="selectedProductId"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            >
                                <option :value="null">-- Pilih Produk --</option>
                                <option v-for="product in filteredProducts" :key="product.id_produk" :value="product.id_produk">
                                    {{ product.nama }} ({{ product.sku }}) - Stok: {{ product.stok }} {{ product.satuan }}
                                </option>
                            </select>
                            <p v-if="selectedProduct" class="mt-1 text-xs text-emerald-600">
                                <i class="fas fa-info-circle"></i>
                                Batas Minimum: {{ selectedProduct.batas_stok_minimum }}, Stok Saat Ini: {{ selectedProduct.stok }}, Kekurangan:
                                {{ getStockDifference(selectedProduct) }}
                            </p>
                        </div>

                        <!-- Quantity Input -->
                        <div>
                            <label for="qty" class="mb-2 block text-sm font-medium text-emerald-700">
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    id="qty"
                                    v-model.number="selectedQty"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                    placeholder="Qty"
                                />
                                <BaseButton @click="addToCart" type="button" variant="primary" icon="fas fa-plus" :disabled="!canAddToCart">
                                    Tambah
                                </BaseButton>
                            </div>
                            <p v-if="selectedProduct && selectedProduct.jumlah_restock" class="mt-1 text-xs text-emerald-600">
                                <i class="fas fa-lightbulb"></i>
                                Jumlah Restock (Saran): {{ selectedProduct.jumlah_restock }} {{ selectedProduct.satuan }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="card-emerald">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-emerald-800">Item PO ({{ cart.length }} produk, {{ totalItems }} total qty)</h2>
                        <div v-if="form.errors.items" class="text-sm text-red-600">
                            {{ form.errors.items }}
                        </div>
                    </div>

                    <div v-if="cart.length === 0" class="rounded-lg border-2 border-dashed border-emerald-200 p-8 text-center">
                        <i class="fas fa-shopping-cart mb-3 text-4xl text-emerald-300"></i>
                        <p class="text-emerald-600">Belum ada item. Tambahkan produk ke keranjang untuk membuat PO.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-emerald-200 bg-emerald-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Produk</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">SKU</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Jumlah</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100">
                                <tr v-for="(item, index) in cart" :key="item.id_produk" class="transition-colors hover:bg-emerald-50">
                                    <td class="px-4 py-3 text-sm text-emerald-800">{{ item.nama }}</td>
                                    <td class="px-4 py-3 text-sm text-emerald-600">{{ item.sku }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-model.number="item.qty_request"
                                                @change="updateCartQty(index, item.qty_request)"
                                                type="number"
                                                min="1"
                                                class="w-24 rounded border border-emerald-300 px-2 py-1 text-sm text-emerald-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-200 focus:outline-none"
                                            />
                                            <span class="text-sm text-emerald-600">{{ item.satuan }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button
                                            @click="removeFromCart(index)"
                                            type="button"
                                            class="text-red-600 transition-colors hover:text-red-700"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3">
                    <BaseButton @click="$inertia.visit('/kasir/goods-in')" type="button" variant="secondary"> Batal </BaseButton>
                    <BaseButton
                        type="submit"
                        variant="primary"
                        icon="fas fa-paper-plane"
                        :disabled="cart.length === 0 || form.processing"
                        :loading="form.processing"
                    >
                        Ajukan PO
                    </BaseButton>
                </div>
            </form>

            <!-- Products Below ROP Suggestions -->
            <div class="card-emerald" v-if="productsBelowROP.length > 0">
                <div class="mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    <h2 class="text-lg font-semibold text-emerald-800">Produk di Bawah ROP ({{ productsBelowROP.length }} produk)</h2>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <div class="relative">
                        <i class="fas fa-search absolute top-1/2 left-3 -translate-y-1/2 text-emerald-400"></i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari produk berdasarkan nama, SKU, atau kategori..."
                            class="w-full rounded-lg border border-emerald-300 py-2 pr-4 pl-10 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Produk</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">SKU</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kategori</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Stok</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Batas Minimum</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Saran Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100">
                            <tr v-for="product in filteredProducts" :key="product.id_produk" class="transition-colors hover:bg-emerald-50">
                                <td class="px-4 py-3 text-sm text-emerald-800">{{ product.nama }}</td>
                                <td class="px-4 py-3 text-sm text-emerald-600">{{ product.sku }}</td>
                                <td class="px-4 py-3 text-sm text-emerald-600">{{ product.kategori?.nama || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-red-600"> {{ product.stok }} {{ product.satuan }} </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-emerald-700">{{ product.batas_stok_minimum }} {{ product.satuan }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-emerald-700">{{ getSuggestedQty(product) }} {{ product.satuan }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <BaseButton @click="quickAddFromROP(product)" type="button" variant="success" size="sm" icon="fas fa-plus-circle">
                                        Quick Add
                                    </BaseButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
