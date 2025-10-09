<template>
    <AdminLayout>
        <Head title="Edit Produk" />

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Edit Produk: {{ produk.nama }}</h2>
                            <div class="space-x-2">
                                <Link
                                    :href="route('admin.produk.show', produk.id_produk)"
                                    class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                                >
                                    Detail
                                </Link>
                                <Link
                                    :href="route('admin.produk.index')"
                                    class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"
                                >
                                    Kembali
                                </Link>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <!-- ID Produk (Read Only) -->
                                    <div>
                                        <label for="id_produk" class="mb-1 block text-sm font-medium">ID Produk (Barcode)</label>
                                        <input
                                            id="id_produk"
                                            :value="produk.id_produk"
                                            type="text"
                                            readonly
                                            class="w-full cursor-not-allowed rounded-md border bg-gray-100 px-3 py-2 dark:border-gray-500 dark:bg-gray-600"
                                        />
                                        <p class="mt-1 text-sm text-gray-500">ID produk tidak dapat diubah</p>
                                    </div>

                                    <!-- Nama Produk -->
                                    <div>
                                        <label for="nama" class="mb-1 block text-sm font-medium">Nama Produk</label>
                                        <input
                                            id="nama"
                                            v-model="form.nama"
                                            type="text"
                                            class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                            :class="{ 'border-red-500': errors.nama }"
                                        />
                                        <div v-if="errors.nama" class="mt-1 text-sm text-red-500">
                                            {{ errors.nama }}
                                        </div>
                                    </div>

                                    <!-- Nomor BPOM -->
                                    <div>
                                        <label for="nomor_bpom" class="mb-1 block text-sm font-medium">Nomor BPOM (Opsional)</label>
                                        <input
                                            id="nomor_bpom"
                                            v-model="form.nomor_bpom"
                                            type="text"
                                            class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                            :class="{ 'border-red-500': errors.nomor_bpom }"
                                        />
                                        <div v-if="errors.nomor_bpom" class="mt-1 text-sm text-red-500">
                                            {{ errors.nomor_bpom }}
                                        </div>
                                    </div>

                                    <!-- Kategori -->
                                    <div>
                                        <label for="id_kategori" class="mb-1 block text-sm font-medium">Kategori</label>
                                        <select
                                            id="id_kategori"
                                            v-model="form.id_kategori"
                                            class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                            :class="{ 'border-red-500': errors.id_kategori }"
                                        >
                                            <option value="">Pilih Kategori</option>
                                            <option v-for="cat in kategori" :key="cat.id_kategori" :value="cat.id_kategori">
                                                {{ cat.nama }}
                                            </option>
                                        </select>
                                        <div v-if="errors.id_kategori" class="mt-1 text-sm text-red-500">
                                            {{ errors.id_kategori }}
                                        </div>
                                    </div>

                                    <!-- Gambar -->
                                    <div>
                                        <label for="gambar" class="mb-1 block text-sm font-medium">Gambar Produk</label>
                                        <!-- Current Image -->
                                        <div v-if="produk.gambar && !imagePreview" class="mb-3">
                                            <p class="mb-2 text-sm text-gray-600">Gambar saat ini:</p>
                                            <img
                                                :src="`/storage/${produk.gambar}`"
                                                alt="Current Product Image"
                                                class="h-32 w-32 rounded border object-cover"
                                            />
                                        </div>
                                        <!-- New Image Preview -->
                                        <div v-if="imagePreview" class="mb-3">
                                            <p class="mb-2 text-sm text-gray-600">Gambar baru:</p>
                                            <img :src="imagePreview" alt="New Image Preview" class="h-32 w-32 rounded border object-cover" />
                                        </div>
                                        <input
                                            id="gambar"
                                            type="file"
                                            accept="image/*"
                                            @change="handleImageUpload"
                                            class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                            :class="{ 'border-red-500': errors.gambar }"
                                        />
                                        <div v-if="errors.gambar" class="mt-1 text-sm text-red-500">
                                            {{ errors.gambar }}
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">Biarkan kosong jika tidak ingin mengubah gambar</p>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-4">
                                    <!-- Harga & Biaya -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="biaya_produk" class="mb-1 block text-sm font-medium">Biaya Produk</label>
                                            <input
                                                id="biaya_produk"
                                                v-model="form.biaya_produk"
                                                type="number"
                                                min="0"
                                                step="1"
                                                class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                :class="{ 'border-red-500': errors.biaya_produk }"
                                            />
                                            <div v-if="errors.biaya_produk" class="mt-1 text-sm text-red-500">
                                                {{ errors.biaya_produk }}
                                            </div>
                                        </div>
                                        <div>
                                            <label for="harga" class="mb-1 block text-sm font-medium">Harga Jual</label>
                                            <input
                                                id="harga"
                                                v-model="form.harga"
                                                type="number"
                                                min="0"
                                                step="1"
                                                class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                :class="{ 'border-red-500': errors.harga }"
                                            />
                                            <div v-if="errors.harga" class="mt-1 text-sm text-red-500">
                                                {{ errors.harga }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stok & Satuan -->
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label for="stok" class="mb-1 block text-sm font-medium">Stok</label>
                                            <input
                                                id="stok"
                                                v-model="form.stok"
                                                type="number"
                                                min="0"
                                                class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                :class="{ 'border-red-500': errors.stok }"
                                            />
                                            <div v-if="errors.stok" class="mt-1 text-sm text-red-500">
                                                {{ errors.stok }}
                                            </div>
                                        </div>
                                        <div>
                                            <label for="batas_stok" class="mb-1 block text-sm font-medium">Batas Stok</label>
                                            <input
                                                id="batas_stok"
                                                v-model="form.batas_stok"
                                                type="number"
                                                min="0"
                                                class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                :class="{ 'border-red-500': errors.batas_stok }"
                                            />
                                            <div v-if="errors.batas_stok" class="mt-1 text-sm text-red-500">
                                                {{ errors.batas_stok }}
                                            </div>
                                        </div>
                                        <div>
                                            <label for="satuan" class="mb-1 block text-sm font-medium">Satuan</label>
                                            <input
                                                id="satuan"
                                                v-model="form.satuan"
                                                type="text"
                                                placeholder="pcs, box, dll"
                                                class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                :class="{ 'border-red-500': errors.satuan }"
                                            />
                                            <div v-if="errors.satuan" class="mt-1 text-sm text-red-500">
                                                {{ errors.satuan }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pack Information -->
                                    <div class="border-t pt-4">
                                        <h3 class="mb-3 text-lg font-medium">Informasi Pack (Opsional)</h3>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div>
                                                <label for="satuan_pack" class="mb-1 block text-sm font-medium">Satuan Pack</label>
                                                <input
                                                    id="satuan_pack"
                                                    v-model="form.satuan_pack"
                                                    type="text"
                                                    placeholder="dus, karton, dll"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.satuan_pack }"
                                                />
                                                <div v-if="errors.satuan_pack" class="mt-1 text-sm text-red-500">
                                                    {{ errors.satuan_pack }}
                                                </div>
                                            </div>
                                            <div>
                                                <label for="isi_per_pack" class="mb-1 block text-sm font-medium">Isi per Pack</label>
                                                <input
                                                    id="isi_per_pack"
                                                    v-model="form.isi_per_pack"
                                                    type="number"
                                                    min="1"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.isi_per_pack }"
                                                />
                                                <div v-if="errors.isi_per_pack" class="mt-1 text-sm text-red-500">
                                                    {{ errors.isi_per_pack }}
                                                </div>
                                            </div>
                                            <div>
                                                <label for="harga_pack" class="mb-1 block text-sm font-medium">Harga Pack</label>
                                                <input
                                                    id="harga_pack"
                                                    v-model="form.harga_pack"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.harga_pack }"
                                                />
                                                <div v-if="errors.harga_pack" class="mt-1 text-sm text-red-500">
                                                    {{ errors.harga_pack }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discount Information -->
                                    <div class="border-t pt-4">
                                        <h3 class="mb-3 text-lg font-medium">Informasi Diskon (Opsional)</h3>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div>
                                                <label for="min_beli_diskon" class="mb-1 block text-sm font-medium">Min. Beli Diskon</label>
                                                <input
                                                    id="min_beli_diskon"
                                                    v-model="form.min_beli_diskon"
                                                    type="number"
                                                    min="0"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.min_beli_diskon }"
                                                />
                                                <div v-if="errors.min_beli_diskon" class="mt-1 text-sm text-red-500">
                                                    {{ errors.min_beli_diskon }}
                                                </div>
                                            </div>
                                            <div>
                                                <label for="harga_diskon_unit" class="mb-1 block text-sm font-medium">Harga Diskon Unit</label>
                                                <input
                                                    id="harga_diskon_unit"
                                                    v-model="form.harga_diskon_unit"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.harga_diskon_unit }"
                                                />
                                                <div v-if="errors.harga_diskon_unit" class="mt-1 text-sm text-red-500">
                                                    {{ errors.harga_diskon_unit }}
                                                </div>
                                            </div>
                                            <div>
                                                <label for="harga_diskon_pack" class="mb-1 block text-sm font-medium">Harga Diskon Pack</label>
                                                <input
                                                    id="harga_diskon_pack"
                                                    v-model="form.harga_diskon_pack"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    class="w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                                    :class="{ 'border-red-500': errors.harga_diskon_pack }"
                                                />
                                                <div v-if="errors.harga_diskon_pack" class="mt-1 text-sm text-red-500">
                                                    {{ errors.harga_diskon_pack }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="mt-8 flex justify-end space-x-4 border-t pt-6">
                                <Link
                                    :href="route('admin.produk.index')"
                                    class="rounded bg-gray-500 px-6 py-2 font-bold text-white hover:bg-gray-700"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="rounded bg-blue-500 px-6 py-2 font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ processing ? 'Menyimpan...' : 'Update Produk' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    produk: Object,
    kategori: Array,
});

// Form data
const form = useForm({
    nama: props.produk.nama,
    nomor_bpom: props.produk.nomor_bpom || '',
    harga: props.produk.harga,
    biaya_produk: props.produk.biaya_produk,
    stok: props.produk.stok,
    batas_stok: props.produk.batas_stok,
    satuan: props.produk.satuan,
    satuan_pack: props.produk.satuan_pack || '',
    isi_per_pack: props.produk.isi_per_pack || '',
    harga_pack: props.produk.harga_pack || '',
    min_beli_diskon: props.produk.min_beli_diskon || '',
    harga_diskon_unit: props.produk.harga_diskon_unit || '',
    harga_diskon_pack: props.produk.harga_diskon_pack || '',
    id_kategori: props.produk.id_kategori,
    gambar: null,
});

const imagePreview = ref(null);
const processing = ref(false);
const errors = ref({});

// Methods
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    form.gambar = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
};

const submit = () => {
    processing.value = true;

    form.post(route('admin.produk.update', props.produk.id_produk), {
        _method: 'put',
        onSuccess: () => {
            processing.value = false;
        },
        onError: (formErrors) => {
            processing.value = false;
            errors.value = formErrors;
        },
    });
};
</script>
