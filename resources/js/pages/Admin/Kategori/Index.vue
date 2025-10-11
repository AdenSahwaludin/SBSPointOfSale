<template>
<template>
    <AdminLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="py-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Manajemen Kategori</h1>
                                <p class="text-gray-600 mt-1">Kelola kategori produk untuk toko Anda</p>
                            </div>
                            <Link
                                :href="route('admin.kategori.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-150"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Kategori
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Alert Messages -->
            <div v-if="$page.props.flash.success" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ $page.props.flash.success }}
            </div>
            
            <div v-if="$page.props.errors.delete" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ $page.props.errors.delete }}
            </div>

            <div v-if="$page.props.errors.error" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ $page.props.errors.error }}
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari Kategori</label>
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Masukkan nama kategori..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @keyup.enter="search"
                        />
                    </div>
                    <div class="sm:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan</label>
                        <select
                            v-model="searchForm.sort_by"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="search"
                        >
                            <option value="nama">Nama</option>
                            <option value="created_at">Tanggal Dibuat</option>
                            <option value="produk_count">Jumlah Produk</option>
                        </select>
                    </div>
                    <div class="sm:w-32">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Arah</label>
                        <select
                            v-model="searchForm.sort_order"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="search"
                        >
                            <option value="asc">A-Z</option>
                            <option value="desc">Z-A</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="search"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-colors duration-150"
                        >
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedItems.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-blue-700 font-medium">{{ selectedItems.length }} kategori dipilih</span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkDelete"
                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded transition-colors duration-150"
                        >
                            Hapus Terpilih
                        </button>
                        <button
                            @click="clearSelection"
                            class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded transition-colors duration-150"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID Kategori
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Kategori
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah Produk
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Dibuat
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in kategori.data" :key="item.id_kategori" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="item.id_kategori"
                                        v-model="selectedItems"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.id_kategori }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ item.nama }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ item.produk_count }} produk
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('admin.kategori.show', item.id_kategori)"
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                            title="Lihat Detail"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.kategori.edit', item.id_kategori)"
                                            class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150"
                                            title="Edit"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteItem(item.id_kategori)"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                            title="Hapus"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="kategori.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada kategori</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan kategori produk pertama Anda.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('admin.kategori.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-150"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Kategori
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="kategori.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Menampilkan {{ kategori.from }} sampai {{ kategori.to }} dari {{ kategori.total }} kategori
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in kategori.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 text-sm rounded transition-colors duration-150',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                        ? 'text-gray-700 hover:bg-gray-100'
                                        : 'text-gray-400 cursor-not-allowed'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeDeleteModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">Konfirmasi Hapus</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Apakah Anda yakin ingin menghapus kategori ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="flex gap-4 px-4 py-3">
                        <button
                            @click="closeDeleteModal"
                            class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-base font-medium rounded-md shadow-sm transition-colors duration-150"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmDelete"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-base font-medium rounded-md shadow-sm transition-colors duration-150"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { defineProps } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Props
const props = defineProps({
    kategori: Object,
    filters: Object
})

// Reactive state
const selectedItems = ref([])
const showDeleteModal = ref(false)
const deleteId = ref(null)

const searchForm = reactive({
    search: props.filters.search || '',
    sort_by: props.filters.sort_by || 'nama',
    sort_order: props.filters.sort_order || 'asc'
})

// Computed
const allSelected = computed(() => {
    return props.kategori.data.length > 0 && selectedItems.value.length === props.kategori.data.length
})

// Methods
const toggleAll = () => {
    if (allSelected.value) {
        selectedItems.value = []
    } else {
        selectedItems.value = props.kategori.data.map(item => item.id_kategori)
    }
}

const clearSelection = () => {
    selectedItems.value = []
}

const search = () => {
    router.get(route('admin.kategori.index'), searchForm, {
        preserveState: true,
        replace: true
    })
}

const deleteItem = (id) => {
    deleteId.value = id
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    deleteId.value = null
}

const confirmDelete = () => {
    if (deleteId.value) {
        router.delete(route('admin.kategori.destroy', deleteId.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal()
            }
        })
    }
}

const bulkDelete = () => {
    if (selectedItems.value.length === 0) return
    
    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItems.value.length} kategori?`)) {
        router.post(route('admin.kategori.bulk-action'), {
            action: 'delete',
            ids: selectedItems.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedItems.value = []
            }
        })
    }
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<style scoped>
/* Custom styles jika diperlukan */
</style>
