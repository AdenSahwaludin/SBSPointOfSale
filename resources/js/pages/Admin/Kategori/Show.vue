<template>
    <AdminLayout>
        <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Link
                                :href="route('admin.kategori.index')"
                                class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-150"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Detail Kategori</h1>
                                <p class="text-gray-600 mt-1">Informasi lengkap kategori {{ kategori.nama }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.kategori.edit', kategori.id_kategori)"
                                class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Kategori
                            </Link>
                            <button
                                @click="deleteCategory"
                                :disabled="kategori.produk.length > 0"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-150"
                                :title="kategori.produk.length > 0 ? 'Tidak dapat menghapus kategori yang memiliki produk' : 'Hapus kategori'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </div>
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

            <!-- Category Information -->
            <div class="bg-white rounded-lg shadow-sm border mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informasi Kategori</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID Kategori</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ kategori.id_kategori }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Kategori</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ kategori.nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Produk</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ kategori.produk.length }} produk
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Dibuat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(kategori.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(kategori.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Products in Category -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">
                            Produk dalam Kategori ({{ kategori.produk.length }})
                        </h2>
                        <Link
                            :href="route('admin.produk.create')"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-150"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Produk
                        </Link>
                    </div>
                </div>

                <div v-if="kategori.produk.length > 0" class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produk
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Harga
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stok
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="produk in kategori.produk" :key="produk.id_produk" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ produk.nama }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ produk.id_produk }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ produk.sku }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ formatCurrency(produk.harga) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        produk.stok > 10 ? 'bg-green-100 text-green-800' :
                                        produk.stok > 0 ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800'
                                    ]">
                                        {{ produk.stok }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('admin.produk.show', produk.id_produk)"
                                        class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-150"
                                    >
                                        Lihat
                                    </Link>
                                    <Link
                                        :href="route('admin.produk.edit', produk.id_produk)"
                                        class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada produk</h3>
                    <p class="mt-1 text-sm text-gray-500">Kategori ini belum memiliki produk. Mulai dengan menambahkan produk pertama.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('admin.produk.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-150"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Produk
                        </Link>
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
                            Apakah Anda yakin ingin menghapus kategori "{{ kategori.nama }}"? Tindakan ini tidak dapat dibatalkan.
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { defineProps } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Props
const props = defineProps({
    kategori: Object
})

// State
const showDeleteModal = ref(false)

// Methods
const deleteCategory = () => {
    if (props.kategori.produk.length > 0) {
        return
    }
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
}

const confirmDelete = () => {
    router.delete(route('admin.kategori.destroy', props.kategori.id_kategori), {
        onSuccess: () => {
            // Redirect akan dilakukan otomatis oleh controller
        }
    })
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount)
}
</script>

<style scoped>
/* Custom styles jika diperlukan */
</style>