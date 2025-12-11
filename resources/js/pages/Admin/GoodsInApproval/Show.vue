<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Kasir {
    id_pengguna: number;
    name: string;
}

interface Admin {
    id_pengguna: number;
    name: string;
}

interface ProdukDetail {
    id_produk: string;
    nama: string;
    satuan: string;
}

interface GoodsInDetail {
    id_goods_in_detail: number;
    id_goods_in: number;
    id_produk: string;
    qty_request: number;
    produk: ProdukDetail;
}

interface GoodsIn {
    id_goods_in: number;
    nomor_po: string;
    status: string;
    tanggal_request: string;
    tanggal_approval: string | null;
    catatan_approval: string | null;
    id_kasir: number;
    id_admin: number | null;
    kasir: Kasir;
    admin: Admin | null;
    details: GoodsInDetail[];
}

interface Props {
    goodsIn: GoodsIn;
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/goods-in-approval');

const approveForm = useForm({
    catatan: '',
});

const rejectForm = useForm({
    catatan: '',
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getTotalItems() {
    return props.goodsIn.details.reduce((sum, detail) => sum + detail.qty_request, 0);
}

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'submitted':
            return 'border-yellow-200 bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'border-green-200 bg-green-100 text-green-800';
        case 'rejected':
            return 'border-red-200 bg-red-100 text-red-800';
        default:
            return 'border-gray-200 bg-gray-100 text-gray-800';
    }
}

function getStatusIcon(status: string) {
    switch (status) {
        case 'submitted':
            return 'fas fa-clock';
        case 'approved':
            return 'fas fa-check-circle';
        case 'rejected':
            return 'fas fa-times-circle';
        default:
            return 'fas fa-question-circle';
    }
}

function getStatusLabel(status: string) {
    switch (status) {
        case 'submitted':
            return 'Pending';
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        default:
            return status;
    }
}

function openApproveModal() {
    showApproveModal.value = true;
}

function openRejectModal() {
    showRejectModal.value = true;
}

function handleApprove() {
    approveForm.post(`/admin/goods-in-approval/${props.goodsIn.id_goods_in}/approve`, {
        onSuccess: () => {
            showApproveModal.value = false;
        },
    });
}

function handleReject() {
    rejectForm.post(`/admin/goods-in-approval/${props.goodsIn.id_goods_in}/reject`, {
        onSuccess: () => {
            showRejectModal.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Detail Purchase Order ${goodsIn.nomor_po} - Admin`" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Purchase Order</h1>
                    <p class="text-emerald-600">{{ goodsIn.nomor_po }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/goods-in-approval')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- PO Information -->
            <div class="card-emerald">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi PO</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Nomor PO</label>
                        <div class="flex items-center">
                            <div class="mr-3 h-10 w-10 flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                    <i class="fas fa-file-invoice text-emerald-600"></i>
                                </div>
                            </div>
                            <div class="text-lg font-medium text-emerald-800">{{ goodsIn.nomor_po }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Status</label>
                        <span
                            :class="getStatusBadgeClass(goodsIn.status)"
                            class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold"
                        >
                            <i :class="getStatusIcon(goodsIn.status)" class="mr-1.5"></i>
                            {{ getStatusLabel(goodsIn.status) }}
                        </span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Kasir</label>
                        <div class="text-emerald-800">
                            <div class="font-medium">{{ goodsIn.kasir.name }}</div>
                            <div class="text-sm text-emerald-600">ID: {{ goodsIn.kasir.id_pengguna }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Tanggal Request</label>
                        <div class="text-emerald-800">{{ formatDate(goodsIn.tanggal_request) }}</div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Total Items</label>
                        <div class="text-emerald-800">
                            <span class="font-medium">{{ goodsIn.details.length }}</span> item{{ goodsIn.details.length > 1 ? 's' : '' }} ({{
                                getTotalItems()
                            }}
                            qty)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card-emerald">
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Daftar Item</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">ID Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Satuan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-emerald-600 uppercase">Qty Request</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 bg-white-emerald">
                            <tr v-for="(detail, index) in goodsIn.details" :key="detail.id_goods_in_detail" class="hover:bg-emerald-25">
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-emerald-800">{{ index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ detail.id_produk }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-emerald-800">{{ detail.produk.nama }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-emerald-800">{{ detail.produk.satuan }}</div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="text-sm font-medium text-emerald-800">{{ detail.qty_request }}</div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t-2 border-emerald-300 bg-emerald-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-semibold text-emerald-800">Total Quantity:</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-emerald-800">{{ getTotalItems() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Approval Info (if approved/rejected) -->
            <div
                v-if="goodsIn.status !== 'submitted'"
                class="card-emerald border-l-4 bg-emerald-50"
                :class="goodsIn.status === 'approved' ? 'border-green-500' : 'border-red-500'"
            >
                <h3 class="mb-4 text-lg font-semibold text-emerald-800">Informasi Persetujuan</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Admin</label>
                        <div class="text-emerald-800">
                            <div class="font-medium">{{ goodsIn.admin?.name || '-' }}</div>
                            <div v-if="goodsIn.admin" class="text-sm text-emerald-600">ID: {{ goodsIn.admin.id_pengguna }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Tanggal Persetujuan</label>
                        <div class="text-emerald-800">{{ goodsIn.tanggal_approval ? formatDate(goodsIn.tanggal_approval) : '-' }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-emerald-700">Catatan</label>
                        <div class="rounded-lg bg-white p-3 text-emerald-800">
                            {{ goodsIn.catatan_approval || 'Tidak ada catatan' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons (if pending) -->
            <div v-if="goodsIn.status === 'submitted'" class="flex justify-end gap-3">
                <BaseButton @click="openRejectModal" variant="danger" icon="fas fa-times-circle"> Tolak PO </BaseButton>
                <BaseButton @click="openApproveModal" variant="primary" icon="fas fa-check-circle"> Setujui PO </BaseButton>
            </div>
        </div>

        <!-- Approve Modal -->
        <Teleport to="body">
            <div
                v-if="showApproveModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="showApproveModal = false"
            >
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <i class="fas fa-check-circle text-xl text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Setujui Purchase Order</h3>
                            <p class="text-sm text-gray-600">{{ goodsIn.nomor_po }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="handleApprove">
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Catatan
                                <span class="text-gray-400">(opsional)</span>
                            </label>
                            <textarea
                                v-model="approveForm.catatan"
                                rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="Tambahkan catatan jika diperlukan..."
                            ></textarea>
                            <div v-if="approveForm.errors.catatan" class="mt-1 text-sm text-red-600">
                                {{ approveForm.errors.catatan }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <BaseButton type="button" @click="showApproveModal = false" variant="secondary" :disabled="approveForm.processing">
                                Batal
                            </BaseButton>
                            <BaseButton type="submit" variant="primary" icon="fas fa-check-circle" :disabled="approveForm.processing">
                                {{ approveForm.processing ? 'Memproses...' : 'Setujui' }}
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Reject Modal -->
        <Teleport to="body">
            <div
                v-if="showRejectModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="showRejectModal = false"
            >
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <i class="fas fa-times-circle text-xl text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Tolak Purchase Order</h3>
                            <p class="text-sm text-gray-600">{{ goodsIn.nomor_po }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="handleReject">
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Catatan
                                <span class="text-gray-400">(opsional)</span>
                            </label>
                            <textarea
                                v-model="rejectForm.catatan"
                                rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none"
                                placeholder="Berikan alasan penolakan..."
                            ></textarea>
                            <div v-if="rejectForm.errors.catatan" class="mt-1 text-sm text-red-600">
                                {{ rejectForm.errors.catatan }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <BaseButton type="button" @click="showRejectModal = false" variant="secondary" :disabled="rejectForm.processing">
                                Batal
                            </BaseButton>
                            <BaseButton type="submit" variant="danger" icon="fas fa-times-circle" :disabled="rejectForm.processing">
                                {{ rejectForm.processing ? 'Memproses...' : 'Tolak' }}
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </BaseLayout>
</template>
