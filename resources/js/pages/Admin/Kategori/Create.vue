<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/kategori');

// Form management
const form = useForm({
    id_kategori: '',
    nama: '',
});

// Submit form
function submit() {
    form.post('/admin/kategori', {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}

// Handle ID kategori input - only allow uppercase alphanumeric
function handleIdInput(event: Event) {
    const target = event.target as HTMLInputElement;
    const value = target.value;
    // Convert to uppercase and remove non-alphanumeric characters
    const cleanValue = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    form.id_kategori = cleanValue;
    target.value = cleanValue;
}
</script>

<template>
    <Head title="Tambah Kategori - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Tambah Kategori</h1>
                    <p class="text-emerald-600">Buat kategori produk baru</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/kategori')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Kategori -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Kategori * </label>
                            <input
                                v-model="form.id_kategori"
                                type="text"
                                maxlength="4"
                                @input="handleIdInput"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Contoh: ELK, FRN"
                                required
                            />
                            <p class="mt-1 text-xs text-emerald-600">Maksimal 4 karakter huruf besar dan angka</p>
                            <div v-if="form.errors.id_kategori" class="mt-1 text-sm text-red-600">
                                {{ form.errors.id_kategori }}
                            </div>
                        </div>

                        <!-- Nama Kategori -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Nama Kategori * </label>
                            <input
                                v-model="form.nama"
                                type="text"
                                maxlength="50"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Contoh: Elektronik, Furniture"
                                required
                            />
                            <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nama }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-emerald-200 pt-6">
                        <BaseButton @click="$inertia.visit('/admin/kategori')" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Kategori</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
