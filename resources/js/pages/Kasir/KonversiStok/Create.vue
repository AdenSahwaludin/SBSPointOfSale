<script lang="ts" setup>
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Produk {
    id_produk: number;
    nama: string;
    sku: string;
    satuan: string;
    isi_per_pack: number;
    stok: number;
    kategori: string;
}

interface Props {
    produkAsal: Produk[];
    produkTujuan: Produk[];
}

const props = defineProps<Props>();
const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/konversi-stok');

const form = useForm({
    from_produk_id: null as number | null,
    to_produk_id: null as number | null,
    rasio: 1,
    qty_from: 1,
    qty_to: 1,
    mode: 'penuh' as 'penuh' | 'parsial',
    keterangan: '',
});

const searchFromProduk = ref('');
const searchToProduk = ref('');
const showFromDropdown = ref(false);
const showToDropdown = ref(false);

const filteredProdukAsal = computed(() => {
    if (!searchFromProduk.value) return props.produkAsal;
    const search = searchFromProduk.value.toLowerCase();
    return props.produkAsal.filter(
        (p) => p.nama.toLowerCase().includes(search) || p.sku.toLowerCase().includes(search) || p.kategori?.toLowerCase().includes(search),
    );
});

const filteredProdukTujuan = computed(() => {
    if (!searchToProduk.value) return props.produkTujuan;
    const search = searchToProduk.value.toLowerCase();
    return props.produkTujuan.filter(
        (p) => p.nama.toLowerCase().includes(search) || p.sku.toLowerCase().includes(search) || p.kategori?.toLowerCase().includes(search),
    );
});

const selectedFromProduk = computed(() => {
    return props.produkAsal.find((p) => p.id_produk === form.from_produk_id);
});

const selectedToProduk = computed(() => {
    return props.produkTujuan.find((p) => p.id_produk === form.to_produk_id);
});

function selectFromProduk(produk: Produk) {
    form.from_produk_id = produk.id_produk;
    showFromDropdown.value = false;
    searchFromProduk.value = '';
}

function selectToProduk(produk: Produk) {
    form.to_produk_id = produk.id_produk;
    showToDropdown.value = false;
    searchToProduk.value = '';
}

function clearFromProduk() {
    form.from_produk_id = null;
    searchFromProduk.value = '';
}

function clearToProduk() {
    form.to_produk_id = null;
    searchToProduk.value = '';
}

// Auto-calculate rasio when qty_from or qty_to changes
watch([() => form.qty_from, () => form.qty_to], () => {
    if (form.qty_from > 0 && form.qty_to > 0) {
        form.rasio = Math.round(form.qty_to / form.qty_from);
    }
});

// Auto-suggest based on isi_per_pack
watch(
    () => form.from_produk_id,
    () => {
        if (selectedFromProduk.value && selectedFromProduk.value.isi_per_pack) {
            form.qty_from = 1;
            form.qty_to = selectedFromProduk.value.isi_per_pack;
            form.rasio = selectedFromProduk.value.isi_per_pack;
        }
    },
);

function submit() {
    form.post('/kasir/konversi-stok', {
        preserveScroll: true,
        onSuccess: () => {
            // Form will redirect on success
        },
    });
}
</script>

