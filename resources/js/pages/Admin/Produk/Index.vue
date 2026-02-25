<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import { useNotifications } from '@/composables/useNotifications';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Kategori {
    id_kategori: string;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    harga: number;
    stok: number;
    satuan: string;
    kategori?: Kategori;
    created_at: string;
    sku?: string;
    barcode?: string;
    harga_pack?: number;
    isi_per_pack?: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
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

interface Props {
    produk: PaginatedData<Produk>;
    kategori: Kategori[];
    stats?: {
        total: number;
        stokHabis: number;
        stokRendah: number;
        stokTersedia: number;
    };
    filters?: {
        per_page?: number;
        page?: number;
    };
}

const props = defineProps<Props>();

const { warning } = useNotifications();
const page = usePage();

const showDeleteModal = ref(false);
const deleteTarget = ref<Produk | null>(null);
const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref<Produk[]>([]);
const searchTimeout = ref<number | null>(null);

// Filter states
const selectedKategori = ref<string>('all');
const selectedStokStatus = ref<string>('all');
const showFilters = ref(false);

// Pagination states
const perPage = ref(props.filters?.per_page || 12);

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/produk');

// Get actual produk data from paginated response
const allProdukData = computed(() => props.produk.data);

// Computed property untuk data dasar (search atau all)
const baseData = computed(() => {
    if (searchQuery.value.trim() && searchResults.value.length > 0) {
        return searchResults.value;
    }
    if (searchQuery.value.trim() && !isSearching.value) {
        return []; // Jika search tapi tidak ada hasil
    }
    return allProdukData.value; // Default tampilkan data dari pagination
});

// Computed property untuk menentukan data yang ditampilkan setelah filter
const displayedProduk = computed(() => {
    let filtered = baseData.value;

    // Filter by kategori
    if (selectedKategori.value !== 'all') {
        filtered = filtered.filter((p: Produk) => p.kategori?.id_kategori === selectedKategori.value);
    }

    // Filter by stok status
    if (selectedStokStatus.value !== 'all') {
        if (selectedStokStatus.value === 'habis') {
            filtered = filtered.filter((p: Produk) => p.stok === 0);
        } else if (selectedStokStatus.value === 'rendah') {
            filtered = filtered.filter((p: Produk) => p.stok > 0 && p.stok <= 10);
        } else if (selectedStokStatus.value === 'tersedia') {
            filtered = filtered.filter((p: Produk) => p.stok > 10);
        }
    }

    return filtered;
});

// Computed untuk stats
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedKategori.value !== 'all') count++;
    if (selectedStokStatus.value !== 'all') count++;
    return count;
});

// Use stats from backend if available, otherwise calculate from current data
const statsData = computed(() => {
    if (props.stats) {
        return {
            total: props.stats.total,
            displayed: displayedProduk.value.length,
            stokHabis: props.stats.stokHabis,
            stokRendah: props.stats.stokRendah,
            stokTersedia: props.stats.stokTersedia,
        };
    }
    // Fallback to calculating from current page data
    return {
        total: props.produk.total,
        displayed: displayedProduk.value.length,
        stokHabis: allProdukData.value.filter((p: Produk) => p.stok === 0).length,
        stokRendah: allProdukData.value.filter((p: Produk) => p.stok > 0 && p.stok <= 10).length,
        stokTersedia: allProdukData.value.filter((p: Produk) => p.stok > 10).length,
    };
});

function confirmDelete(produk: Produk) {
    deleteTarget.value = produk;
    showDeleteModal.value = true;
}

