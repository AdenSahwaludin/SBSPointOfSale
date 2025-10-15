<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Produk {
    id_produk: number;
    nama: string;
    sku: string;
    satuan: string;
}

interface KonversiStok {
    id_konversi: number;
    from_produk_id: number;
    to_produk_id: number;
    rasio: number;
    qty_from: number;
    qty_to: number;
    keterangan: string | null;
    created_at: string;
    updated_at: string;
    from_produk: Produk;
    to_produk: Produk;
}

interface Props {
    konversiStok: {
        data: KonversiStok[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

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

const searchQuery = ref(props.filters.search || '');
const selectedItems = ref<number[]>([]);
const showDeleteModal = ref(false);
const itemToDelete = ref<number | null>(null);

const allSelected = computed(() => {
    return props.konversiStok.data.length > 0 && selectedItems.value.length === props.konversiStok.data.length;
});

function toggleSelectAll() {
    if (allSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.konversiStok.data.map((item) => item.id_konversi);
    }
}

function handleSearch() {
    router.get(
        '/admin/konversi-stok',
        { search: searchQuery.value },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function sortBy(field: string) {
    const direction = props.filters.sort_field === field && props.filters.sort_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        '/admin/konversi-stok',
        {
            ...props.filters,
            sort_field: field,
            sort_direction: direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function confirmDelete(id: number) {
    itemToDelete.value = id;
    showDeleteModal.value = true;
}

function deleteItem() {
    if (itemToDelete.value) {
        router.delete(`/admin/konversi-stok/${itemToDelete.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            },
        });
    }
}

function bulkDelete() {
    if (selectedItems.value.length === 0) return;

    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItems.value.length} konversi stok?`)) {
        router.post(
            '/admin/konversi-stok/bulk-delete',
            { ids: selectedItems.value },
            {
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                },
            },
        );
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <BaseLayout title="Konversi Stok - Sari Bumi Sakti" :menuItems="adminMenuItems" userRole="admin">
        <template #header> Konversi Stok </template>

        <Head title="Konversi Stok" />

        <!-- Header Section -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-emerald-800">Konversi Stok</h2>
                <p class="mt-1 text-sm text-gray-600">Kelola konversi antar satuan produk</p>
            </div>
            <Link
                href="/admin/konversi-stok/create"
                class="flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white transition-colors hover:bg-emerald-700"
            >
                <i class="fas fa-plus mr-2"></i>
                Tambah Konversi
            </Link>
        </div>

        <!-- Search & Filter Bar -->
        <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari produk atau keterangan..."
                            class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            @keyup.enter="handleSearch"
                        />
                        <i class="fas fa-search absolute top-1/2 left-3 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button @click="handleSearch" class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition-colors hover:bg-emerald-700">
                        <i class="fas fa-search mr-2"></i>
                        Cari
                    </button>
                    <button
                        v-if="selectedItems.length > 0"
                        @click="bulkDelete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700"
                    >
                        <i class="fas fa-trash mr-2"></i>
                        Hapus ({{ selectedItems.length }})
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                            </th>
                            <th
                                class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase"
                                @click="sortBy('from_produk_id')"
                            >
                                Produk Asal
                                <i
                                    v-if="filters.sort_field === 'from_produk_id'"
                                    :class="filters.sort_direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
                                    class="fas ml-1"
                                ></i>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-700 uppercase">Konversi</th>
                            <th
                                class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase"
                                @click="sortBy('to_produk_id')"
                            >
                                Produk Tujuan
                                <i
                                    v-if="filters.sort_field === 'to_produk_id'"
                                    :class="filters.sort_direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
                                    class="fas ml-1"
                                ></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase">Keterangan</th>
                            <th
                                class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase"
                                @click="sortBy('created_at')"
                            >
                                Dibuat
                                <i
                                    v-if="filters.sort_field === 'created_at'"
                                    :class="filters.sort_direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
                                    class="fas ml-1"
                                ></i>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="konversiStok.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox mb-2 text-4xl text-gray-300"></i>
                                <p>Tidak ada data konversi stok</p>
                            </td>
                        </tr>
                        <tr v-for="item in konversiStok.data" :key="item.id_konversi" class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    :value="item.id_konversi"
                                    v-model="selectedItems"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ item.from_produk.nama }}</span>
                                    <span class="text-sm text-gray-500">{{ item.from_produk.sku }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <div class="rounded-lg bg-emerald-50 px-3 py-1 text-center">
                                        <span class="font-semibold text-emerald-700">{{ item.qty_from }} {{ item.from_produk.satuan }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-emerald-600"></i>
                                        <span class="font-semibold text-emerald-700">{{ item.qty_to }} {{ item.to_produk.satuan }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ item.to_produk.nama }}</span>
                                    <span class="text-sm text-gray-500">{{ item.to_produk.sku }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ item.keterangan || '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ formatDate(item.created_at) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/konversi-stok/${item.id_konversi}/edit`"
                                        class="rounded-lg bg-blue-600 px-3 py-1 text-sm text-white transition-colors hover:bg-blue-700"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </Link>
                                    <button
                                        @click="confirmDelete(item.id_konversi)"
                                        class="rounded-lg bg-red-600 px-3 py-1 text-sm text-white transition-colors hover:bg-red-700"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="konversiStok.data.length > 0" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">{{ konversiStok.from }}</span> sampai
                        <span class="font-medium">{{ konversiStok.to }}</span> dari <span class="font-medium">{{ konversiStok.total }}</span> hasil
                    </div>
                    <div class="flex gap-1">
                        <template v-for="(link, index) in konversiStok.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="{
                                    'bg-emerald-600 text-white': link.active,
                                    'bg-white text-gray-700 hover:bg-gray-100': !link.active,
                                }"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-sm transition-colors"
                                preserve-scroll
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 py-1 text-sm text-gray-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDeleteModal"
                class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
                @click.self="showDeleteModal = false"
            >
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                    <p class="mb-6 text-gray-600">Apakah Anda yakin ingin menghapus konversi stok ini? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showDeleteModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button @click="deleteItem" class="rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </BaseLayout>
</template>
