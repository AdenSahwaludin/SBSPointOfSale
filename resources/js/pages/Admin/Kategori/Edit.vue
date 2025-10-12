<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Kategori {
    id_kategori: string;
    nama: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    kategori: Kategori;
}

const props = defineProps<Props>();

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/kategori');

// Form management
const form = useForm({
    nama: props.kategori.nama,
});

// Submit form
function submit() {
    form.put(`/admin/kategori/${props.kategori.id_kategori}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Edit Kategori - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Edit Kategori</h1>
                    <p class="text-emerald-600">Perbarui informasi kategori {{ kategori.nama }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/kategori')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Kategori (Read Only) -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Kategori </label>
                            <input
                                :value="kategori.id_kategori"
                                type="text"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-600"
                            />
                            <p class="mt-1 text-xs text-emerald-600">ID kategori tidak dapat diubah</p>
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

                    <!-- Category Info -->
                    <div class="border-t border-emerald-200 pt-6">
                        <h3 class="mb-4 text-lg font-medium text-emerald-800">Informasi Tambahan</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <span class="text-sm font-medium text-emerald-600">Tanggal Dibuat:</span>
                                <div class="text-sm text-emerald-800">{{ formatDate(kategori.created_at) }}</div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-emerald-600">Terakhir Diperbarui:</span>
                                <div class="text-sm text-emerald-800">{{ formatDate(kategori.updated_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-emerald-200 pt-6">
                        <BaseButton @click="$inertia.visit('/admin/kategori')" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                            <span v-if="form.processing">Memperbarui...</span>
                            <span v-else>Perbarui Kategori</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
