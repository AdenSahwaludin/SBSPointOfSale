<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { useDebouncedSearch } from '@/composables/useDebouncedSearch';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Kategori {
    id_kategori: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    sku?: string;
    barcode?: string;
    nama: string;
    harga: number;
    harga_pack?: number;
    stok: number;
    satuan: string;
    isi_per_pack: number;
    kategori: Kategori;
    deskripsi?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Stats {
    total_produk: number;
    stok_tersedia: number;
    stok_rendah: number;
    stok_habis: number;
}

interface Filters {
    search?: string;
    kategori?: string | number;
    stok_status?: string;
}

const props = defineProps<{
    produk: PaginatedData<Produk>;
    kategori: Kategori[];
    stats: Stats;
    filters: Filters;
}>();

// State
const perPage = ref(props.produk.per_page);
const searchQuery = ref(props.filters.search || '');
const selectedKategori = ref(props.filters.kategori || 'all');
const selectedStokStatus = ref(props.filters.stok_status || 'all');
const showFilters = ref(false);
const selectedProduk = ref<Produk | null>(null);
const showDetailModal = ref(false);

// Computed
const displayedProduk = computed(() => props.produk.data);

// Methods
function handleSearch() {
    performSearch();
}

function performSearch() {
    router.get(
        '/kasir/products',
        {
            search: searchQuery.value,
            kategori: selectedKategori.value !== 'all' ? selectedKategori.value : undefined,
            stok_status: selectedStokStatus.value !== 'all' ? selectedStokStatus.value : undefined,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

// Debounced search action for better UX
const { onInput: onProdukSearchInput } = useDebouncedSearch({
    wait: 300,
    minLength: 1,
    search: async () => {
        router.get(
            '/kasir/products',
            {
                search: searchQuery.value,
                kategori: selectedKategori.value !== 'all' ? selectedKategori.value : undefined,
                stok_status: selectedStokStatus.value !== 'all' ? selectedStokStatus.value : undefined,
                per_page: perPage.value,
            },
            { preserveState: true, preserveScroll: true },
        );
    },
});

function changePerPage() {
    router.get(
        '/kasir/products',
        {
            search: searchQuery.value,
            kategori: selectedKategori.value !== 'all' ? selectedKategori.value : undefined,
            stok_status: selectedStokStatus.value !== 'all' ? selectedStokStatus.value : undefined,
            per_page: perPage.value,
            page: 1,
        },
        {
            preserveState: true,
        },
    );
}

function goToPage(url: string | null) {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

function clearSearch() {
    searchQuery.value = '';
    performSearch();
}

function clearFilters() {
    selectedKategori.value = 'all';
    selectedStokStatus.value = 'all';
    performSearch();
}

function clearAll() {
    searchQuery.value = '';
    selectedKategori.value = 'all';
    selectedStokStatus.value = 'all';
    performSearch();
}

function viewDetail(produk: Produk) {
    selectedProduk.value = produk;
    showDetailModal.value = true;
}

function closeDetailModal() {
    showDetailModal.value = false;
    selectedProduk.value = null;
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Safely compute per-satuan price
function getPackPrice(p: Produk): number {
    const hargaPack = Number(p.harga_pack || 0);
    if (hargaPack > 0) return hargaPack;
    const unit = String(p.satuan || '').toLowerCase();
    // Jika satuan besar (karton/pack) dan harga diisi di `harga`, anggap itu harga per pack
    if ((unit === 'karton' || unit === 'pack') && p.harga) return Number(p.harga);
    return 0;
}

function getPerUnitPrice(p: Produk): number {
    const unit = String(p.satuan || '').toLowerCase();
    const isi = Number(p.isi_per_pack || 0);
    const harga = Number(p.harga || 0);
    // Jika satuan besar, turunkan harga per-satuan dari harga pack
    if ((unit === 'karton' || unit === 'pack') && isi > 0) {
        const packPrice = getPackPrice(p);
        return packPrice > 0 ? Math.round(packPrice / isi) : 0;
    }
    // Selain itu, anggap `harga` adalah per-satuan
    if (harga > 0) return harga;
    // Fallback: jika ada harga pack + isi, turunkan
    if (isi > 0) {
        const packPrice = getPackPrice(p);
        if (packPrice > 0) return Math.round(packPrice / isi);
    }
    return 0;
}

function hasPack(p: Produk): boolean {
    const unit = String(p.satuan || '').toLowerCase();
    const isi = Number(p.isi_per_pack || 0);
    const packPrice = getPackPrice(p);
    return Boolean(isi > 1 && packPrice > 0 && (unit === 'karton' || unit === 'pack' || p.harga_pack));
}

function getStokBadgeClass(stok: number): string {
    if (stok > 10) return 'bg-green-100 text-green-800';
    if (stok > 0) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
}

function getStokStatus(stok: number): string {
    if (stok > 10) return 'Tersedia';
    if (stok > 0) return 'Stok Rendah';
    return 'Habis';
}

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/products');
</script>

<template>
    <Head title="Daftar Produk - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Daftar Produk</h1>
                    <p class="mt-1 text-sm text-gray-600">Lihat informasi produk dan ketersediaan stok</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Produk - Clickable -->
                <button
                    @click="
                        selectedKategori = 'all';
                        selectedStokStatus = 'all';
                        searchQuery = '';
                        performSearch();
                    "
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 px-6 py-5 text-left transition-all focus:outline-none"
                >
                    <div
                        class="absolute -top-8 -right-8 h-24 w-24 rounded-full bg-gradient-to-br from-blue-400/20 to-transparent transition-transform group-hover:scale-150"
                    ></div>
                    <div class="absolute inset-0 border border-blue-200/50"></div>
                    <p class="relative text-sm font-medium text-blue-700">Total Produk</p>
                    <p class="relative mt-2 text-3xl font-bold text-blue-900">{{ stats.total_produk }}</p>
                </button>

                <!-- Stok Tersedia -->
                <button
                    @click="
                        selectedStokStatus = 'tersedia';
                        performSearch();
                    "
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 px-6 py-5 text-left transition-all focus:outline-none"
                >
                    <div
                        class="absolute -top-8 -right-8 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-400/20 to-transparent transition-transform group-hover:scale-150"
                    ></div>
                    <div class="absolute inset-0 border border-emerald-200/50"></div>
                    <p class="relative text-sm font-medium text-emerald-700">Stok Tersedia</p>
                    <p class="relative mt-2 text-3xl font-bold text-emerald-900">{{ stats.stok_tersedia }}</p>
                </button>

                <!-- Stok Rendah -->
                <button
                    @click="
                        selectedStokStatus = 'rendah';
                        performSearch();
                    "
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 px-6 py-5 text-left transition-all focus:outline-none"
                >
                    <div
                        class="absolute -top-8 -right-8 h-24 w-24 rounded-full bg-gradient-to-br from-amber-400/20 to-transparent transition-transform group-hover:scale-150"
                    ></div>
                    <div class="absolute inset-0 border border-amber-200/50"></div>
                    <p class="relative text-sm font-medium text-amber-700">Stok Rendah</p>
                    <p class="relative mt-2 text-3xl font-bold text-amber-900">{{ stats.stok_rendah }}</p>
                </button>

                <!-- Stok Habis -->
                <button
                    @click="
                        selectedStokStatus = 'habis';
                        performSearch();
                    "
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-50 to-red-50 px-6 py-5 text-left transition-all focus:outline-none"
                >
                    <div
                        class="absolute -top-8 -right-8 h-24 w-24 rounded-full bg-gradient-to-br from-rose-400/20 to-transparent transition-transform group-hover:scale-150"
                    ></div>
                    <div class="absolute inset-0 border border-rose-200/50"></div>
                    <p class="relative text-sm font-medium text-rose-700">Stok Habis</p>
                    <p class="relative mt-2 text-3xl font-bold text-rose-900">{{ stats.stok_habis }}</p>
                </button>
            </div>

            <!-- Search and Filters -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Search Bar -->
                        <div class="flex gap-4">
                            <div class="relative flex-1">
                                <input
                                    v-model="searchQuery"
                                    @input="onProdukSearchInput()"
                                    @keyup.enter="handleSearch"
                                    type="text"
                                    placeholder="Cari nama, SKU, atau barcode produk..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                />
                                <button
                                    v-if="searchQuery"
                                    @click="clearSearch"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                                <div v-else class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                            <BaseButton @click="handleSearch" variant="primary">
                                <i class="fas fa-search mr-2"></i>
                                Cari
                            </BaseButton>
                            <BaseButton @click="showFilters = !showFilters" variant="outline">
                                <i class="fas fa-filter mr-2"></i>
                                Filter
                                <i :class="['fas ml-2', showFilters ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                            </BaseButton>
                        </div>

                        <!-- Advanced Filters -->
                        <div v-if="showFilters" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Kategori</label>
                                    <select
                                        v-model="selectedKategori"
                                        @change="performSearch"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="all">Semua Kategori</option>
                                        <option v-for="kat in kategori" :key="kat.id_kategori" :value="kat.id_kategori">
                                            {{ kat.nama }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Status Stok</label>
                                    <select
                                        v-model="selectedStokStatus"
                                        @change="performSearch"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="all">Semua Status</option>
                                        <option value="tersedia">Stok Tersedia (&gt; 10)</option>
                                        <option value="rendah">Stok Rendah (1-10)</option>
                                        <option value="habis">Stok Habis (0)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <BaseButton @click="clearFilters" variant="outline" size="sm">
                                    <i class="fas fa-undo mr-2"></i>
                                    Reset Filter
                                </BaseButton>
                                <BaseButton @click="clearAll" variant="outline" size="sm">
                                    <i class="fas fa-times mr-2"></i>
                                    Hapus Semua
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6">
                    <div v-if="displayedProduk.length === 0" class="flex flex-col items-center justify-center py-12">
                        <i class="fas fa-box-open mb-4 text-5xl text-gray-300"></i>
                        <p class="text-lg font-medium text-gray-900">Tidak ada produk ditemukan</p>
                        <p class="mt-1 text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="produk in displayedProduk"
                            :key="produk.id_produk"
                            @click="viewDetail(produk)"
                            class="group relative cursor-pointer overflow-hidden rounded-2xl border border-gray-200 bg-white transition-all hover:border-emerald-400 hover:shadow-lg"
                        >
                            <!-- Gradient overlay on hover -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>

                            <div class="relative p-5">
                                <!-- Product Header with SKU -->
                                <div class="mb-3 flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                            {{ produk.sku || produk.id_produk }}
                                        </p>
                                        <h3 class="mt-1 line-clamp-2 text-sm font-bold text-gray-900">{{ produk.nama }}</h3>
                                    </div>
                                </div>

                                <!-- Category & Barcode -->
                                <div class="mb-4 space-y-2 border-b border-gray-100 pb-3">
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-tag mr-2 w-4 text-emerald-500"></i>
                                        <span class="font-medium">{{ produk.kategori.nama }}</span>
                                    </div>
                                    <div v-if="produk.barcode" class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-barcode mr-2 w-4 text-blue-500"></i>
                                        <span class="font-mono text-xs">{{ produk.barcode }}</span>
                                    </div>
                                </div>

                                <!-- Price Section -->
                                <div class="mb-4 rounded-lg bg-gradient-to-br from-emerald-50 to-green-50 p-3">
                                    <div v-if="hasPack(produk)">
                                        <p class="text-xl font-bold text-emerald-700">{{ formatCurrency(getPackPrice(produk)) }}</p>
                                        <p class="text-xs text-emerald-600">per pack ({{ produk.isi_per_pack }} satuan)</p>
                                        <p class="mt-2 text-xs text-blue-700">
                                            <span class="font-semibold">{{ formatCurrency(getPerUnitPrice(produk)) }}</span>
                                            <span class="text-blue-600"> per satuan</span>
                                        </p>
                                    </div>
                                    <div v-else>
                                        <p class="text-xl font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(produk)) }}</p>
                                        <p class="text-xs text-emerald-600">per satuan</p>
                                    </div>
                                </div>

                                <!-- Stock Status -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500">Stok</p>
                                        <p class="text-base font-bold text-gray-900">{{ produk.stok }}</p>
                                    </div>
                                    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-bold', getStokBadgeClass(produk.stok)]">
                                        {{ getStokStatus(produk.stok) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="produk.last_page > 1" class="border-t border-gray-200 bg-gradient-to-r from-white to-gray-50 px-6 py-5">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <!-- Items per page selector -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">Tampilkan:</span>
                            <select
                                v-model="perPage"
                                @change="changePerPage"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-all hover:border-emerald-400 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            >
                                <option :value="12">12 per halaman</option>
                                <option :value="20">20 per halaman</option>
                                <option :value="40">40 per halaman</option>
                                <option :value="60">60 per halaman</option>
                                <option :value="100">100 per halaman</option>
                            </select>
                            <span class="text-sm text-gray-600">
                                <span class="font-semibold">{{ produk.from }}</span> - <span class="font-semibold">{{ produk.to }}</span> dari
                                <span class="font-semibold text-emerald-600">{{ produk.total }}</span>
                            </span>
                        </div>

                        <!-- Page info and pagination buttons -->
                        <div class="flex items-center gap-2">
                            <!-- Previous Button -->
                            <button
                                @click="goToPage(produk.prev_page_url)"
                                :disabled="!produk.prev_page_url"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                    produk.prev_page_url
                                        ? 'border border-gray-300 bg-white text-gray-700 hover:border-emerald-400 hover:text-emerald-600'
                                        : 'cursor-not-allowed border border-gray-200 bg-gray-50 text-gray-400',
                                ]"
                            >
                                <i class="fas fa-chevron-left"></i>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </button>

                            <!-- Page Numbers -->
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="(link, index) in produk.links.slice(1, -1)"
                                    :key="index"
                                    @click="goToPage(link.url)"
                                    :class="[
                                        'rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                        link.active
                                            ? 'bg-gradient-to-br from-emerald-600 to-emerald-700 text-white shadow-md'
                                            : 'border border-gray-300 bg-white text-gray-700 hover:border-emerald-400 hover:text-emerald-600',
                                    ]"
                                    v-html="link.label"
                                ></button>
                            </div>

                            <!-- Next Button -->
                            <button
                                @click="goToPage(produk.next_page_url)"
                                :disabled="!produk.next_page_url"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                    produk.next_page_url
                                        ? 'border border-gray-300 bg-white text-gray-700 hover:border-emerald-400 hover:text-emerald-600'
                                        : 'cursor-not-allowed border border-gray-200 bg-gray-50 text-gray-400',
                                ]"
                            >
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div
                v-if="showDetailModal && selectedProduk"
                class="bg-opacity-50 modal-bg fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="closeDetailModal"
            >
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-gray-200 p-5">
                        <h3 class="text-lg font-bold text-gray-900">Detail Produk</h3>
                        <button @click="closeDetailModal" class="text-gray-400 transition-colors hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5">
                        <div class="space-y-4">
                            <!-- SKU & Product Name -->
                            <div class="rounded-lg border border-blue-100 bg-gradient-to-br from-blue-50 to-cyan-50 p-4">
                                <p class="text-xs font-semibold tracking-wide text-blue-600 uppercase">
                                    {{ selectedProduk.sku || selectedProduk.id_produk }}
                                </p>
                                <h4 class="mt-2 text-base font-bold text-gray-900">{{ selectedProduk.nama }}</h4>
                            </div>

                            <!-- Basic Info Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs font-medium text-gray-600">Kategori</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ selectedProduk.kategori.nama }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs font-medium text-gray-600">Satuan</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ selectedProduk.satuan }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs font-medium text-gray-600">Barcode</p>
                                    <p class="mt-1 font-mono text-sm text-gray-900">{{ selectedProduk.barcode || '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs font-medium text-gray-600">Isi Per Pack</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ selectedProduk.isi_per_pack }}</p>
                                </div>
                            </div>

                            <!-- Pricing Section -->
                            <div class="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
                                <p class="text-xs font-semibold text-emerald-700 uppercase">Harga</p>
                                <div class="mt-3 space-y-2">
                                    <div v-if="hasPack(selectedProduk)" class="flex items-center justify-between">
                                        <span class="text-sm text-emerald-700">Per Pack ({{ selectedProduk.isi_per_pack }})</span>
                                        <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPackPrice(selectedProduk)) }}</span>
                                    </div>
                                    <div v-if="hasPack(selectedProduk)" class="flex items-center justify-between border-t border-emerald-200 pt-2">
                                        <span class="text-sm text-emerald-700">Per Satuan</span>
                                        <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(selectedProduk)) }}</span>
                                    </div>
                                    <div v-else class="flex items-center justify-between">
                                        <span class="text-sm text-emerald-700">Per Satuan</span>
                                        <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(selectedProduk)) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Section -->
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold text-gray-700 uppercase">Ketersediaan</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900">{{ selectedProduk.stok }}</p>
                                        <p class="mt-1 text-xs text-gray-600">{{ selectedProduk.satuan }} tersedia</p>
                                    </div>
                                    <span :class="['inline-flex rounded-full px-4 py-2 text-xs font-bold', getStokBadgeClass(selectedProduk.stok)]">
                                        {{ getStokStatus(selectedProduk.stok) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="selectedProduk.deskripsi" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold text-gray-700 uppercase">Deskripsi</p>
                                <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ selectedProduk.deskripsi }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end gap-2 border-t border-gray-200 bg-gray-50 p-5">
                        <BaseButton @click="closeDetailModal" variant="primary">
                            <i class="fas fa-check mr-2"></i>
                            Tutup
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Teleport>
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
