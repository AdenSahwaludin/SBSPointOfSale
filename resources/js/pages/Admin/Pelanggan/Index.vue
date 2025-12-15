<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import { useNotifications } from '@/composables/useNotifications';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    telepon: string | null;
    kota: string | null;
    alamat: string | null;
    aktif: boolean;
    trust_score: number;
    credit_limit: number;
    created_at: string;
}

interface PaginatedData {
    data: Pelanggan[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

interface Props {
    pelanggan: PaginatedData;
    filters: {
        search?: string;
        sort_by: string;
        sort_order: string;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const deleteTarget = ref<Pelanggan | null>(null);

// Menu items dengan active state menggunakan composable
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pelanggan');

// Notification system
const { success, error } = useNotifications();
const page = usePage();

// Watch for flash messages from server
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            success('Berhasil', flash.success);
        }
        if (flash?.error) {
            error('Gagal', flash.error);
        }
    },
    { deep: true, immediate: true },
);

function confirmDelete(pelanggan: Pelanggan) {
    deleteTarget.value = pelanggan;
    showDeleteModal.value = true;
}

function deletePelanggan() {
    if (deleteTarget.value) {
        router.delete(`/admin/pelanggan/${deleteTarget.value.id_pelanggan}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID');
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

function getTrustScoreColor(score: number) {
    if (score >= 80) return 'text-green-700 bg-green-100 border-green-200';
    if (score >= 60) return 'text-yellow-700 bg-yellow-100 border-yellow-200';
    return 'text-red-700 bg-red-100 border-red-200';
}
</script>

<template>
    <Head title="Manajemen Pelanggan - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Manajemen Pelanggan</h1>
                    <p class="text-emerald-600">Kelola data pelanggan dan informasi kontak</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/pelanggan/create')" variant="primary" icon="fas fa-plus"> Tambah Pelanggan </BaseButton>
            </div>

            <!-- Table -->
            <div class="card-emerald overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">ID Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Trust Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Credit Limit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr
                                v-for="item in pelanggan.data"
                                :key="item.id_pelanggan"
                                class="emerald-transition transition-all duration-200 hover:bg-emerald-25"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                                <i class="fas fa-user text-emerald-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-emerald-800">{{ item.id_pelanggan }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ item.nama }}</div>
                                    <div v-if="item.kota" class="text-sm text-emerald-600">{{ item.kota }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="item.email" class="text-sm text-emerald-800">{{ item.email }}</div>
                                    <div v-if="item.telepon" class="text-sm text-emerald-600">{{ item.telepon }}</div>
                                    <div v-if="!item.email && !item.telepon" class="text-sm text-gray-400">-</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getTrustScoreColor(item.trust_score)"
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ item.trust_score }}/100
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ formatCurrency(item.credit_limit) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            item.aktif ? 'border-green-200 bg-green-100 text-green-800' : 'border-red-200 bg-red-100 text-red-800'
                                        "
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/pelanggan/${item.id_pelanggan}`)"
                                            variant="secondary"
                                            icon="fas fa-eye"
                                            custom-class="rounded-lg p-2"
                                            title="Lihat Detail"
                                        />
                                        <BaseButton
                                            @click="$inertia.visit(`/admin/pelanggan/${item.id_pelanggan}/edit`)"
                                            variant="secondary"
                                            icon="fas fa-edit"
                                            custom-class="rounded-lg p-2"
                                            title="Edit"
                                        />
                                        <BaseButton
                                            @click="confirmDelete(item)"
                                            variant="danger"
                                            icon="fas fa-trash"
                                            custom-class="rounded-lg p-2"
                                            title="Hapus"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="pelanggan.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-users mb-4 text-4xl text-emerald-300"></i>
                    <h3 class="mb-2 text-lg font-medium text-emerald-800">Belum ada pelanggan</h3>
                    <p class="mb-4 text-emerald-600">Mulai dengan menambahkan pelanggan pertama</p>
                    <BaseButton @click="$inertia.visit('/admin/pelanggan/create')" variant="primary" icon="fas fa-plus">
                        Tambah Pelanggan
                    </BaseButton>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[2px]">
            <div class="shadow-emerald-xl mx-4 max-w-md rounded-lg bg-white-emerald p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-emerald-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-emerald-600">Apakah Anda yakin ingin menghapus pelanggan ini?</p>
                    </div>
                </div>

                <div v-if="deleteTarget" class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                    <div class="text-sm">
                        <div class="font-medium text-emerald-800">{{ deleteTarget.nama }}</div>
                        <div class="text-emerald-600">ID: {{ deleteTarget.id_pelanggan }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <BaseButton @click="showDeleteModal = false" variant="secondary"> Batal </BaseButton>
                    <BaseButton @click="deletePelanggan" variant="danger"> Hapus </BaseButton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
