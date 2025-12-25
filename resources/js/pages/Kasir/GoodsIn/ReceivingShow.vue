<script setup lang="ts">
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface DetailItem {
    id_goods_in_detail: number;
    id_produk: number;
    nama_produk: string;
    sku: string;
    jumlah_dipesan: number;
    jumlah_diterima: number;
    qty_remaining: number;
}

interface ReceivedGood {
    id_goods_received: number;
    id_goods_in_detail: number;
    jumlah_diterima: number;
    catatan: string | null;
    created_at: string;
    produk: {
        nama: string;
        sku: string;
    };
    kasir?: {
        name?: string;
        nama?: string;
    };
}

interface GoodsInData {
    id_pemesanan_barang: number;
    nomor_po: string;
    status: string;
    kasir?: {
        name?: string;
        nama?: string;
    };
    tanggal_approval: string;
}

const props = defineProps<{
    goodsIn: GoodsInData;
    pendingItems: DetailItem[];
    receivedGoods: ReceivedGood[];
}>();

const page = usePage();
const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/goods-in-receiving');
const showForm = ref(false);
const selectedIds = ref<number[]>([]);
const selectedItems = ref<Record<number, number>>({});
const selectedDamaged = ref<Record<number, number>>({});
const selectedNotes = ref<Record<number, string>>({});

const form = useForm({
    items: [] as Array<{ id_goods_in_detail: number; qty_received: number; qty_damaged?: number; catatan?: string }>,
});

const allItemsReceived = computed(() => {
    return props.pendingItems.every((item) => item.qty_remaining === 0);
});

const itemsWithRemaining = computed(() => props.pendingItems.filter((item) => item.qty_remaining > 0));

// Derived list of items currently selected to render forms
const selectedFormItems = computed(() =>
    itemsWithRemaining.value.filter((item) => selectedIds.value.includes(item.id_goods_in_detail)),
);

const resetSelections = () => {
    selectedIds.value = [];
    selectedItems.value = {};
    selectedDamaged.value = {};
    selectedNotes.value = {};
};

// Ensure qty/damaged/notes maps stay in sync with selected ids
watch(
    selectedIds,
    (newIds, oldIds) => {
        // added ids
        newIds.forEach((id) => {
            if (selectedItems.value[id] === undefined) {
                const item = props.pendingItems.find((i) => i.id_goods_in_detail === id);
                const defaultQty = item?.qty_remaining ?? 1;
                selectedItems.value[id] = defaultQty;
                selectedDamaged.value[id] = 0;
                selectedNotes.value[id] = '';
            }
        });

        // removed ids
        oldIds
            .filter((id) => !newIds.includes(id))
            .forEach((id) => {
                delete selectedItems.value[id];
                delete selectedDamaged.value[id];
                delete selectedNotes.value[id];
            });
    },
    { deep: false },
);

const getSelectedCount = computed(() => selectedIds.value.length);

const submitForm = () => {
    const items = selectedIds.value.map((detailId) => ({
        id_goods_in_detail: detailId,
        qty_received: selectedItems.value[detailId],
        qty_damaged: selectedDamaged.value[detailId] || 0,
        catatan: selectedNotes.value[detailId] || undefined,
    }));

    form.items = items;
    form.post(`/kasir/goods-in/${props.goodsIn.id_pemesanan_barang}/record-received`, {
        preserveScroll: true,
        onSuccess: () => {
            resetSelections();
            showForm.value = false;
        },
    });
};

const goBack = () => {
    router.visit('/kasir/goods-in-receiving');
};
</script>