<template>
    <BaseLayout title="Tambah Konversi Stok - Kasir" :menuItems="kasirMenuItems" userRole="kasir">
        <Head title="Tambah Konversi Stok" />

        <!-- Header Section -->
        <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Konversi Stok</h1>
                <p class="mt-1 text-gray-600">Buat aturan konversi baru antar satuan produk (karton → pcs)</p>
            </div>
            <Link
                href="/kasir/konversi-stok"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 shadow-sm transition hover:bg-gray-100"
            >
                <i class="fas fa-arrow-left"></i>
                Kembali
            </Link>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Produk Asal -->
                <div>
                    <label for="from_produk_id" class="mb-2 block text-sm font-semibold text-gray-700">
                        Produk Asal (Non-PCS) <span class="text-red-500">*</span>
                    </label>

                    <!-- Selected Product Display -->
                    <div
                        v-if="selectedFromProduk"
                        class="mb-2 flex items-center justify-between rounded-lg border-2 border-emerald-500 bg-gradient-to-r from-emerald-50 to-teal-50 p-3"
                    >
                        <div class="flex-1">
                            <p class="font-semibold text-emerald-900">{{ selectedFromProduk.nama }}</p>
                            <p class="text-sm text-emerald-700">
                                SKU: {{ selectedFromProduk.sku }} | {{ selectedFromProduk.satuan }} | Stok: {{ selectedFromProduk.stok }}
                                <span v-if="selectedFromProduk.isi_per_pack" class="ml-2">| Per pack: {{ selectedFromProduk.isi_per_pack }}</span>
                            </p>
                        </div>
                        <button @click="clearFromProduk" type="button" class="ml-3 text-emerald-600 hover:text-emerald-800">
                            <i class="fas fa-times-circle text-xl"></i>
                        </button>
                    </div>

                    <!-- Search & Dropdown -->
                    <div v-else class="relative">
                        <div class="relative">
                            <input
                                v-model="searchFromProduk"
                                @focus="showFromDropdown = true"
                                @input="showFromDropdown = true"
                                type="text"
                                placeholder="Ketik untuk mencari produk asal (nama, SKU, kategori)..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-10 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                                :class="{ 'border-red-500': form.errors.from_produk_id }"
                            />
                            <i class="fas fa-search absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <!-- Dropdown List -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-if="showFromDropdown"
                                class="absolute z-50 mt-2 max-h-80 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                            >
                                <div v-if="filteredProdukAsal.length === 0" class="p-4 text-center text-gray-500">
                                    <i class="fas fa-inbox mb-2 text-3xl text-gray-300"></i>
                                    <p>Tidak ada produk ditemukan</p>
                                </div>
                                <button
                                    v-for="item in filteredProdukAsal"
                                    :key="item.id_produk"
                                    @click="selectFromProduk(item)"
                                    type="button"
                                    class="flex w-full items-center border-b border-gray-100 p-3 text-left transition hover:bg-emerald-50"
                                >
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-box text-xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-semibold text-gray-900">{{ item.nama }}</p>
                                        <p class="text-sm text-gray-600">
                                            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">{{ item.sku }}</span>
                                            <span class="ml-2 text-gray-500">{{ item.satuan }}</span>
                                            <span class="ml-2 text-emerald-600">Stok: {{ item.stok }}</span>
                                            <span v-if="item.isi_per_pack" class="ml-2 text-purple-600">Per pack: {{ item.isi_per_pack }}</span>
                                        </p>
                                    </div>
                                    <i class="fas fa-chevron-right ml-2 text-gray-400"></i>
                                </button>
                            </div>
                        </Transition>
                    </div>
                    <p v-if="form.errors.from_produk_id" class="mt-1 text-sm text-red-500">{{ form.errors.from_produk_id }}</p>
                </div>

                <!-- Produk Tujuan -->
                <div>
                    <label for="to_produk_id" class="mb-2 block text-sm font-semibold text-gray-700">
                        Produk Tujuan (PCS Only) <span class="text-red-500">*</span>
                    </label>

                    <!-- Selected Product Display -->
                    <div
                        v-if="selectedToProduk"
                        class="mb-2 flex items-center justify-between rounded-lg border-2 border-blue-500 bg-gradient-to-r from-blue-50 to-indigo-50 p-3"
                    >
                        <div class="flex-1">
                            <p class="font-semibold text-blue-900">{{ selectedToProduk.nama }}</p>
                            <p class="text-sm text-blue-700">
                                SKU: {{ selectedToProduk.sku }} | {{ selectedToProduk.satuan }} | Stok: {{ selectedToProduk.stok }}
                            </p>
                        </div>
                        <button @click="clearToProduk" type="button" class="ml-3 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times-circle text-xl"></i>
                        </button>
                    </div>

                    <!-- Search & Dropdown -->
                    <div v-else class="relative">
                        <div class="relative">
                            <input
                                v-model="searchToProduk"
                                @focus="showToDropdown = true"
                                @input="showToDropdown = true"
                                type="text"
                                placeholder="Ketik untuk mencari produk tujuan (nama, SKU, kategori)..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-10 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                :class="{ 'border-red-500': form.errors.to_produk_id }"
                            />
                            <i class="fas fa-search absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <!-- Dropdown List -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-if="showToDropdown"
                                class="absolute z-50 mt-2 max-h-80 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                            >
                                <div v-if="filteredProdukTujuan.length === 0" class="p-4 text-center text-gray-500">
                                    <i class="fas fa-inbox mb-2 text-3xl text-gray-300"></i>
                                    <p>Tidak ada produk ditemukan</p>
                                </div>
                                <button
                                    v-for="item in filteredProdukTujuan"
                                    :key="item.id_produk"
                                    @click="selectToProduk(item)"
                                    type="button"
                                    class="flex w-full items-center border-b border-gray-100 p-3 text-left transition hover:bg-blue-50"
                                >
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <i class="fas fa-cube text-xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-semibold text-gray-900">{{ item.nama }}</p>
                                        <p class="text-sm text-gray-600">
                                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{
                                                item.sku
                                            }}</span>
                                            <span class="ml-2 text-gray-500">{{ item.satuan }}</span>
                                            <span class="ml-2 text-blue-600">Stok: {{ item.stok }}</span>
                                        </p>
                                    </div>
                                    <i class="fas fa-chevron-right ml-2 text-gray-400"></i>
                                </button>
                            </div>
                        </Transition>
                    </div>
                    <p v-if="form.errors.to_produk_id" class="mt-1 text-sm text-red-500">{{ form.errors.to_produk_id }}</p>
                </div>

                <!-- Konversi Ratio -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Qty From -->
                    <div>
                        <label for="qty_from" class="mb-2 block text-sm font-semibold text-gray-700">
                            Jumlah Asal <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_from"
                            v-model.number="form.qty_from"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            :class="{ 'border-red-500': form.errors.qty_from }"
                            required
                        />
                        <p v-if="selectedFromProduk" class="mt-1 text-xs text-gray-500">{{ selectedFromProduk.satuan }}</p>
                        <p v-if="form.errors.qty_from" class="mt-1 text-sm text-red-500">{{ form.errors.qty_from }}</p>
                    </div>

                    <!-- Arrow -->
                    <div class="flex items-center justify-center pt-8">
                        <i class="fas fa-arrow-right text-2xl text-emerald-600"></i>
                    </div>

                    <!-- Qty To -->
                    <div>
                        <label for="qty_to" class="mb-2 block text-sm font-semibold text-gray-700">
                            Jumlah Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_to"
                            v-model.number="form.qty_to"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            :class="{ 'border-red-500': form.errors.qty_to }"
                            required
                        />
                        <p v-if="selectedToProduk" class="mt-1 text-xs text-gray-500">{{ selectedToProduk.satuan }}</p>
                        <p v-if="form.errors.qty_to" class="mt-1 text-sm text-red-500">{{ form.errors.qty_to }}</p>
                    </div>
                </div>

                <!-- Rasio (Auto-calculated, read-only) -->
                <div>
                    <label for="rasio" class="mb-2 block text-sm font-semibold text-gray-700"> Rasio Konversi </label>
                    <input
                        id="rasio"
                        v-model.number="form.rasio"
                        type="number"
                        readonly
                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-gray-600"
                    />
                    <p class="mt-1 text-xs text-gray-500">Otomatis terhitung dari jumlah asal dan tujuan</p>
                    <p v-if="form.errors.rasio" class="mt-1 text-sm text-red-500">{{ form.errors.rasio }}</p>
                </div>

                <!-- Mode Selection -->
                <div>
                    <label for="mode" class="mb-2 block text-sm font-semibold text-gray-700">
                        Mode Konversi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Mode PENUH -->
                        <label
                            class="relative flex cursor-pointer items-center rounded-lg border-2 p-4 transition-all"
                            :class="form.mode === 'penuh' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300 bg-white hover:border-gray-400'"
                        >
                            <input type="radio" v-model="form.mode" value="penuh" class="h-4 w-4 text-emerald-600" />
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900">Penuh</span>
                                <p class="text-xs text-gray-600">Konversi semua quantity asal</p>
                                <p class="text-xs font-medium text-emerald-700">Contoh: 1 karton → 144 pcs</p>
                            </span>
                        </label>

                        <!-- Mode PARSIAL -->
                        <label
                            class="relative flex cursor-pointer items-center rounded-lg border-2 p-4 transition-all"
                            :class="form.mode === 'parsial' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white hover:border-gray-400'"
                        >
                            <input type="radio" v-model="form.mode" value="parsial" class="h-4 w-4 text-blue-600" />
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900">Parsial</span>
                                <p class="text-xs text-gray-600">Konversi sebagian quantity asal</p>
                                <p class="text-xs font-medium text-blue-700">Contoh: 10 pcs dari 1 karton</p>
                            </span>
                        </label>
                    </div>
                    <p v-if="form.errors.mode" class="mt-1 text-sm text-red-500">{{ form.errors.mode }}</p>
                </div>

                <!-- Preview -->
                <div
                    v-if="selectedFromProduk && selectedToProduk && form.qty_from && form.qty_to"
                    class="rounded-lg border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 p-4"
                >
                    <h4 class="mb-2 font-semibold text-emerald-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Preview Konversi
                    </h4>
                    <p class="text-emerald-700">
                        <strong>{{ form.qty_from }} {{ selectedFromProduk.satuan }}</strong> dari
                        <strong>{{ selectedFromProduk.nama }}</strong>
                        <i class="fas fa-arrow-right mx-2 text-emerald-600"></i>
                        <strong>{{ form.qty_to }} {{ selectedToProduk.satuan }}</strong> dari
                        <strong>{{ selectedToProduk.nama }}</strong>
                    </p>
                    <p class="mt-1 text-sm text-emerald-600">Rasio: 1 : {{ form.rasio }}</p>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-semibold text-gray-700"> Keterangan (Opsional) </label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="3"
                        maxlength="200"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        placeholder="Contoh: Sisa 10pcs"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">Maksimal 200 karakter</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <Link
                        href="/kasir/konversi-stok"
                        class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 font-medium text-gray-700 transition hover:bg-gray-100"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 font-semibold text-white shadow-lg transition hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </BaseLayout>
</template>
