<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import * as goodsInRoutes from '@/routes/kasir/goods-in';
import * as itemsRoutes from '@/routes/kasir/goods-in/items';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Produk {
    id_produk: number;
    sku: string;
    nama: string;
    satuan: string;
    stok: number;
    rop?: number;
}

interface GoodsInDetail {
    id_goods_in_detail: number;
    id_produk: number;
    jumlah_dipesan: number;
    qty_received: number;
    produk: Produk;
}

interface Kasir {
    id_pengguna: string;
    nama: string;
    email: string;
}

interface Admin {
    id_pengguna: string;
    nama: string;
    email: string;
}

interface GoodsIn {
    id_pemesanan_barang: number;
    nomor_po: string;
    status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'received';
    tanggal_request: string;
    tanggal_approval?: string;
    catatan_approval?: string;
    id_kasir: string;
    id_admin?: string;
    details: GoodsInDetail[];
    kasir: Kasir;
    admin?: Admin;
}

interface Props {
    goodsIn: GoodsIn;
    availableProducts: Produk[];
}

const props = defineProps<Props>();
const page = usePage();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in');
const selectedProductId = ref<number | null>(null);
const selectedQty = ref<number>(1);

const addForm = useForm({
    id_produk: null as number | null,
    jumlah_dipesan: 1,
});

const selectedProduct = computed(() => {
    if (!selectedProductId.value) return null;
    return props.availableProducts.find((p) => p.id_produk === selectedProductId.value);
});

