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
const perPage = ref(props.filters?.per_page || 10);

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
                if (page.props.flash?.warning) {
                    warning('Tidak Dapat Menghapus', page.props.flash.warning);
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
    if (stok > 10) return 'bg-green-100 text-green-700 border-green-200';
    if (stok > 0) return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    return 'bg-red-100 text-red-700 border-red-200';
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

            <!-- Table -->
            <div class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="item in displayedProduk"
                                :key="item.id_produk"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-box text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ item.nama }}</div>
                                            <div class="text-sm text-emerald-500">ID: {{ item.id_produk }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="item.kategori"
                                        class="inline-flex rounded-full border border-blue-200 bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700"
                                    >
                                        {{ item.kategori.nama }}
                                    </span>
                                    <span v-else class="text-emerald-500">-</span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-800">
                                    {{ formatPrice(item.harga) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStockBadgeClass(item.stok)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ item.stok }} {{ item.satuan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-600">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/produk/${item.id_produk}`)"
                                            variant="outline"
                                            size="xs"
                                            icon="fas fa-eye"
                                            custom-class="rounded-lg p-2"
                                            title="Lihat Detail"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/produk/${item.id_produk}/edit`)"
                                            variant="secondary"
                                            size="xs"
                                            icon="fas fa-edit"
                                            custom-class="rounded-lg p-2"
                                            title="Edit"
                                        />
                                        <BaseButton
                                            @click="confirmDelete(item)"
                                            variant="danger"
                                            size="xs"
                                            icon="fas fa-trash"
                                            custom-class="rounded-lg p-2"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="displayedProduk.length === 0 && !searchQuery" class="py-12 text-center">
                    <i class="fas fa-box mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada produk</h3>
                    <p class="mb-4 text-emerald-600">Mulai dengan menambahkan produk pertama</p>
                    <BaseButton @click="$inertia.visit('/admin/produk/create')" variant="primary" icon="fas fa-plus"> Tambah Produk </BaseButton>
                </div>

                <!-- Search Empty State -->
                <div v-if="displayedProduk.length === 0 && searchQuery && !isSearching" class="py-12 text-center">
                    <i class="fas fa-search mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Produk tidak ditemukan</h3>
                    <p class="mb-4 text-emerald-600">Coba gunakan kata kunci yang berbeda</p>
                    <BaseButton @click="clearSearch" variant="secondary" icon="fas fa-times"> Clear Search </BaseButton>
                </div>

                <!-- Pagination Section -->
                <div v-if="!searchQuery && produk.total > 0" class="border-t border-emerald-100 bg-emerald-50/50 px-6 py-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <!-- Per Page Selector -->
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-emerald-700">
                                <i class="fas fa-list mr-1"></i>
                                Items per page:
                            </label>
                            <select
                                :value="perPage"
                                @change="changePerPage(parseInt(($event.target as HTMLSelectElement).value))"
                                class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                            <span class="text-sm text-emerald-600">
                                Showing <strong>{{ produk.from }}</strong> to <strong>{{ produk.to }}</strong> of
                                <strong>{{ produk.total }}</strong> results
                            </span>
                        </div>

                        <!-- Pagination Links -->
                        <div class="flex items-center gap-1">
                            <!-- Previous Button -->
                            <button
                                @click="goToPage(produk.prev_page_url)"
                                :disabled="!produk.prev_page_url"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                :class="
                                    produk.prev_page_url
                                        ? 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'
                                        : 'border-emerald-100 bg-emerald-50 text-emerald-300'
                                "
                            >
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>

                            <!-- Page Numbers -->
                            <template v-for="(link, index) in produk.links" :key="index">
                                <button
                                    v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                                    @click="goToPage(link.url)"
                                    :disabled="!link.url"
                                    class="flex h-9 min-w-[36px] items-center justify-center rounded-lg border px-2 text-sm transition-colors"
                                    :class="
                                        link.active
                                            ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                                            : link.url
                                              ? 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'
                                              : 'border-emerald-100 bg-emerald-50 text-emerald-300'
                                    "
                                    v-html="link.label"
                                ></button>
                            </template>

                            <!-- Next Button -->
                            <button
                                @click="goToPage(produk.next_page_url)"
                                :disabled="!produk.next_page_url"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                :class="
                                    produk.next_page_url
                                        ? 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'
                                        : 'border-emerald-100 bg-emerald-50 text-emerald-300'
                                "
                            >
                                <i class="fas fa-chevron-right text-xs"></i>
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
