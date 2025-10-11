<template>
    <AdminLayout>
        <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
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
                            <h1 class="text-2xl font-bold text-gray-900">Tambah Kategori Baru</h1>
                            <p class="text-gray-600 mt-1">Buat kategori produk baru untuk toko Anda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Alert Messages -->
            <div v-if="$page.props.errors.error" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ $page.props.errors.error }}
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informasi Kategori</h2>
                    <p class="text-sm text-gray-600 mt-1">Masukkan detail kategori produk yang akan dibuat</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- ID Kategori -->
                    <div>
                        <label for="id_kategori" class="block text-sm font-medium text-gray-700 mb-2">
                            ID Kategori <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="id_kategori"
                            v-model="form.id_kategori"
                            type="text"
                            maxlength="4"
                            placeholder="Contoh: ELK, FRN, CSM"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.id_kategori }"
                            @input="form.id_kategori = $event.target.value.toUpperCase()"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Maksimal 4 karakter, huruf besar dan angka saja. Contoh: ELK (Elektronik), FRN (Furniture)
                        </p>
                        <div v-if="errors.id_kategori" class="text-red-600 text-sm mt-1">
                            {{ errors.id_kategori }}
                        </div>
                    </div>

                    <!-- Nama Kategori -->
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            maxlength="50"
                            placeholder="Masukkan nama kategori"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.nama }"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Maksimal 50 karakter. Contoh: Elektronik, Furniture, Kosmetik
                        </p>
                        <div v-if="errors.nama" class="text-red-600 text-sm mt-1">
                            {{ errors.nama }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <Link
                            :href="route('admin.kategori.index')"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors duration-150"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition-colors duration-150 flex items-center"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Tips Pembuatan Kategori</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Gunakan ID kategori yang mudah diingat dan deskriptif</li>
                    <li>• Nama kategori harus jelas dan mudah dipahami</li>
                    <li>• Hindari duplikasi nama kategori yang sudah ada</li>
                    <li>• ID kategori akan otomatis diubah menjadi huruf besar</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Form handling
const form = useForm({
    id_kategori: '',
    nama: ''
})

// Computed errors
const errors = computed(() => {
    return Object.fromEntries(
        Object.entries($page.props.errors || {}).filter(([key]) => 
            ['id_kategori', 'nama'].includes(key)
        )
    )
})

// Methods
const submit = () => {
    form.post(route('admin.kategori.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Form akan redirect otomatis jika berhasil
        }
    })
}
</script>

<style scoped>
/* Custom styles jika diperlukan */
</style>