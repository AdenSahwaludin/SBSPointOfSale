<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Kategori {
    id_kategori: number;
    nama_kategori: string;
}

interface Produk {
    id_produk: number;
    nama: string;
    sku: string;
    stok: number;
    satuan: string;
    kategori?: Kategori;
}

interface AdjustmentType {
    value: string;
    label: string;
}

interface Props {
    produk: Produk[];
    adjustmentTypes: AdjustmentType[];
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/stock-adjustment');

const form = useForm({
    id_produk: null as number | null,
    tipe: '',
    qty_adjustment: 0,
    alasan: '',
});

const selectedProductId = ref<number | null>(null);
const searchQuery = ref('');
const showProductDropdown = ref(false);

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.produk;
    const query = searchQuery.value.toLowerCase().trim();
    
    // Filter products that match the query
    const matches = props.produk.filter(
        (p) => p.nama.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query) || p.kategori?.nama_kategori.toLowerCase().includes(query),
    );
    
    // Sort: prioritize exact matches and start-with matches
    return matches.sort((a, b) => {
        const aNameLower = a.nama.toLowerCase();
        const bNameLower = b.nama.toLowerCase();
        const aSkuLower = a.sku.toLowerCase();
        const bSkuLower = b.sku.toLowerCase();
        
        // Exact match priority
        if (aNameLower === query || aSkuLower === query) return -1;
        if (bNameLower === query || bSkuLower === query) return 1;
        
        // Start-with priority
        if (aNameLower.startsWith(query) || aSkuLower.startsWith(query)) return -1;
        if (bNameLower.startsWith(query) || bSkuLower.startsWith(query)) return 1;
        
        return 0;
    });
});

const selectedProduct = computed(() => {
    if (!selectedProductId.value) return null;
    return props.produk.find((p) => p.id_produk === selectedProductId.value);
});

const selectedType = computed(() => {
    if (!form.tipe) return null;
    return props.adjustmentTypes.find((t) => t.value === form.tipe);
});

const isPositiveAdjustment = computed(() => {
    const positiveTypes = ['retur_pelanggan', 'retur_gudang', 'koreksi_plus', 'penyesuaian_opname'];
    return positiveTypes.includes(form.tipe);
});

const isNegativeAdjustment = computed(() => {
    const negativeTypes = ['koreksi_minus', 'expired', 'rusak'];
    return negativeTypes.includes(form.tipe);
});

const isOpnameAdjustment = computed(() => {
    return form.tipe === 'penyesuaian_opname';
});

const maxNegativeQty = computed(() => {
    if (!selectedProduct.value) return 0;
    return selectedProduct.value.stok;
});

watch(selectedProductId, (newValue) => {
    form.id_produk = newValue;
});

watch(
    () => form.tipe,
    (newType) => {
        // Reset qty when type changes
        form.qty_adjustment = 0;
    },
);

function selectProduct(product: Produk) {
    selectedProductId.value = product.id_produk;
    searchQuery.value = `${product.nama} (${product.sku})`;
    showProductDropdown.value = false;
}

function handleQtyInput(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = parseInt(input.value) || 0;

    if (isPositiveAdjustment.value) {
        form.qty_adjustment = Math.abs(value);
    } else if (isNegativeAdjustment.value) {
        const absValue = Math.abs(value);
        form.qty_adjustment = -Math.min(absValue, maxNegativeQty.value);
    }
}

function submit() {
    if (!form.id_produk) {
        alert('Pilih produk terlebih dahulu');
        return;
    }

    if (!form.tipe) {
        alert('Pilih tipe adjustment terlebih dahulu');
        return;
    }

    if (form.qty_adjustment === 0) {
        alert('Qty adjustment harus lebih dari 0');
        return;
    }

    form.post('/kasir/stock-adjustment', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedProductId.value = null;
            searchQuery.value = '';
        },
    });
}
</script>

