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

interface Produk {
    id_produk: string;
    sku: string;
    nama: string;
    id_kategori: string;
    barcode: string;
    no_bpom: string;
    satuan: string;
    isi_per_pack: number;
    harga: number;
    harga_pack: number;
    stok: number;
    sisa_pcs_terbuka: number;
    batas_stok_minimum: number;
    jumlah_restock: number;
}

interface Props {
    produk: Produk;
    kategori: Kategori[];
}

const props = defineProps<Props>();

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/produk');

// Form initialization with all 13 fields
const form = useForm({
    nama: props.produk.nama,
    id_kategori: props.produk.id_kategori,
    barcode: props.produk.barcode,
    no_bpom: props.produk.no_bpom,
    satuan: props.produk.satuan,
    isi_per_pack: props.produk.isi_per_pack,
    harga: props.produk.harga,
    harga_pack: props.produk.harga_pack,
    stok: props.produk.stok,
    sisa_pcs_terbuka: props.produk.sisa_pcs_terbuka,
    batas_stok_minimum: props.produk.batas_stok_minimum,
    jumlah_restock: props.produk.jumlah_restock,
});

function submit() {
    form.patch(`/admin/produk/${props.produk.id_produk}`);
}
</script>

<template>
    <Head title="Edit Produk - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Edit Produk</h1>
                    <p class="text-emerald-600">Ubah data produk: {{ produk.nama }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Section 1: Identity -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Identitas Produk</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- ID Produk -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Produk </label>
                                <input
                                    :value="props.produk.id_produk"
                                    type="text"
                                    class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-600"
                                    readonly
                                />
                            </div>

                            <!-- SKU (Read Only) -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> SKU (Auto Generated) </label>
                                <input
                                    :value="props.produk.sku"
                                    type="text"
                                    disabled
                                    class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700"
                                />
                            </div>

                            <!-- Nama -->
                            <div class="md:col-span-2">
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

                            <!-- Barcode -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Barcode </label>
                                <input
                                    v-model="form.barcode"
                                    type="text"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Kode barcode (13 digit)"
                                    maxlength="13"
                                />
                                <div v-if="form.errors.barcode" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.barcode }}
                                </div>
                            </div>

                            <!-- No BPOM -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> No BPOM </label>
                                <input
                                    v-model="form.no_bpom"
                                    type="text"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Nomor registrasi BPOM"
                                    maxlength="18"
                                />
                                <div v-if="form.errors.no_bpom" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.no_bpom }}
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Kategori * </label>
                                <select
                                    v-model="form.id_kategori"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    required
                                >
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="kategori in props.kategori" :key="kategori.id_kategori" :value="kategori.id_kategori">
                                        {{ kategori.nama }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_kategori" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.id_kategori }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Satuan & Kemasan -->
                    <div class="border-t border-emerald-200 pt-6">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Satuan & Kemasan</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Satuan -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Satuan * </label>
                                <select
                                    v-model="form.satuan"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    required
                                >
                                    <option value="pcs">Pcs</option>
                                    <option value="karton">Karton</option>
                                    <option value="pack">Pack</option>
                                </select>
                                <div v-if="form.errors.satuan" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.satuan }}
                                </div>
                            </div>

                            <!-- Isi per Pack -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Isi per Pack * </label>
                                <input
                                    v-model.number="form.isi_per_pack"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Jumlah item per kemasan"
                                    required
                                />
                                <div v-if="form.errors.isi_per_pack" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.isi_per_pack }}
                                </div>
                            </div>

                            <!-- Sisa PCS Terbuka -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Sisa PCS Terbuka * </label>
                                <input
                                    v-model.number="form.sisa_pcs_terbuka"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Sisa PCS dari kemasan terbuka"
                                    required
                                />
                                <div v-if="form.errors.sisa_pcs_terbuka" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.sisa_pcs_terbuka }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Harga -->
                    <div class="border-t border-emerald-200 pt-6">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Harga</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Harga -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Harga Satuan * </label>
                                <input
                                    v-model.number="form.harga"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Harga per satuan"
                                    required
                                />
                                <div v-if="form.errors.harga" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.harga }}
                                </div>
                            </div>

                            <!-- Harga Pack -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Harga Pack </label>
                                <input
                                    v-model.number="form.harga_pack"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Harga untuk 1 pack/kemasan"
                                />
                                <div v-if="form.errors.harga_pack" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.harga_pack }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Stok & Reorder -->
                    <div class="border-t border-emerald-200 pt-6">
                        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Stok & Reorder</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Stok -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Stok * </label>
                                <input
                                    v-model.number="form.stok"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Jumlah stok"
                                    required
                                />
                                <div v-if="form.errors.stok" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.stok }}
                                </div>
                            </div>

                            <!-- Batas Stok Minimum -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Batas Stok Minimum (ROP) * </label>
                                <input
                                    v-model.number="form.batas_stok_minimum"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Stok minimal untuk reorder"
                                    required
                                />
                                <div v-if="form.errors.batas_stok_minimum" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.batas_stok_minimum }}
                                </div>
                            </div>

                            <!-- Jumlah Restock -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-emerald-700"> Jumlah Restock (ROQ) * </label>
                                <input
                                    v-model.number="form.jumlah_restock"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="Jumlah untuk setiap restock"
                                    required
                                />
                                <div v-if="form.errors.jumlah_restock" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.jumlah_restock }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-emerald-200 pt-6">
                        <BaseButton @click="$inertia.visit('/admin/produk')" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Update Produk</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