<template>
    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="min-h-screen bg-gradient-to-br from-emerald-50 to-white p-8">
            <div class="mx-auto max-w-4xl">
                <!-- Back Button & Header -->
                <div class="mb-8 flex items-center gap-4">
                    <button @click="goBack" class="inline-flex items-center gap-2 text-emerald-700 hover:text-emerald-900">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </button>
                    <h1 class="text-3xl font-bold text-emerald-950">{{ goodsIn.nomor_po }}</h1>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-800">
                        Dalam Proses
                    </span>
                </div>

                <!-- Info Card -->
                <div class="mb-8 rounded-lg border border-emerald-200 bg-white p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-emerald-600">Diminta oleh</p>
                            <p class="mt-1 font-semibold text-emerald-950">{{ goodsIn.kasir?.name || goodsIn.kasir?.nama || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-emerald-600">Tanggal Persetujuan</p>
                            <p class="mt-1 font-semibold text-emerald-950">{{ new Date(goodsIn.tanggal_approval).toLocaleDateString('id-ID') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Already Received Items -->
                <div v-if="receivedGoods.length > 0" class="mb-8">
                    <h2 class="mb-4 text-xl font-bold text-emerald-950">Barang yang Sudah Dicatat</h2>
                    <div class="space-y-3 rounded-lg border border-emerald-200 bg-white p-4">
                        <div
                            v-for="received in receivedGoods"
                            :key="received.id_goods_received"
                            class="flex items-center justify-between border-b border-emerald-100 py-3 last:border-0"
                        >
                            <div class="flex-1">
                                <div class="font-semibold text-emerald-950">{{ received.produk.nama }}</div>
                                <div class="text-sm text-emerald-600">SKU: {{ received.produk.sku }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-emerald-950">{{ received.jumlah_diterima }} pcs</div>
                                <div class="text-xs text-emerald-600">{{ new Date(received.created_at).toLocaleDateString('id-ID') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Items -->
                <div v-if="itemsWithRemaining.length > 0" class="mb-8">
                    <h2 class="mb-4 text-xl font-bold text-emerald-950">Barang yang Belum Selesai</h2>

                    <!-- Success Message -->
                    <div
                        v-if="(page.props.flash as any)?.success"
                        class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ (page.props.flash as any).success }}
                    </div>

                    <!-- Error Message -->
                    <div v-if="(page.props.flash as any)?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ (page.props.flash as any).error }}
                    </div>

                    <div v-if="!showForm" class="space-y-3 rounded-lg border border-emerald-200 bg-white p-4">
                        <div
                            v-for="item in itemsWithRemaining"
                            :key="item.id_goods_in_detail"
                            class="flex items-center justify-between border-b border-emerald-100 py-3 last:border-0"
                        >
                            <div class="flex-1">
                                <div class="font-semibold text-emerald-950">{{ item.nama_produk }}</div>
                                <div class="text-sm text-emerald-600">SKU: {{ item.sku }}</div>
                                <div class="mt-1 flex gap-6 text-xs text-emerald-700">
                                    <span
                                        >Diminta: <span class="font-semibold">{{ item.jumlah_dipesan }}</span></span
                                    >
                                    <span
                                        >Diterima: <span class="font-semibold">{{ item.jumlah_diterima }}</span></span
                                    >
                                    <span class="font-semibold text-amber-600">Sisa: {{ item.qty_remaining }}</span>
                                </div>
                            </div>
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="selectedIds"
                                    :value="item.id_goods_in_detail"
                                    class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span class="text-sm text-emerald-700">Pilih</span>
                            </label>
                        </div>
                    </div>

                    <!-- Form View -->
                    <form v-if="showForm" @submit.prevent="submitForm" class="space-y-4 rounded-lg border border-emerald-200 bg-white p-6">
                        <div
                            v-for="item in selectedFormItems"
                            :key="item.id_goods_in_detail"
                            class="space-y-3 border-b border-emerald-100 pb-4 last:border-0"
                        >
                            <div class="font-semibold text-emerald-950">{{ item.nama_produk }}</div>

                            <!-- Quantity Input -->
                            <div>
                                <label class="block text-sm font-medium text-emerald-700">Qty Diterima (Maks: {{ item.qty_remaining }})</label>
                                <input
                                    type="number"
                                    :value="selectedItems[item.id_goods_in_detail]"
                                    @input="
                                        (e) =>
                                            (selectedItems[item.id_goods_in_detail] = Math.min(
                                                Math.max(parseInt((e.target as HTMLInputElement).value) || 1, 1),
                                                item.qty_remaining,
                                            ))
                                    "
                                    :max="item.qty_remaining"
                                    min="1"
                                    class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-emerald-950 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                />
                            </div>

                            <!-- Damaged Quantity Input -->
                            <div>
                                <label class="block text-sm font-medium text-amber-700">
                                    <i class="fas fa-package mr-1"></i>
                                    Qty Rusak/Cacat (Opsional)
                                </label>
                                <input
                                    type="number"
                                    :value="selectedDamaged[item.id_goods_in_detail]"
                                    @input="
                                        (e) =>
                                            (selectedDamaged[item.id_goods_in_detail] = Math.min(
                                                Math.max(parseInt((e.target as HTMLInputElement).value) || 0, 0),
                                                Math.max(selectedItems[item.id_goods_in_detail] - 1, 0),
                                            ))
                                    "
                                    :max="Math.max(selectedItems[item.id_goods_in_detail] - 1, 0)"
                                    min="0"
                                    class="mt-1 w-full rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-amber-950 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                                    placeholder="0"
                                />
                                <p class="mt-1 text-xs text-amber-700">
                                    Barang rusak tidak akan menambah stok. Maks: {{ Math.max(selectedItems[item.id_goods_in_detail] - 1, 0) }}
                                </p>
                            </div>

                            <!-- Notes Input -->
                            <div>
                                <label class="block text-sm font-medium text-emerald-700">Catatan (Opsional)</label>
                                <textarea
                                    :value="selectedNotes[item.id_goods_in_detail]"
                                    @input="(e) => (selectedNotes[item.id_goods_in_detail] = (e.target as HTMLTextAreaElement).value)"
                                    maxlength="500"
                                    rows="2"
                                    class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-emerald-950 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                    placeholder="Contoh: Barang rusak 1 pcs, tidak sesuai ekspektasi, dll."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                :disabled="form.processing || getSelectedCount === 0"
                            >
                                <i class="fas fa-check mr-2"></i>
                                Simpan Penerimaan
                            </button>
                            <button
                                type="button"
                                @click="() => { resetSelections(); showForm = false; }"
                                class="rounded-lg border border-emerald-300 bg-white px-4 py-2 font-semibold text-emerald-700 transition hover:bg-emerald-50 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:outline-none"
                            >
                                Batal
                            </button>
                        </div>
                    </form>

                    <!-- Show Form Button -->
                    <button
                        v-if="!showForm && itemsWithRemaining.length > 0"
                        @click="showForm = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <i class="fas fa-plus"></i>
                        Catat Barang Diterima
                    </button>
                </div>

                <!-- All Received State -->
                <div v-if="allItemsReceived" class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 text-center">
                    <i class="fas fa-check-circle text-4xl text-emerald-600"></i>
                    <h3 class="mt-2 text-lg font-semibold text-emerald-950">Semua Barang Sudah Diterima</h3>
                    <p class="mt-1 text-emerald-700">PO ini telah selesai dengan semua barang tercatat.</p>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
