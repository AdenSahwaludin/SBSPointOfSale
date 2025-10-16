<script lang="ts" setup>
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

interface KonversiStok {
    id_konversi: number;
    from_produk_id: number;
    to_produk_id: number;
    rasio: number;
    qty_from: number;
    qty_to: number;
    keterangan: string | null;
    from_produk: Produk;
    to_produk: Produk;
}

interface Props {
    konversi: KonversiStok;
    produkAsal: Produk[];
    produkTujuan: Produk[];
}

const props = defineProps<Props>();

const adminMenuItems = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'fas fa-tachometer-alt',
    },
    {
        name: 'Manajemen Data',
        icon: 'fas fa-database',
        children: [
            { name: 'Pengguna', href: '/admin/pengguna', icon: 'fas fa-users' },
            { name: 'Produk', href: '/admin/produk', icon: 'fas fa-boxes' },
            { name: 'Kategori', href: '/admin/kategori', icon: 'fas fa-tags' },
            { name: 'Pelanggan', href: '/admin/pelanggan', icon: 'fas fa-user-friends' },
            { name: 'Konversi Stok', href: '/admin/konversi-stok', icon: 'fas fa-exchange-alt', active: true },
        ],
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-cash-register',
        children: [
            { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
            { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
            { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
        ],
    },
    {
        name: 'Laporan',
        href: '/admin/reports',
        icon: 'fas fa-chart-bar',
    },
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: 'fas fa-cog',
    },
];

const form = useForm({
    from_produk_id: props.konversi.from_produk_id as number | null,
    to_produk_id: props.konversi.to_produk_id as number | null,
    rasio: props.konversi.rasio,
    qty_from: props.konversi.qty_from,
    qty_to: props.konversi.qty_to,
    keterangan: props.konversi.keterangan || '',
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

function submit() {
    form.put(`/admin/konversi-stok/${props.konversi.id_konversi}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Form will redirect on success
        },
    });
}
</script>

<template>
    <BaseLayout title="Edit Konversi Stok - Sari Bumi Sakti" :menuItems="adminMenuItems" userRole="admin">
        <template #header> Edit Konversi Stok </template>

        <Head title="Edit Konversi Stok" />

        <!-- Header Section -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-emerald-800">Edit Konversi Stok</h2>
                <p class="mt-1 text-sm text-gray-600">Perbarui aturan konversi antar satuan produk</p>
            </div>
            <Link
                href="/admin/konversi-stok"
                class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </Link>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Produk Asal -->
                <div>
                    <label for="from_produk_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Produk Asal (Non-PCS) <span class="text-red-500">*</span>
                    </label>

                    <!-- Selected Product Display -->
                    <div
                        v-if="selectedFromProduk"
                        class="mb-2 flex items-center justify-between rounded-lg border-2 border-emerald-500 bg-emerald-50 p-3"
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
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 pr-10 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
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
                                    class="flex w-full items-center border-b border-gray-100 p-3 text-left transition-colors hover:bg-emerald-50"
                                >
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-box text-xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-semibold text-gray-900">{{ item.nama }}</p>
                                        <p class="text-sm text-gray-600">
                                            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-800">{{ item.sku }}</span>
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
                    <label for="to_produk_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Produk Tujuan (PCS Only) <span class="text-red-500">*</span>
                    </label>

                    <!-- Selected Product Display -->
                    <div v-if="selectedToProduk" class="mb-2 flex items-center justify-between rounded-lg border-2 border-blue-500 bg-blue-50 p-3">
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
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 pr-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
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
                                    class="flex w-full items-center border-b border-gray-100 p-3 text-left transition-colors hover:bg-blue-50"
                                >
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <i class="fas fa-cube text-xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-semibold text-gray-900">{{ item.nama }}</p>
                                        <p class="text-sm text-gray-600">
                                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">{{ item.sku }}</span>
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
                        <label for="qty_from" class="mb-2 block text-sm font-medium text-gray-700">
                            Jumlah Asal <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_from"
                            v-model.number="form.qty_from"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
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
                        <label for="qty_to" class="mb-2 block text-sm font-medium text-gray-700">
                            Jumlah Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_to"
                            v-model.number="form.qty_to"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            :class="{ 'border-red-500': form.errors.qty_to }"
                            required
                        />
                        <p v-if="selectedToProduk" class="mt-1 text-xs text-gray-500">{{ selectedToProduk.satuan }}</p>
                        <p v-if="form.errors.qty_to" class="mt-1 text-sm text-red-500">{{ form.errors.qty_to }}</p>
                    </div>
                </div>

                <!-- Rasio (Auto-calculated, read-only) -->
                <div>
                    <label for="rasio" class="mb-2 block text-sm font-medium text-gray-700"> Rasio Konversi </label>
                    <input
                        id="rasio"
                        v-model.number="form.rasio"
                        type="number"
                        readonly
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2"
                    />
                    <p class="mt-1 text-xs text-gray-500">Otomatis terhitung dari jumlah asal dan tujuan</p>
                    <p v-if="form.errors.rasio" class="mt-1 text-sm text-red-500">{{ form.errors.rasio }}</p>
                </div>

                <!-- Preview -->
                <div v-if="selectedFromProduk && selectedToProduk && form.qty_from && form.qty_to" class="rounded-lg bg-emerald-50 p-4">
                    <h4 class="mb-2 font-semibold text-emerald-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Preview Konversi
                    </h4>
                    <p class="text-emerald-700">
                        <strong>{{ form.qty_from }} {{ selectedFromProduk.satuan }}</strong> dari
                        <strong>{{ selectedFromProduk.nama }}</strong>
                        =
                        <strong>{{ form.qty_to }} {{ selectedToProduk.satuan }}</strong> dari
                        <strong>{{ selectedToProduk.nama }}</strong>
                    </p>
                    <p class="mt-1 text-sm text-emerald-600">Rasio: 1 : {{ form.rasio }}</p>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700"> Keterangan (Opsional) </label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="3"
                        maxlength="200"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        placeholder="Contoh: Konversi dari karton ke pcs"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">Maksimal 200 karakter</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <Link
                        href="/admin/konversi-stok"
                        class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 transition-colors hover:bg-gray-100"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-emerald-600 px-6 py-2 text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ form.processing ? 'Menyimpan...' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </BaseLayout>
</template>
