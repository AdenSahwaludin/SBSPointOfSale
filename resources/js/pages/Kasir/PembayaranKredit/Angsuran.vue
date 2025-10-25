<script lang="ts" setup>
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    contracts?: {
        data: Array<{
            id_kontrak: number;
            nomor_kontrak: string;
            mulai_kontrak: string;
            tenor_bulan: number;
            cicilan_bulanan: number;
            status: string;
            pelanggan: { id_pelanggan: string; nama: string };
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters?: { search?: string; due_this_month?: string; unpaid_only?: string };
    selected?: any;
    summary?: any;
}>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/angsuran');
const { formatCurrency } = useCurrencyFormat();

const search = ref(props.filters?.search || '');
const dueThisMonth = ref(props.filters?.due_this_month === '1');
const payAmount = ref<number>(0);
const payMethod = ref<'TUNAI' | 'QRIS' | 'TRANSFER BCA'>('TUNAI');
const note = ref('');

// Determine if detail view is active (selected contract present)
const isDetailActive = computed(() => !!props.selected);

// Auto-apply filter when component mounts (only on list view)
onMounted(() => {
    if (!isDetailActive.value) applyFilter();
});

// Auto-apply filter when search text or checkbox changes (debounced on list view)
let filterTimer: number | null = null;

watch([search, dueThisMonth], () => {
    // Hanya auto-filter di list view, tidak di detail view
    if (isDetailActive.value) return;

    if (filterTimer) {
        clearTimeout(filterTimer);
    }
    filterTimer = window.setTimeout(() => {
        applyFilter();
    }, 300); // 300ms debounce
});

function applyFilter() {
    const params = new URLSearchParams();
    const q = search.value?.trim();
    if (q) params.set('search', q);
    params.set('due_this_month', dueThisMonth.value ? '1' : '0');
    const url = params.toString() ? `/kasir/angsuran?${params.toString()}` : `/kasir/angsuran`;
    router.get(url, {}, { replace: true, preserveState: true });
}

function openContract(id: number) {
    router.get(`/kasir/angsuran/${id}`);
}

const schedule = computed(() =>
    (props.selected?.jadwal_angsurans || props.selected?.jadwal_angsuran || []).map((j: any) => ({
        ...j,
        sisa: Number(j.jumlah_tagihan || 0) - Number(j.jumlah_dibayar || 0),
    })),
);

const totalDueThisMonth = computed(() => {
    const now = new Date();
    const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    return schedule.value
        .filter((s: any) => s.status !== 'PAID' && String(s.jatuh_tempo).startsWith(ym))
        .reduce((sum: number, it: any) => sum + Math.max(0, it.sisa), 0);
});

function quickPay(months: number | 'all') {
    const notPaid = schedule.value.filter((s: any) => s.status !== 'PAID');
    const list = months === 'all' ? notPaid : notPaid.slice(0, Number(months));
    const sum = list.reduce((sum: number, it: any) => sum + Math.max(0, it.sisa), 0);
    payAmount.value = sum;
}

function submitPayment() {
    if (!props.selected) return;
    if (!payAmount.value || payAmount.value <= 0) return;
    router.post(
        `/kasir/angsuran/${props.selected.id_kontrak}/pay`,
        {
            jumlah: payAmount.value,
            metode: payMethod.value,
            keterangan: note.value,
        },
        { preserveScroll: true },
    );
}
</script>

<template>
    <BaseLayout title="Angsuran Kredit - Kasir" :menuItems="kasirMenuItems" userRole="kasir">
        <Head title="Angsuran Kredit" />

        <div class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Angsuran Kredit</h1>
                    <p class="text-sm text-gray-600">Kelola pembayaran angsuran kontrak kredit</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-sm"
                        ><input type="checkbox" v-model="dueThisMonth" /> Tampilkan yang jatuh tempo bulan ini</label
                    >
                    <input
                        v-model="search"
                        placeholder="Cari kontrak/pelanggan..."
                        class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Contracts list -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-1">
                    <div class="border-b border-gray-200 p-4 text-sm font-semibold text-gray-700">Kontrak</div>
                    <div class="divide-y divide-gray-100">
                        <div v-if="!contracts || contracts.data.length === 0" class="p-6 text-center text-gray-500">Tidak ada kontrak ditemukan</div>
                        <button
                            v-for="c in contracts?.data || []"
                            :key="c.id_kontrak"
                            @click="openContract(c.id_kontrak)"
                            class="block w-full p-4 text-left hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ c.nomor_kontrak }}</div>
                                    <div class="text-xs text-gray-600">{{ c.pelanggan?.nama }}</div>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-xs',
                                        c.status === 'AKTIF' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700',
                                    ]"
                                    >{{ c.status }}</span
                                >
                            </div>
                            <div class="mt-1 text-xs text-gray-500">Mulai: {{ c.mulai_kontrak }} • Tenor: {{ c.tenor_bulan }} bln</div>
                        </button>
                    </div>
                </div>

                <!-- Detail + payment -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">Detail Kontrak</div>
                                <div v-if="selected" class="text-sm text-gray-600">
                                    {{ selected?.nomor_kontrak }} • {{ selected?.pelanggan?.nama }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-3" v-if="selected">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Pokok Pinjaman</div>
                            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(summary?.pokok_pinjaman || 0) }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Cicilan/Bulan</div>
                            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(selected?.cicilan_bulanan || 0) }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Tunggakan Bulan Ini</div>
                            <div class="text-lg font-bold text-red-600">{{ formatCurrency(totalDueThisMonth) }}</div>
                        </div>
                    </div>

                    <div class="p-4" v-if="selected">
                        <div class="mb-2 text-sm font-semibold text-gray-900">Jadwal Angsuran</div>
                        <div class="max-h-72 overflow-y-auto">
                            <div class="grid grid-cols-5 border-b bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                                <div>Periode</div>
                                <div>Jatuh Tempo</div>
                                <div class="text-right">Tagihan</div>
                                <div class="text-right">Dibayar</div>
                                <div class="text-right">Sisa</div>
                            </div>
                            <div v-for="row in schedule" :key="row.id_angsuran" class="grid grid-cols-5 px-3 py-2 text-sm">
                                <div>#{{ row.periode_ke }}</div>
                                <div>{{ row.jatuh_tempo }}</div>
                                <div class="text-right">{{ formatCurrency(row.jumlah_tagihan) }}</div>
                                <div class="text-right">{{ formatCurrency(row.jumlah_dibayar) }}</div>
                                <div class="text-right" :class="row.status !== 'PAID' ? 'font-semibold text-red-600' : 'text-gray-600'">
                                    {{ formatCurrency(row.sisa) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-gray-200 p-3">
                                <div class="mb-2 text-sm font-semibold text-gray-900">Pembayaran</div>
                                <div class="space-y-2">
                                    <div>
                                        <label class="text-xs text-gray-600">Metode</label>
                                        <select
                                            v-model="payMethod"
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                                        >
                                            <option value="TUNAI">Tunai</option>
                                            <option value="QRIS">QRIS</option>
                                            <option value="TRANSFER BCA">Transfer BCA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-600">Jumlah</label>
                                        <input
                                            type="number"
                                            min="0"
                                            v-model.number="payAmount"
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-600">Keterangan</label>
                                        <input
                                            type="text"
                                            v-model="note"
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                                            placeholder="Opsional"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <button
                                            @click="quickPay(1)"
                                            class="rounded-full border border-emerald-200 px-3 py-1 text-emerald-700 hover:bg-emerald-50"
                                        >
                                            Bayar 1 Bulan
                                        </button>
                                        <button
                                            @click="quickPay(2)"
                                            class="rounded-full border border-emerald-200 px-3 py-1 text-emerald-700 hover:bg-emerald-50"
                                        >
                                            Bayar 2 Bulan
                                        </button>
                                        <button
                                            @click="quickPay('all')"
                                            class="rounded-full border border-emerald-200 px-3 py-1 text-emerald-700 hover:bg-emerald-50"
                                        >
                                            Bayar Tunggakan
                                        </button>
                                    </div>
                                    <button
                                        @click="submitPayment"
                                        class="w-full rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700"
                                    >
                                        Proses Pembayaran
                                    </button>
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-3 md:col-span-2">
                                <div class="text-sm font-semibold text-gray-900">Ringkasan</div>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-gray-600">Total Tagihan</div>
                                    <div class="text-right font-medium">{{ formatCurrency(summary?.total_tagihan || 0) }}</div>
                                    <div class="text-gray-600">Total Dibayar</div>
                                    <div class="text-right font-medium">{{ formatCurrency(summary?.total_dibayar || 0) }}</div>
                                    <div class="text-gray-600">Sisa</div>
                                    <div class="text-right font-semibold text-red-600">{{ formatCurrency(summary?.total_sisa || 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-6 text-center text-gray-500">
                        Pilih kontrak terlebih dahulu untuk melihat detail dan melakukan pembayaran.
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped></style>