<template>
    <Head title="Buat Adjustment Stok - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Buat Adjustment Stok</h1>
                    <p class="text-emerald-600">Tambah penyesuaian stok barang</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/stock-adjustment')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="rounded-lg border border-emerald-100 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Product Select -->
                    <div>
                        <label for="produk" class="mb-2 block text-sm font-medium text-gray-700"> Produk <span class="text-red-500">*</span> </label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                @focus="showProductDropdown = true"
                                @input="showProductDropdown = true"
                                type="text"
                                placeholder="Cari produk..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            />
                            <i class="fas fa-search absolute top-3 right-3 text-gray-400"></i>

                            <!-- Dropdown -->
                            <div
                                v-if="showProductDropdown && filteredProducts.length > 0"
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                            >
                                <button
                                    v-for="product in filteredProducts"
                                    :key="product.id_produk"
                                    @click.prevent="selectProduct(product)"
                                    type="button"
                                    class="w-full border-b border-gray-100 px-4 py-3 text-left transition-colors last:border-b-0 hover:bg-emerald-50"
                                >
                                    <div class="font-medium text-gray-900">{{ product.nama }}</div>
                                    <div class="space-x-2 text-xs text-gray-500">
                                        <span>{{ product.sku }}</span>
                                        <span>•</span>
                                        <span>Stok: {{ product.stok }} {{ product.satuan }}</span>
                                        <span v-if="product.kategori">• {{ product.kategori.nama_kategori }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.id_produk" class="mt-1 text-sm text-red-600">{{ form.errors.id_produk }}</p>
                    </div>

                    <!-- Current Stock Info -->
                    <div v-if="selectedProduct" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-emerald-800">Produk Terpilih</p>
                                <p class="text-lg font-bold text-emerald-900">{{ selectedProduct.nama }}</p>
                                <p class="text-xs text-emerald-600">{{ selectedProduct.sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-emerald-800">Stok Saat Ini</p>
                                <p class="text-2xl font-bold text-emerald-900">{{ selectedProduct.stok }}</p>
                                <p class="text-xs text-emerald-600">{{ selectedProduct.satuan }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Type -->
                    <div>
                        <label for="tipe" class="mb-2 block text-sm font-medium text-gray-700">
                            Tipe Adjustment <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.tipe"
                            id="tipe"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Pilih tipe adjustment</option>
                            <option v-for="type in adjustmentTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.tipe" class="mt-1 text-sm text-red-600">{{ form.errors.tipe }}</p>
                    </div>

                    <!-- Qty Adjustment -->
                    <div>
                        <label for="qty_adjustment" class="mb-2 block text-sm font-medium text-gray-700">
                            Qty Adjustment <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                :value="Math.abs(form.qty_adjustment)"
                                @input="handleQtyInput"
                                type="number"
                                id="qty_adjustment"
                                min="0"
                                :max="isNegativeAdjustment ? maxNegativeQty : undefined"
                                placeholder="Masukkan jumlah"
                                :disabled="!form.tipe"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-50"
                            />
                            <div v-if="isPositiveAdjustment && !isOpnameAdjustment" class="absolute top-2.5 right-3">
                                <span class="font-semibold text-emerald-600">+</span>
                            </div>
                            <div v-if="isNegativeAdjustment" class="absolute top-2.5 right-3">
                                <span class="font-semibold text-red-600">-</span>
                            </div>
                            <div v-if="isOpnameAdjustment" class="absolute top-2.5 right-3 flex gap-2">
                                <span class="font-semibold text-emerald-600">+</span>
                                <span class="font-semibold text-red-600">-</span>
                            </div>
                        </div>
                        <p v-if="isNegativeAdjustment && selectedProduct" class="mt-1 text-xs text-gray-500">
                            Maksimal pengurangan: {{ maxNegativeQty }} {{ selectedProduct.satuan }}
                        </p>
                        <p v-if="isOpnameAdjustment" class="mt-1 text-xs text-gray-500">
                            Opname: Gunakan + untuk penambahan atau - untuk pengurangan
                        </p>
                        <p v-if="form.errors.qty_adjustment" class="mt-1 text-sm text-red-600">{{ form.errors.qty_adjustment }}</p>
                    </div>

                    <!-- Projected Stock -->
                    <div v-if="selectedProduct && form.qty_adjustment !== 0" class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-800">Proyeksi Stok Setelah Adjustment</p>
                                <p class="text-xs text-blue-600">Stok saat ini: {{ selectedProduct.stok }} {{ selectedProduct.satuan }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold" :class="form.qty_adjustment >= 0 ? 'text-emerald-600' : 'text-orange-600'">
                                    {{ selectedProduct.stok + form.qty_adjustment }}
                                </p>
                                <p class="text-xs text-blue-600">{{ selectedProduct.satuan }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label for="alasan" class="mb-2 block text-sm font-medium text-gray-700"> Alasan </label>
                        <textarea
                            v-model="form.alasan"
                            id="alasan"
                            rows="4"
                            placeholder="Tuliskan alasan adjustment (opsional)"
                            class="w-full resize-none rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                        <p v-if="form.errors.alasan" class="mt-1 text-sm text-red-600">{{ form.errors.alasan }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-4">
                        <BaseButton @click="$inertia.visit('/kasir/stock-adjustment')" type="button" variant="secondary" class="flex-1">
                            Batal
                        </BaseButton>
                        <BaseButton
                            type="submit"
                            variant="primary"
                            icon="fas fa-check"
                            :disabled="!form.id_produk || !form.tipe || form.qty_adjustment === 0 || form.processing"
                            :loading="form.processing"
                            class="flex-1"
                        >
                            Simpan Adjustment
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
