<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Define interfaces
interface Kategori {
    id_kategori: string;
    nama: string;
}

interface Props {
    kategori: Kategori[];
    nextPrefix: string;
}

const props = defineProps<Props>();

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/produk');

// Form state
const form = useForm({
    suffix: '',
    nama: '',
    kategori_id: '',
    harga: '',
    stok: '',
    satuan: 'pcs',
});

function submit() {
    form.post('/admin/produk', {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}
</script>

<template>
    <Head title="Tambah Produk - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Tambah Produk</h1>
                    <p class="text-emerald-600">Buat produk baru</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Produk -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Produk * </label>
                            <div class="flex items-center">
                                <span class="mr-2 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 font-mono">
                                    {{ props.nextPrefix }}-
                                </span>
                                <input
                                    v-model="form.suffix"
                                    type="text"
                                    maxlength="4"
                                    class="flex-1 rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="4 alfanumerik"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.suffix" class="mt-1 text-sm text-red-600">
                                {{ form.errors.suffix }}
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Kategori * </label>
                            <select
                                v-model="form.kategori_id"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                required
                            >
                                <option value="">Pilih Kategori</option>
                                <option v-for="kategori in props.kategori" :key="kategori.id_kategori" :value="kategori.id_kategori">
                                    {{ kategori.nama }}
                                </option>
                            </select>
                            <div v-if="form.errors.kategori_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.kategori_id }}
                            </div>
                        </div>

                        <!-- Nama -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Nama Produk * </label>
                            <input
                                v-model="form.nama"
                                type="text"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Nama produk"
                                required
                            />
                            <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nama }}
                            </div>
                        </div>

                        <!-- Harga -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Harga * </label>
                            <input
                                v-model="form.harga"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="0"
                                required
                            />
                            <div v-if="form.errors.harga" class="mt-1 text-sm text-red-600">
                                {{ form.errors.harga }}
                            </div>
                        </div>

                        <!-- Stok -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Stok * </label>
                            <input
                                v-model="form.stok"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="0"
                                required
                            />
                            <div v-if="form.errors.stok" class="mt-1 text-sm text-red-600">
                                {{ form.errors.stok }}
                            </div>
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Satuan * </label>
                            <select
                                v-model="form.satuan"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                required
                            >
                                <option value="pcs">Pcs</option>
                                <option value="pack">Pack</option>
                                <option value="box">Box</option>
                                <option value="bottle">Bottle</option>
                                <option value="tube">Tube</option>
                            </select>
                            <div v-if="form.errors.satuan" class="mt-1 text-sm text-red-600">
                                {{ form.errors.satuan }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-emerald-200 pt-6">
                        <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Produk</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
