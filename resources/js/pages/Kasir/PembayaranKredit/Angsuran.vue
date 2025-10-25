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
            <!-- Header with gradient - hidden when detail view is active -->
            <div
                v-if="!isDetailActive"
                class="flex flex-wrap items-end justify-between gap-4 rounded-xl border border-gray-200 bg-white p-6 shadow-lg"
            >
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Angsuran Kredit</h1>
                    <p class="mt-1 text-gray-600">Kelola pembayaran angsuran kontrak kredit</p>
                </div>
                <div class="flex items-center gap-3">
                    <label
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            v-model="dueThisMonth"
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500"
                        />
                        <span class="text-gray-700">Jatuh tempo bulan ini</span>
                    </label>
                    <div class="relative">
                        <svg
                            class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            placeholder="Cari kontrak/pelanggan..."
                            class="w-64 rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Contracts list - Enhanced Card -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl lg:col-span-1">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white p-4">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <span class="text-sm font-bold text-gray-700">Daftar Kontrak</span>
                    </div>
                    <div class="max-h-[600px] divide-y divide-gray-50 overflow-y-auto">
                        <div v-if="!contracts || contracts.data.length === 0" class="flex flex-col items-center justify-center p-8 text-center">
                            <svg class="mb-3 h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Tidak ada kontrak ditemukan</p>
                            <p class="mt-1 text-xs text-gray-400">Coba ubah filter pencarian</p>
                        </div>
                        <button
                            v-for="c in contracts?.data || []"
                            :key="c.id_kontrak"
                            @click="openContract(c.id_kontrak)"
                            class="group block w-full p-4 text-left transition-all duration-200 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-emerald-700">{{ c.nomor_kontrak }}</div>
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                                c.status === 'AKTIF'
                                                    ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white'
                                                    : 'bg-gradient-to-r from-gray-400 to-gray-500 text-white',
                                            ]"
                                            >{{ c.status }}</span
                                        >
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 text-xs text-gray-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        <span>{{ c.pelanggan?.nama }}</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <span>{{ new Date(c.mulai_kontrak).toLocaleDateString('id-ID') }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            <span>{{ c.tenor_bulan }} bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <svg
                                    class="h-5 w-5 text-gray-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Detail + payment - Enhanced Card -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl lg:col-span-2">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg"
                                >
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-gray-900">Detail Kontrak</div>
                                    <div v-if="selected" class="text-sm text-gray-600">
                                        {{ selected?.nomor_kontrak }} • {{ selected?.pelanggan?.nama }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3" v-if="selected">
                        <div
                            class="group relative overflow-hidden rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-4 shadow-md transition-all hover:shadow-lg"
                        >
                            <div class="absolute top-0 right-0 h-20 w-20 translate-x-8 -translate-y-8 rounded-full bg-emerald-200/30"></div>
                            <div class="relative">
                                <div class="flex items-center gap-2 text-xs font-medium text-emerald-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Pokok Pinjaman
                                </div>
                                <div class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary?.pokok_pinjaman || 0) }}</div>
                            </div>
                        </div>
                        <div
                            class="group relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4 shadow-md transition-all hover:shadow-lg"
                        >
                            <div class="absolute top-0 right-0 h-20 w-20 translate-x-8 -translate-y-8 rounded-full bg-blue-200/30"></div>
                            <div class="relative">
                                <div class="flex items-center gap-2 text-xs font-medium text-blue-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                    </svg>
                                    Cicilan/Bulan
                                </div>
                                <div class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(selected?.cicilan_bulanan || 0) }}</div>
                            </div>
                        </div>
                        <div
                            class="group relative overflow-hidden rounded-xl border border-red-100 bg-gradient-to-br from-red-50 to-pink-50 p-4 shadow-md transition-all hover:shadow-lg"
                        >
                            <div class="absolute top-0 right-0 h-20 w-20 translate-x-8 -translate-y-8 rounded-full bg-red-200/30"></div>
                            <div class="relative">
                                <div class="flex items-center gap-2 text-xs font-medium text-red-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                    Tunggakan Bulan Ini
                                </div>
                                <div class="mt-2 text-2xl font-bold text-red-600">{{ formatCurrency(totalDueThisMonth) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6" v-if="selected">
                        <div class="mb-4 flex items-center gap-2 text-sm font-bold text-gray-900">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                />
                            </svg>
                            Jadwal Angsuran
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                            <div class="max-h-72 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="sticky top-0 z-10 bg-gradient-to-r from-gray-100 to-gray-50">
                                        <tr class="border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-xs font-bold tracking-wide text-gray-600 uppercase">Periode</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold tracking-wide text-gray-600 uppercase">Jatuh Tempo</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold tracking-wide text-gray-600 uppercase">Tagihan</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold tracking-wide text-gray-600 uppercase">Dibayar</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold tracking-wide text-gray-600 uppercase">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr
                                            v-for="row in schedule"
                                            :key="row.id_angsuran"
                                            class="transition-colors hover:bg-gray-50"
                                            :class="row.status === 'PAID' ? 'bg-green-50/30' : ''"
                                        >
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700"
                                                    >#{{ row.periode_ke }}</span
                                                >
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ new Date(row.jatuh_tempo).toLocaleDateString('id-ID') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                {{ formatCurrency(row.jumlah_tagihan) }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(row.jumlah_dibayar) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <span :class="row.status !== 'PAID' ? 'font-bold text-red-600' : 'font-medium text-green-600'">
                                                    {{ formatCurrency(row.sisa) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-5 shadow-lg">
                                <div class="mb-4 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow"
                                    >
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">Pembayaran</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold text-gray-700">Metode Pembayaran</label>
                                        <select
                                            v-model="payMethod"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                                        >
                                            <option value="TUNAI">💵 Tunai</option>
                                            <option value="QRIS">📱 QRIS</option>
                                            <option value="TRANSFER BCA">🏦 Transfer BCA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold text-gray-700">Jumlah Pembayaran</label>
                                        <div class="relative">
                                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-sm font-medium text-gray-500">Rp</span>
                                            <input
                                                type="number"
                                                min="0"
                                                v-model.number="payAmount"
                                                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm font-medium transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                                                placeholder="0"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold text-gray-700">Keterangan (Opsional)</label>
                                        <input
                                            type="text"
                                            v-model="note"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Catatan pembayaran..."
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold text-gray-700">Bayar Cepat</label>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                @click="quickPay(1)"
                                                class="flex-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 hover:shadow-md"
                                            >
                                                1 Bulan
                                            </button>
                                            <button
                                                @click="quickPay(2)"
                                                class="flex-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 hover:shadow-md"
                                            >
                                                2 Bulan
                                            </button>
                                            <button
                                                @click="quickPay('all')"
                                                class="w-full rounded-lg border border-emerald-300 bg-gradient-to-r from-emerald-500 to-teal-600 px-3 py-2 text-xs font-bold text-white shadow-md transition hover:shadow-lg"
                                            >
                                                Semua Tunggakan
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        @click="submitPayment"
                                        class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3 font-bold text-white shadow-lg transition hover:shadow-xl"
                                    >
                                        <span class="relative z-10 flex items-center justify-center gap-2">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Proses Pembayaran
                                        </span>
                                        <div
                                            class="absolute inset-0 -translate-x-full bg-white/20 transition-transform group-hover:translate-x-full"
                                        ></div>
                                    </button>
                                </div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-5 shadow-lg md:col-span-2">
                                <div class="mb-4 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow"
                                    >
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">Ringkasan Kontrak</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Total Tagihan</span>
                                        <span class="text-lg font-bold text-gray-900">{{ formatCurrency(summary?.total_tagihan || 0) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Total Dibayar</span>
                                        <span class="text-lg font-bold text-emerald-600">{{ formatCurrency(summary?.total_dibayar || 0) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-lg bg-gradient-to-r from-red-50 to-pink-50 p-4 shadow-md">
                                        <span class="text-sm font-bold text-red-700">Sisa Tagihan</span>
                                        <span class="text-xl font-bold text-red-600">{{ formatCurrency(summary?.total_sisa || 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center p-12 text-center">
                        <svg class="mb-4 h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <p class="text-base font-semibold text-gray-600">Pilih kontrak terlebih dahulu</p>
                        <p class="mt-2 text-sm text-gray-500">
                            Klik salah satu kontrak di sebelah kiri untuk melihat detail dan melakukan pembayaran
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped></style>