const canAddItem = computed(() => {
    return selectedProductId.value && selectedQty.value > 0;
});

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusBadgeClass(status: string) {
    const classes = {
        draft: 'bg-gray-100 text-gray-700 border-gray-200',
        submitted: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        approved: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        rejected: 'bg-red-100 text-red-700 border-red-200',
        received: 'bg-blue-100 text-blue-700 border-blue-200',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700 border-gray-200';
}

function getStatusLabel(status: string) {
    const labels = {
        draft: 'Draft',
        submitted: 'Menunggu Persetujuan',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        received: 'Diterima',
    };
    return labels[status as keyof typeof labels] || status;
}

function getStatusIcon(status: string) {
    const icons = {
        draft: 'fas fa-file-edit',
        submitted: 'fas fa-clock',
        approved: 'fas fa-check-circle',
        rejected: 'fas fa-times-circle',
        received: 'fas fa-box-open',
    };
    return icons[status as keyof typeof icons] || 'fas fa-question-circle';
}

function getTotalItems() {
    return props.goodsIn.details.reduce((sum, detail) => sum + detail.jumlah_dipesan, 0);
}

function addItem() {
    if (!selectedProductId.value || selectedQty.value <= 0) return;

    addForm.id_produk = selectedProductId.value;
    addForm.jumlah_dipesan = selectedQty.value;

    addForm.post(itemsRoutes.add({ goodsIn: props.goodsIn.id_pemesanan_barang }).url, {
        onSuccess: () => {
            selectedProductId.value = null;
            selectedQty.value = 1;
            addForm.reset();
        },
    });
}

function removeItem(detailId: number) {
    if (!confirm('Yakin ingin menghapus item ini dari PO?')) return;

    const form = useForm({});
    form.delete(itemsRoutes.remove([{ goodsIn: props.goodsIn.id_pemesanan_barang }, detailId]).url);
}

function submitPO() {
    if (!confirm('Yakin ingin mengajukan PO ini untuk persetujuan? PO tidak dapat diubah setelah diajukan.')) return;

    const form = useForm({});
    form.post(goodsInRoutes.submit({ id_pemesanan_barang: props.goodsIn.id_pemesanan_barang }).url);
}
</script>

<template>
    <Head :title="`Detail PO ${goodsIn.nomor_po} - Kasir`" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Detail Purchase Order</h1>
                    <p class="text-emerald-600">{{ goodsIn.nomor_po }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/goods-in')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Error Messages -->
            <div v-if="page.props.errors && Object.keys(page.props.errors).length > 0" class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-xl text-red-600"></i>
                    <div class="text-sm text-red-700">
                        <p class="font-medium">Terjadi Kesalahan</p>
                        <ul class="mt-2 space-y-1">
                            <li v-for="(error, key) in page.props.errors" :key="key" class="text-sm">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Success Messages -->
            <div v-if="(page.props.flash as any)?.success" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle text-xl text-emerald-600"></i>
                    <div class="text-sm text-emerald-700">
                        <p class="font-medium">Berhasil</p>
                        <p class="mt-1">{{ (page.props.flash as any)?.success }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card-emerald">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div :class="getStatusBadgeClass(goodsIn.status)" class="flex h-16 w-16 items-center justify-center rounded-full border-2">
                            <i :class="getStatusIcon(goodsIn.status)" class="text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-emerald-800">{{ goodsIn.nomor_po }}</h2>
                            <p
                                :class="getStatusBadgeClass(goodsIn.status)"
                                class="mt-1 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium"
                            >
                                <i :class="getStatusIcon(goodsIn.status)"></i>
                                {{ getStatusLabel(goodsIn.status) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- PO Information -->
                <div class="mt-6 grid grid-cols-1 gap-4 border-t border-emerald-100 pt-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-emerald-50 p-3">
                        <div class="text-xs tracking-wider text-emerald-600 uppercase">Tanggal Request</div>
                        <div class="mt-1 text-sm font-medium text-emerald-800">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ formatDate(goodsIn.tanggal_request) }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-emerald-50 p-3">
                        <div class="text-xs tracking-wider text-emerald-600 uppercase">Dibuat Oleh</div>
                        <div class="mt-1 text-sm font-medium text-emerald-800">
                            <i class="fas fa-user mr-1"></i>
                            {{ goodsIn.kasir.nama }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-emerald-50 p-3">
                        <div class="text-xs tracking-wider text-emerald-600 uppercase">Total Item</div>
                        <div class="mt-1 text-sm font-medium text-emerald-800">
                            <i class="fas fa-boxes mr-1"></i>
                            {{ goodsIn.details.length }} produk ({{ getTotalItems() }} qty)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Info (if approved/rejected) -->
            <div v-if="goodsIn.status === 'approved' || goodsIn.status === 'rejected'" class="card-emerald">
                <div class="mb-4 flex items-center gap-2">
                    <i :class="goodsIn.status === 'approved' ? 'fas fa-check-circle text-emerald-600' : 'fas fa-times-circle text-red-600'"></i>
                    <h3 class="font-semibold text-emerald-800">Informasi {{ goodsIn.status === 'approved' ? 'Persetujuan' : 'Penolakan' }}</h3>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-32 text-sm text-emerald-600">Tanggal:</div>
                        <div class="text-sm text-emerald-800">{{ goodsIn.tanggal_approval ? formatDate(goodsIn.tanggal_approval) : '-' }}</div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-32 text-sm text-emerald-600">{{ goodsIn.status === 'approved' ? 'Disetujui' : 'Ditolak' }} Oleh:</div>
                        <div class="text-sm text-emerald-800">{{ goodsIn.admin?.nama || '-' }}</div>
                    </div>

                    <div v-if="goodsIn.catatan_approval" class="flex items-start gap-3">
                        <div class="w-32 text-sm text-emerald-600">Catatan:</div>
                        <div class="text-sm text-emerald-800">{{ goodsIn.catatan_approval }}</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card-emerald">
                <h3 class="mb-4 font-semibold text-emerald-800">Detail Item PO</h3>

                <div v-if="goodsIn.details.length === 0" class="rounded-lg border border-dashed border-emerald-200 bg-emerald-50 p-8 text-center">
                    <i class="fas fa-inbox mb-3 text-4xl text-emerald-300"></i>
                    <p class="mb-2 text-sm font-medium text-emerald-800">Belum ada item di PO ini</p>
                    <p class="text-sm text-emerald-600">Tambahkan item untuk melanjutkan</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-emerald-200 bg-emerald-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Nama Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Stok</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Qty Request</th>
                                <th
                                    v-if="goodsIn.status === 'received'"
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase"
                                >
                                    Qty Diterima
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-emerald-600 uppercase">Status</th>
                                <th
                                    v-if="goodsIn.status === 'draft'"
                                    class="px-4 py-3 text-center text-xs font-medium tracking-wider text-emerald-600 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100">
                            <tr
                                v-for="(detail, index) in goodsIn.details"
                                :key="detail.id_goods_in_detail"
                                class="transition-colors hover:bg-emerald-50"
                            >
                                <td class="px-4 py-3 text-sm whitespace-nowrap text-emerald-600">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap text-emerald-600">{{ detail.produk.sku }}</td>
                                <td class="px-4 py-3 text-sm text-emerald-800">{{ detail.produk.nama }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap text-emerald-700">
                                    <span class="font-medium">{{ detail.produk.stok }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap text-emerald-700">
                                    <span class="font-medium">{{ detail.jumlah_dipesan }}</span> {{ detail.produk.satuan }}
                                </td>
                                <td v-if="goodsIn.status === 'received'" class="px-4 py-3 text-sm whitespace-nowrap text-emerald-700">
                                    <span class="font-medium">{{ detail.qty_received }}</span> {{ detail.produk.satuan }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        v-if="goodsIn.status === 'received' && detail.qty_received >= detail.jumlah_dipesan"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700"
                                    >
                                        <i class="fas fa-check"></i>
                                        Lengkap
                                    </span>
                                    <span
                                        v-else-if="goodsIn.status === 'received' && detail.qty_received > 0"
                                        class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700"
                                    >
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Sebagian
                                    </span>
                                    <span
                                        v-else-if="goodsIn.status === 'received'"
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700"
                                    >
                                        <i class="fas fa-minus"></i>
                                        Belum
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700"
                                    >
                                        <i class="fas fa-info-circle"></i>
                                        Dalam Proses
                                    </span>
                                </td>
                                <td v-if="goodsIn.status === 'draft'" class="px-4 py-3 text-center whitespace-nowrap">
                                    <button
                                        @click="removeItem(detail.id_goods_in_detail)"
                                        class="text-red-600 transition-colors hover:text-red-700"
                                        title="Hapus item"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t border-emerald-200 bg-emerald-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-emerald-800">Total:</td>
                                <td colspan="2" class="px-4 py-3 text-sm font-semibold text-emerald-800">{{ getTotalItems() }} items</td>
                                <td v-if="goodsIn.status === 'received'" colspan="2"></td>
                                <td v-if="goodsIn.status === 'draft'"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Add Item Form (only if draft) -->
            <div v-if="goodsIn.status === 'draft'" class="card-emerald">
                <h3 class="mb-4 font-semibold text-emerald-800">Tambah Item</h3>

                <div v-if="availableProducts.length === 0" class="rounded-lg border border-dashed border-emerald-200 bg-emerald-50 p-6 text-center">
                    <i class="fas fa-check-circle mb-2 text-3xl text-emerald-600"></i>
                    <p class="text-sm font-medium text-emerald-800">Semua produk aktif sudah ada di PO ini</p>
                </div>

                <form v-else @submit.prevent="addItem" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Product Select -->
                        <div>
                            <label for="product-select" class="mb-2 block text-sm font-medium text-emerald-700">
                                <i class="fas fa-box mr-1"></i>
                                Pilih Produk
                            </label>
                            <select
                                id="product-select"
                                v-model.number="selectedProductId"
                                class="w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            >
                                <option :value="null" disabled>-- Pilih Produk --</option>
                                <option v-for="product in availableProducts" :key="product.id_produk" :value="product.id_produk">
                                    {{ product.sku }} - {{ product.nama }}
                                </option>
                            </select>
                            <div v-if="(page.props.errors as any)?.id_produk" class="mt-1 text-sm text-red-600">
                                {{ (page.props.errors as any)?.id_produk }}
                            </div>
                        </div>

                        <!-- Qty Input -->
                        <div>
                            <label for="qty-input" class="mb-2 block text-sm font-medium text-emerald-700">
                                <i class="fas fa-calculator mr-1"></i>
                                Kuantitas
                            </label>
                            <input
                                id="qty-input"
                                v-model.number="selectedQty"
                                type="number"
                                min="1"
                                max="9999"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="Masukkan kuantitas"
                            />
                            <div v-if="(page.props.errors as any)?.jumlah_dipesan" class="mt-1 text-sm text-red-600">
                                {{ (page.props.errors as any)?.jumlah_dipesan }}
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div v-if="selectedProduct" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                            <div>
                                <div class="text-xs text-emerald-600">SKU</div>
                                <div class="mt-1 font-medium text-emerald-800">{{ selectedProduct.sku }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-emerald-600">Stok Saat Ini</div>
                                <div class="mt-1 font-medium text-emerald-800">{{ selectedProduct.stok }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-emerald-600">Satuan</div>
                                <div class="mt-1 font-medium text-emerald-800">{{ selectedProduct.satuan }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-2 pt-4">
                        <BaseButton @click="addItem" :disabled="!canAddItem || addForm.processing" type="button" variant="primary" icon="fas fa-plus">
                            {{ addForm.processing ? 'Menambahkan...' : 'Tambah Item' }}
                        </BaseButton>
                    </div>
                </form>
            </div>

            <!-- Info Messages -->
            <div v-if="goodsIn.status === 'draft'" class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-xl text-blue-600"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium">Mode Draft</p>
                        <p class="mt-1">
                            PO dalam status draft. Anda dapat menambah, mengubah, atau menghapus item. Setelah selesai, klik tombol "Ajukan untuk
                            Persetujuan".
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="goodsIn.status === 'submitted'" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-xl text-yellow-600"></i>
                    <div class="text-sm text-yellow-700">
                        <p class="font-medium">Menunggu Persetujuan Admin</p>
                        <p class="mt-1">
                            PO ini sedang menunggu persetujuan dari Admin. Anda akan menerima notifikasi setelah PO disetujui atau ditolak.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="goodsIn.status === 'approved'" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle text-xl text-emerald-600"></i>
                    <div class="text-sm text-emerald-700">
                        <p class="font-medium">PO Telah Disetujui</p>
                        <p class="mt-1">Purchase Order ini telah disetujui dan menunggu proses penerimaan barang oleh Admin.</p>
                    </div>
                </div>
            </div>

            <div v-if="goodsIn.status === 'rejected'" class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-times-circle text-xl text-red-600"></i>
                    <div class="text-sm text-red-700">
                        <p class="font-medium">PO Ditolak</p>
                        <p class="mt-1">Purchase Order ini telah ditolak. Silakan buat PO baru jika masih diperlukan.</p>
                    </div>
                </div>
            </div>

            <div v-if="goodsIn.status === 'received'" class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-box-open text-xl text-blue-600"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium">Barang Telah Diterima</p>
                        <p class="mt-1">Purchase Order ini telah selesai. Barang sudah diterima dan stok sudah diperbarui.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button (only if draft and has items) -->
            <div v-if="goodsIn.status === 'draft' && goodsIn.details.length > 0" class="flex gap-2">
                <BaseButton @click="submitPO" variant="primary" icon="fas fa-paper-plane"> Ajukan untuk Persetujuan </BaseButton>
            </div>
        </div>
    </BaseLayout>
</template>
