<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Produk {
    id_produk: number;
    nama: string;
    sku: string;
    satuan: string;
    isi_per_pack: number;
    stok: number;
    kategori: string;
}

interface KonversiStok {
    id_konversi: number;
    from_produk_id: number;
    to_produk_id: number;
    rasio: number;
    qty_from: number;
    qty_to: number;
    keterangan: string | null;
    from_produk: Produk;
    to_produk: Produk;
}

interface Props {
    konversi: KonversiStok;
    produk: Produk[];
}

const props = defineProps<Props>();

const adminMenuItems = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'fas fa-tachometer-alt',
    },
    {
        name: 'Manajemen Data',
        icon: 'fas fa-database',
        children: [
            { name: 'Pengguna', href: '/admin/pengguna', icon: 'fas fa-users' },
            { name: 'Produk', href: '/admin/produk', icon: 'fas fa-boxes' },
            { name: 'Kategori', href: '/admin/kategori', icon: 'fas fa-tags' },
            { name: 'Pelanggan', href: '/admin/pelanggan', icon: 'fas fa-user-friends' },
            { name: 'Konversi Stok', href: '/admin/konversi-stok', icon: 'fas fa-exchange-alt', active: true },
        ],
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-cash-register',
        children: [
            { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
            { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
            { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
        ],
    },
    {
        name: 'Laporan',
        href: '/admin/reports',
        icon: 'fas fa-chart-bar',
    },
    {
        name: 'Pengaturan',
        href: '/admin/settings',
        icon: 'fas fa-cog',
    },
];

const form = useForm({
    from_produk_id: props.konversi.from_produk_id,
    to_produk_id: props.konversi.to_produk_id,
    rasio: props.konversi.rasio,
    qty_from: props.konversi.qty_from,
    qty_to: props.konversi.qty_to,
    keterangan: props.konversi.keterangan || '',
});

const selectedFromProduk = computed(() => {
    return props.produk.find((p) => p.id_produk === form.from_produk_id);
});

const selectedToProduk = computed(() => {
    return props.produk.find((p) => p.id_produk === form.to_produk_id);
});

// Auto-calculate rasio when qty_from or qty_to changes
watch([() => form.qty_from, () => form.qty_to], () => {
    if (form.qty_from > 0 && form.qty_to > 0) {
        form.rasio = Math.round(form.qty_to / form.qty_from);
    }
});

function submit() {
    form.put(`/admin/konversi-stok/${props.konversi.id_konversi}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Form will redirect on success
        },
    });
}
</script>

<template>
    <BaseLayout title="Edit Konversi Stok - Sari Bumi Sakti" :menuItems="adminMenuItems" userRole="admin">
        <template #header> Edit Konversi Stok </template>

        <Head title="Edit Konversi Stok" />

        <!-- Header Section -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-emerald-800">Edit Konversi Stok</h2>
                <p class="mt-1 text-sm text-gray-600">Perbarui aturan konversi antar satuan produk</p>
            </div>
            <Link
                href="/admin/konversi-stok"
                class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </Link>
        </div>

        <!-- Form -->
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Produk Asal -->
                <div>
                    <label for="from_produk_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Produk Asal <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="from_produk_id"
                        v-model="form.from_produk_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        :class="{ 'border-red-500': form.errors.from_produk_id }"
                        required
                    >
                        <option :value="null">Pilih produk asal</option>
                        <option v-for="item in produk" :key="item.id_produk" :value="item.id_produk">
                            {{ item.nama }} ({{ item.sku }}) - {{ item.satuan }} - Stok: {{ item.stok }}
                        </option>
                    </select>
                    <p v-if="form.errors.from_produk_id" class="mt-1 text-sm text-red-500">{{ form.errors.from_produk_id }}</p>
                    <div v-if="selectedFromProduk" class="mt-2 rounded-lg bg-blue-50 p-3">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>{{ selectedFromProduk.nama }}</strong> - Satuan: {{ selectedFromProduk.satuan }}, Stok:
                            {{ selectedFromProduk.stok }}
                            <span v-if="selectedFromProduk.isi_per_pack">, Isi per pack: {{ selectedFromProduk.isi_per_pack }}</span>
                        </p>
                    </div>
                </div>

                <!-- Produk Tujuan -->
                <div>
                    <label for="to_produk_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Produk Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="to_produk_id"
                        v-model="form.to_produk_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        :class="{ 'border-red-500': form.errors.to_produk_id }"
                        required
                    >
                        <option :value="null">Pilih produk tujuan</option>
                        <option
                            v-for="item in produk"
                            :key="item.id_produk"
                            :value="item.id_produk"
                            :disabled="item.id_produk === form.from_produk_id"
                        >
                            {{ item.nama }} ({{ item.sku }}) - {{ item.satuan }} - Stok: {{ item.stok }}
                        </option>
                    </select>
                    <p v-if="form.errors.to_produk_id" class="mt-1 text-sm text-red-500">{{ form.errors.to_produk_id }}</p>
                    <div v-if="selectedToProduk" class="mt-2 rounded-lg bg-blue-50 p-3">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>{{ selectedToProduk.nama }}</strong> - Satuan: {{ selectedToProduk.satuan }}, Stok:
                            {{ selectedToProduk.stok }}
                        </p>
                    </div>
                </div>

                <!-- Konversi Ratio -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Qty From -->
                    <div>
                        <label for="qty_from" class="mb-2 block text-sm font-medium text-gray-700">
                            Jumlah Asal <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_from"
                            v-model.number="form.qty_from"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            :class="{ 'border-red-500': form.errors.qty_from }"
                            required
                        />
                        <p v-if="selectedFromProduk" class="mt-1 text-xs text-gray-500">{{ selectedFromProduk.satuan }}</p>
                        <p v-if="form.errors.qty_from" class="mt-1 text-sm text-red-500">{{ form.errors.qty_from }}</p>
                    </div>

                    <!-- Arrow -->
                    <div class="flex items-center justify-center pt-8">
                        <i class="fas fa-arrow-right text-2xl text-emerald-600"></i>
                    </div>

                    <!-- Qty To -->
                    <div>
                        <label for="qty_to" class="mb-2 block text-sm font-medium text-gray-700">
                            Jumlah Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="qty_to"
                            v-model.number="form.qty_to"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            :class="{ 'border-red-500': form.errors.qty_to }"
                            required
                        />
                        <p v-if="selectedToProduk" class="mt-1 text-xs text-gray-500">{{ selectedToProduk.satuan }}</p>
                        <p v-if="form.errors.qty_to" class="mt-1 text-sm text-red-500">{{ form.errors.qty_to }}</p>
                    </div>
                </div>

                <!-- Rasio (Auto-calculated, read-only) -->
                <div>
                    <label for="rasio" class="mb-2 block text-sm font-medium text-gray-700"> Rasio Konversi </label>
                    <input
                        id="rasio"
                        v-model.number="form.rasio"
                        type="number"
                        readonly
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2"
                    />
                    <p class="mt-1 text-xs text-gray-500">Otomatis terhitung dari jumlah asal dan tujuan</p>
                    <p v-if="form.errors.rasio" class="mt-1 text-sm text-red-500">{{ form.errors.rasio }}</p>
                </div>

                <!-- Preview -->
                <div v-if="selectedFromProduk && selectedToProduk && form.qty_from && form.qty_to" class="rounded-lg bg-emerald-50 p-4">
                    <h4 class="mb-2 font-semibold text-emerald-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Preview Konversi
                    </h4>
                    <p class="text-emerald-700">
                        <strong>{{ form.qty_from }} {{ selectedFromProduk.satuan }}</strong> dari
                        <strong>{{ selectedFromProduk.nama }}</strong>
                        =
                        <strong>{{ form.qty_to }} {{ selectedToProduk.satuan }}</strong> dari
                        <strong>{{ selectedToProduk.nama }}</strong>
                    </p>
                    <p class="mt-1 text-sm text-emerald-600">Rasio: 1 : {{ form.rasio }}</p>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700"> Keterangan (Opsional) </label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="3"
                        maxlength="200"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        placeholder="Contoh: Konversi dari karton ke pcs"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">Maksimal 200 karakter</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <Link
                        href="/admin/konversi-stok"
                        class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 transition-colors hover:bg-gray-100"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-emerald-600 px-6 py-2 text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ form.processing ? 'Menyimpan...' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </BaseLayout>
</template>