function deleteProduk() {
    if (deleteTarget.value) {
        router.delete(`/admin/produk/${deleteTarget.value.id_produk}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
            onFinish: () => {
                // Check for warning flash message and show as notification
                const flash = (page.props as any).flash;
                if (flash?.warning) {
                    warning('Tidak Dapat Menghapus', flash.warning);
                    showDeleteModal.value = false;
                }
            },
        });
    }
}

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID');
}

function getStockBadgeClass(stok: number) {
    if (stok > 10) return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (stok > 0) return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    return 'bg-red-100 text-red-700 border-red-200';
}

function getStockStatus(stok: number) {
    if (stok > 10) return 'Stok Tersedia';
    if (stok > 0) return 'Stok Rendah';
    return 'Stok Habis';
}

function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function getPackPrice(p: Produk): number {
    const hargaPack = Number(p.harga_pack || 0);
    if (hargaPack > 0) return hargaPack;
    const unit = String(p.satuan || '').toLowerCase();
    if ((unit === 'karton' || unit === 'pack') && p.harga) return Number(p.harga);
    return 0;
}

function getPerUnitPrice(p: Produk): number {
    const unit = String(p.satuan || '').toLowerCase();
    const isi = Number(p.isi_per_pack || 0);
    const harga = Number(p.harga || 0);
    if ((unit === 'karton' || unit === 'pack') && isi > 0) {
        const packPrice = getPackPrice(p);
        return packPrice > 0 ? Math.round(packPrice / isi) : 0;
    }
    if (harga > 0) return harga;
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

function handleSearch() {
    // Clear previous timeout
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    // Jika search kosong, reset
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        isSearching.value = false;
        return;
    }

    isSearching.value = true;

    // Debounce search
    searchTimeout.value = window.setTimeout(() => {
        performSearch();
    }, 300);
}

async function performSearch() {
    try {
        const response = await fetch(`/admin/produk/search-produk?q=${encodeURIComponent(searchQuery.value)}`);
        if (!response.ok) throw new Error('Search failed');

        const data = await response.json();
        searchResults.value = data;
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}

function clearSearch() {
    searchQuery.value = '';
    searchResults.value = [];
    isSearching.value = false;
}

function clearFilters() {
    selectedKategori.value = 'all';
    selectedStokStatus.value = 'all';
}

function clearAll() {
    clearSearch();
    clearFilters();
}

function changePerPage(newPerPage: number) {
    perPage.value = newPerPage;
    router.get(
        '/admin/produk',
        { per_page: newPerPage, page: 1 },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function goToPage(url: string | null) {
    if (!url) return;
    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: false,
        },
    );
}
</script>

<template>
    <Head title="Manajemen Produk - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Manajemen Produk</h1>
                    <p class="text-emerald-600">Kelola data produk</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/produk/create')" variant="primary" icon="fas fa-plus"> Tambah Produk </BaseButton>
            </div>

            <!-- Search Bar -->
            <div class="card-emerald p-4">
                <div class="relative">
                    <div class="relative flex items-center">
                        <i class="fas fa-search absolute left-4 text-emerald-400"></i>
                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Cari produk berdasarkan nama, ID, SKU, barcode, atau kategori..."
                            class="w-full rounded-lg border border-emerald-200 bg-white py-3 pr-24 pl-11 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute right-3 rounded-lg px-3 py-1.5 text-sm text-emerald-600 transition-colors hover:bg-emerald-100"
                        >
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                        <div v-if="isSearching" class="absolute right-3">
                            <i class="fas fa-spinner fa-spin text-emerald-500"></i>
                        </div>
                    </div>

                    <!-- Search Info -->
                    <div v-if="searchQuery.trim()" class="mt-2 text-sm">
                        <span v-if="isSearching" class="text-emerald-600">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            Mencari...
                        </span>
                        <span v-else-if="searchResults.length > 0" class="text-emerald-700">
                            <i class="fas fa-check-circle mr-1 text-green-600"></i>
                            Ditemukan <strong>{{ searchResults.length }}</strong> produk untuk "<strong>{{ searchQuery }}</strong
                            >"
                        </span>
                        <span v-else class="text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Tidak ada produk ditemukan untuk "<strong>{{ searchQuery }}</strong
                            >"
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter & Stats Section -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Stats Cards -->
                <div class="card-emerald p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-emerald-600">Total Produk</p>
                            <p class="text-2xl font-bold text-emerald-800">{{ statsData.total }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <i class="fas fa-boxes text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <div class="card-emerald cursor-pointer p-4 transition-all hover:shadow-md" @click="selectedStokStatus = 'tersedia'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600">Stok Tersedia</p>
                            <p class="text-2xl font-bold text-green-800">{{ statsData.stokTersedia }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="card-emerald cursor-pointer p-4 transition-all hover:shadow-md" @click="selectedStokStatus = 'rendah'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-yellow-600">Stok Rendah</p>
                            <p class="text-2xl font-bold text-yellow-800">{{ statsData.stokRendah }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div class="card-emerald cursor-pointer p-4 transition-all hover:shadow-md" @click="selectedStokStatus = 'habis'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-red-600">Stok Habis</p>
                            <p class="text-2xl font-bold text-red-800">{{ statsData.stokHabis }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="card-emerald p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            @click="showFilters = !showFilters"
                            class="flex items-center gap-2 rounded-lg border border-emerald-300 bg-white px-4 py-2 text-emerald-700 transition-colors hover:bg-emerald-50"
                        >
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                            <span
                                v-if="activeFiltersCount > 0"
                                class="ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-xs text-white"
                            >
                                {{ activeFiltersCount }}
                            </span>
                            <i class="fas fa-chevron-down ml-1 transition-transform" :class="{ 'rotate-180': showFilters }"></i>
                        </button>

                        <!-- Active Filters Badges -->
                        <div class="flex items-center gap-2">
                            <span
                                v-if="selectedKategori !== 'all'"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-700"
                            >
                                <i class="fas fa-tag text-xs"></i>
                                {{ kategori.find((k) => k.id_kategori === selectedKategori)?.nama }}
                                <button @click="selectedKategori = 'all'" class="ml-1 hover:text-blue-900">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>

                            <span
                                v-if="selectedStokStatus !== 'all'"
                                class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-sm text-purple-700"
                            >
                                <i class="fas fa-layer-group text-xs"></i>
                                {{
                                    selectedStokStatus === 'habis' ? 'Stok Habis' : selectedStokStatus === 'rendah' ? 'Stok Rendah' : 'Stok Tersedia'
                                }}
                                <button @click="selectedStokStatus = 'all'" class="ml-1 hover:text-purple-900">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <button
                        v-if="activeFiltersCount > 0 || searchQuery"
                        @click="clearAll"
                        class="rounded-lg px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
                    >
                        <i class="fas fa-times-circle mr-1"></i>
                        Clear All
                    </button>
                </div>

                <!-- Filter Dropdowns -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="mt-4 grid grid-cols-1 gap-4 border-t border-emerald-100 pt-4 md:grid-cols-2">
                        <!-- Kategori Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700">
                                <i class="fas fa-tag mr-1"></i>
                                Filter by Kategori
                            </label>
                            <select
                                v-model="selectedKategori"
                                class="w-full rounded-lg border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            >
                                <option value="all">Semua Kategori</option>
                                <option v-for="kat in kategori" :key="kat.id_kategori" :value="kat.id_kategori">
                                    {{ kat.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Stok Status Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700">
                                <i class="fas fa-layer-group mr-1"></i>
                                Filter by Stok
                            </label>
                            <select
                                v-model="selectedStokStatus"
                                class="w-full rounded-lg border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            >
                                <option value="all">Semua Status</option>
                                <option value="tersedia">Stok Tersedia (> 10)</option>
                                <option value="rendah">Stok Rendah (1-10)</option>
                                <option value="habis">Stok Habis (0)</option>
                            </select>
                        </div>
                    </div>
                </Transition>

                <!-- Results Info -->
                <div v-if="activeFiltersCount > 0 || searchQuery" class="mt-3 text-sm text-emerald-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menampilkan <strong>{{ statsData.displayed }}</strong> dari <strong>{{ statsData.total }}</strong> produk
                </div>
            </div>

            <!-- Products Grid -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6">
                    <div v-if="displayedProduk.length === 0 && !searchQuery" class="flex flex-col items-center justify-center py-12">
                        <i class="fas fa-box mb-4 text-5xl text-gray-300"></i>
                        <p class="text-lg font-medium text-gray-900">Belum ada produk</p>
                        <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan produk pertama</p>
                        <BaseButton @click="$inertia.visit('/admin/produk/create')" variant="primary" icon="fas fa-plus" class="mt-4">
                            Tambah Produk
                        </BaseButton>
                    </div>

                    <div
                        v-else-if="displayedProduk.length === 0 && searchQuery && !isSearching"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <i class="fas fa-search mb-4 text-5xl text-gray-300"></i>
                        <p class="text-lg font-medium text-gray-900">Produk tidak ditemukan</p>
                        <p class="mt-1 text-sm text-gray-500">Coba gunakan kata kunci yang berbeda</p>
                        <BaseButton @click="clearSearch" variant="secondary" icon="fas fa-times" class="mt-4"> Clear Search </BaseButton>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="item in displayedProduk"
                            :key="item.id_produk"
                            class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white transition-all hover:border-emerald-200 hover:shadow-md"
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
                                            {{ item.sku || item.id_produk }}
                                        </p>
                                        <h3 class="mt-1 line-clamp-2 text-sm font-bold text-gray-900">{{ item.nama }}</h3>
                                    </div>
                                </div>

                                <!-- Category & Barcode -->
                                <div class="mb-4 space-y-2 border-b border-gray-100 pb-3">
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-tag mr-2 w-4 text-emerald-500"></i>
                                        <span class="font-medium">{{ item.kategori?.nama || '-' }}</span>
                                    </div>
                                    <div v-if="item.barcode" class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-barcode mr-2 w-4 text-blue-500"></i>
                                        <span class="font-mono text-xs">{{ item.barcode }}</span>
                                    </div>
                                </div>

                                <!-- Price Section -->
                                <div class="mb-4 rounded-lg bg-gradient-to-br from-emerald-50 to-green-50 p-3">
                                    <div v-if="hasPack(item)">
                                        <p class="text-xl font-bold text-emerald-700">{{ formatCurrency(getPackPrice(item)) }}</p>
                                        <p class="text-xs text-emerald-600">per pack ({{ item.isi_per_pack }} satuan)</p>
                                        <p class="mt-2 text-xs text-blue-700">
                                            <span class="font-semibold">{{ formatCurrency(getPerUnitPrice(item)) }}</span>
                                            <span class="text-blue-600"> per satuan</span>
                                        </p>
                                    </div>
                                    <div v-else>
                                        <p class="text-xl font-bold text-emerald-700">{{ formatCurrency(getPerUnitPrice(item)) }}</p>
                                        <p class="text-xs text-emerald-600">per {{ item.satuan }}</p>
                                    </div>
                                </div>

                                <!-- Stock Status -->
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500">Stok</p>
                                        <p class="text-base font-bold text-gray-900">{{ item.stok }}</p>
                                    </div>
                                    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-bold', getStockBadgeClass(item.stok)]">
                                        {{ getStockStatus(item.stok) }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2 border-t border-gray-100 pt-3">
                                    <BaseButton
                                        @click="$inertia.visit(`/admin/produk/${item.id_produk}`)"
                                        variant="outline"
                                        size="sm"
                                        icon="fas fa-eye"
                                        custom-class="flex-1 rounded-lg text-xs"
                                        title="Lihat Detail"
                                    >
                                        Lihat
                                    </BaseButton>
                                    <BaseButton
                                        @click="$inertia.visit(`/admin/produk/${item.id_produk}/edit`)"
                                        variant="secondary"
                                        size="sm"
                                        icon="fas fa-edit"
                                        custom-class="flex-1 rounded-lg text-xs"
                                        title="Edit"
                                    >
                                        Edit
                                    </BaseButton>
                                    <BaseButton
                                        @click="confirmDelete(item)"
                                        variant="danger"
                                        size="sm"
                                        icon="fas fa-trash"
                                        custom-class="flex-1 rounded-lg text-xs"
                                        title="Hapus"
                                    >
                                        Hapus
                                    </BaseButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div v-if="!searchQuery && produk.last_page > 1" class="border-t border-gray-200 bg-gradient-to-r from-white to-gray-50 px-6 py-5">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <!-- Items per page selector -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">Tampilkan:</span>
                            <select
                                :value="perPage"
                                @change="changePerPage(parseInt(($event.target as HTMLSelectElement).value))"
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

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[2px]">
            <div class="shadow-emerald-xl mx-4 max-w-md rounded-lg bg-white-emerald p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-emerald-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus produk ini?</p>
                    </div>
                </div>

                <div v-if="deleteTarget" class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-emerald-800">{{ deleteTarget.nama }}</div>
                        <div class="text-emerald-600">ID: {{ deleteTarget.id_produk }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <BaseButton @click="showDeleteModal = false" variant="secondary"> Batal </BaseButton>
                    <BaseButton @click="deleteProduk" variant="danger"> Hapus </BaseButton>
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
