<template>
    <AdminLayout>
        <Head title="Manajemen Produk" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Manajemen Produk</h2>
                            <Link :href="route('admin.produk.create')" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                                Tambah Produk
                            </Link>
                        </div>

                        <!-- Flash Message -->
                        <div v-if="$page.props.flash.message" class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
                            {{ $page.props.flash.message }}
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 rounded bg-gray-50 p-4 dark:bg-gray-700">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <!-- Search -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium">Cari Produk</label>
                                    <input
                                        v-model="filters.search"
                                        type="text"
                                        placeholder="Nama, ID, atau BPOM..."
                                        class="w-full rounded border px-3 py-2 dark:border-gray-500 dark:bg-gray-600"
                                        @input="search"
                                    />
                                </div>

                                <!-- Category Filter -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium">Kategori</label>
                                    <select
                                        v-model="filters.kategori"
                                        class="w-full rounded border px-3 py-2 dark:border-gray-500 dark:bg-gray-600"
                                        @change="search"
                                    >
                                        <option value="">Semua Kategori</option>
                                        <option v-for="cat in kategori" :key="cat.id_kategori" :value="cat.id_kategori">
                                            {{ cat.nama }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Stock Status Filter -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium">Status Stok</label>
                                    <select
                                        v-model="filters.stock_status"
                                        class="w-full rounded border px-3 py-2 dark:border-gray-500 dark:bg-gray-600"
                                        @change="search"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="available">Tersedia</option>
                                        <option value="low">Stok Menipis</option>
                                        <option value="out">Habis</option>
                                    </select>
                                </div>

                                <!-- Sort -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium">Urutkan</label>
                                    <select
                                        v-model="filters.sort_by"
                                        class="w-full rounded border px-3 py-2 dark:border-gray-500 dark:bg-gray-600"
                                        @change="search"
                                    >
                                        <option value="nama">Nama</option>
                                        <option value="harga">Harga</option>
                                        <option value="stok">Stok</option>
                                        <option value="created_at">Tanggal Dibuat</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedProducts.length > 0" class="mb-4 rounded border border-blue-200 bg-blue-50 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-blue-800"> {{ selectedProducts.length }} produk dipilih </span>
                                <div class="space-x-2">
                                    <button
                                        @click="showBulkDeleteModal = true"
                                        class="rounded bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-700"
                                    >
                                        Hapus Terpilih
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="px-4 py-2 text-left">
                                            <input type="checkbox" @change="toggleAllProducts" :checked="allProductsSelected" />
                                        </th>
                                        <th class="px-4 py-2 text-left">Gambar</th>
                                        <th class="px-4 py-2 text-left">ID Produk</th>
                                        <th class="px-4 py-2 text-left">Nama</th>
                                        <th class="px-4 py-2 text-left">Kategori</th>
                                        <th class="px-4 py-2 text-left">Harga</th>
                                        <th class="px-4 py-2 text-left">Stok</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in produk.data" :key="product.id_produk" class="border-b dark:border-gray-600">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" :value="product.id_produk" v-model="selectedProducts" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <img
                                                v-if="product.gambar"
                                                :src="`/storage/${product.gambar}`"
                                                alt="Product Image"
                                                class="h-16 w-16 rounded object-cover"
                                            />
                                            <div v-else class="flex h-16 w-16 items-center justify-center rounded bg-gray-200">
                                                <span class="text-xs text-gray-500">No Image</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 font-mono text-sm">{{ product.id_produk }}</td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ product.nama }}</div>
                                            <div v-if="product.nomor_bpom" class="text-sm text-gray-500">BPOM: {{ product.nomor_bpom }}</div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span v-if="product.kategori" class="rounded bg-blue-100 px-2 py-1 text-sm text-blue-800">
                                                {{ product.kategori.nama }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ formatPrice(product.harga) }}</div>
                                            <div v-if="product.harga_pack" class="text-sm text-gray-500">
                                                Pack: {{ formatPrice(product.harga_pack) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ product.stok }} {{ product.satuan }}</div>
                                            <div v-if="product.batas_stok" class="text-sm text-gray-500">Min: {{ product.batas_stok }}</div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': product.stok > product.batas_stok,
                                                    'bg-yellow-100 text-yellow-800': product.stok <= product.batas_stok && product.stok > 0,
                                                    'bg-red-100 text-red-800': product.stok === 0,
                                                }"
                                                class="rounded px-2 py-1 text-sm"
                                            >
                                                {{ product.stok === 0 ? 'Habis' : product.stok <= product.batas_stok ? 'Menipis' : 'Aman' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex space-x-2">
                                                <Link
                                                    :href="route('admin.produk.show', product.id_produk)"
                                                    class="text-sm text-blue-600 hover:text-blue-900"
                                                >
                                                    Detail
                                                </Link>
                                                <Link
                                                    :href="route('admin.produk.edit', product.id_produk)"
                                                    class="text-sm text-yellow-600 hover:text-yellow-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button @click="confirmDelete(product)" class="text-sm text-red-600 hover:text-red-900">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex flex-1 justify-between sm:hidden">
                                    <Link
                                        v-if="produk.prev_page_url"
                                        :href="produk.prev_page_url"
                                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="produk.next_page_url"
                                        :href="produk.next_page_url"
                                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                </div>
                                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing {{ produk.from }} to {{ produk.to }} of {{ produk.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm">
                                            <Link
                                                v-for="link in produk.links"
                                                :key="link.label"
                                                :href="link.url"
                                                v-html="link.label"
                                                :class="{
                                                    'border-blue-500 bg-blue-50 text-blue-600': link.active,
                                                    'border-gray-300 bg-white text-gray-500 hover:bg-gray-50': !link.active,
                                                }"
                                                class="relative inline-flex items-center border px-4 py-2 text-sm font-medium"
                                            />
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            :show="showDeleteModal"
            title="Konfirmasi Hapus"
            :message="`Apakah Anda yakin ingin menghapus produk '${productToDelete?.nama}'? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteProduct"
            @cancel="showDeleteModal = false"
        />

        <!-- Bulk Delete Confirmation Modal -->
        <ConfirmModal
            :show="showBulkDeleteModal"
            title="Konfirmasi Hapus Massal"
            :message="`Apakah Anda yakin ingin menghapus ${selectedProducts.length} produk terpilih? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="bulkDeleteProducts"
            @cancel="showBulkDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup>
import ConfirmModal from '@/components/ConfirmModal.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    produk: Object,
    kategori: Array,
    filters: Object,
});

// Reactive data
const selectedProducts = ref([]);
const showDeleteModal = ref(false);
const showBulkDeleteModal = ref(false);
const productToDelete = ref(null);

const filters = reactive({
    search: props.filters.search || '',
    kategori: props.filters.kategori || '',
    stock_status: props.filters.stock_status || '',
    sort_by: props.filters.sort_by || 'nama',
    sort_order: props.filters.sort_order || 'asc',
});

// Computed properties
const allProductsSelected = computed(() => {
    return props.produk.data.length > 0 && selectedProducts.value.length === props.produk.data.length;
});

// Methods
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const search = debounce(() => {
    router.get(route('admin.produk.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const toggleAllProducts = () => {
    if (allProductsSelected.value) {
        selectedProducts.value = [];
    } else {
        selectedProducts.value = props.produk.data.map((product) => product.id_produk);
    }
};

const confirmDelete = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const deleteProduct = () => {
    router.delete(route('admin.produk.destroy', productToDelete.value.id_produk), {
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        },
    });
};

const bulkDeleteProducts = () => {
    router.post(
        route('admin.produk.bulk-action'),
        {
            action: 'delete',
            ids: selectedProducts.value,
        },
        {
            onSuccess: () => {
                showBulkDeleteModal.value = false;
                selectedProducts.value = [];
            },
        },
    );
};
</script>
