<script lang="ts" setup>
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  show: boolean;
  total: number; // total transaksi
  dp: number; // uang muka
}>();

const emit = defineEmits<{
  (e: 'confirm', payload: {
    tenor_bulan: number;
    bunga_persen: number;
    cicilan_bulanan: number;
    mulai_kontrak: string; // ISO date
    jadwal: Array<{ periode_ke: number; jatuh_tempo: string; jumlah_tagihan: number }>;
  }): void;
  (e: 'cancel'): void;
}>();

const tenor = ref<number>(12);
const bunga = ref<number>(0);
const startDate = ref<string>(new Date().toISOString().slice(0, 10));

const principal = computed(() => Math.max(0, Number(props.total || 0) - Number(props.dp || 0)));

function computeRoundedSchedule(principalAmount: number, tenorMonths: number, interestPercent: number) {
  const p = Number(principalAmount || 0);
  const t = Math.max(1, Number(tenorMonths || 1));
  const r = Math.max(0, Number(interestPercent || 0)) / 100;
  const totalWithInterest = Math.round(p * (1 + r));
  const base = Math.floor((totalWithInterest / t) / 1000) * 1000;
  const remainder = totalWithInterest - base * t;
  const extraMonths = Math.max(0, Math.floor(remainder / 1000));
  return { totalWithInterest, base, extraMonths };
}

const rounded = computed(() => computeRoundedSchedule(principal.value, tenor.value, bunga.value));
const cicilan = computed(() => rounded.value.base);

function addMonths(dateStr: string, add: number): string {
  const d = new Date(dateStr + 'T00:00:00');
  const dt = new Date(d.getFullYear(), d.getMonth() + add, d.getDate());
  const yyyy = dt.getFullYear();
  const mm = String(dt.getMonth() + 1).padStart(2, '0');
  const dd = String(dt.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

const jadwal = computed(() => {
  const items: Array<{ periode_ke: number; jatuh_tempo: string; jumlah_tagihan: number }> = [];
  const base = rounded.value.base;
  const extra = rounded.value.extraMonths;
  for (let i = 1; i <= tenor.value; i++) {
    const amt = base + (i <= extra ? 1000 : 0);
    items.push({
      periode_ke: i,
      jatuh_tempo: addMonths(startDate.value, i),
      jumlah_tagihan: amt,
    });
  }
  return items;
});

function confirm() {
  emit('confirm', {
    tenor_bulan: tenor.value,
    bunga_persen: bunga.value,
    cicilan_bulanan: cicilan.value,
    mulai_kontrak: startDate.value,
    jadwal: jadwal.value,
  });
}

function cancel() {
  emit('cancel');
}

watch(
  () => props.show,
  (val) => {
    if (val) {
      // reset sensible defaults on open
      tenor.value = 12;
      bunga.value = 0;
      startDate.value = new Date().toISOString().slice(0, 10);
    }
  }
);
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="cancel"></div>
    <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Konfigurasi Kontrak Kredit</h3>
        <button @click="cancel" class="text-gray-400 hover:text-gray-600" aria-label="Tutup">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Pokok Pinjaman</label>
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 font-semibold text-gray-900">
            Rp {{ principal.toLocaleString('id-ID') }}
          </div>
          <p class="mt-1 text-xs text-gray-500">Total - DP</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Tenor (bulan)</label>
          <select v-model.number="tenor" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500">
            <option :value="3">3</option>
            <option :value="6">6</option>
            <option :value="9">9</option>
            <option :value="12">12</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Bunga (%)</label>
          <input type="number" min="0" step="0.5" v-model.number="bunga" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500" />
          <p class="mt-1 text-xs text-gray-500">Bunga flat total kontrak</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Mulai Kontrak</label>
          <input type="date" v-model="startDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Estimasi Cicilan/Bulan</label>
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 font-semibold text-emerald-700">
            Rp {{ cicilan.toLocaleString('id-ID') }}
          </div>
        </div>
      </div>

      <div class="mt-6">
        <h4 class="mb-2 text-sm font-semibold text-gray-700">Preview Jadwal Angsuran</h4>
        <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200">
          <div class="grid grid-cols-3 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
            <div>Periode</div>
            <div>Jatuh Tempo</div>
            <div class="text-right">Tagihan</div>
          </div>
          <div v-for="item in jadwal" :key="item.periode_ke" class="grid grid-cols-3 px-3 py-2 text-sm">
            <div>#{{ item.periode_ke }}</div>
            <div>{{ item.jatuh_tempo }}</div>
            <div class="text-right">Rp {{ item.jumlah_tagihan.toLocaleString('id-ID') }}</div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button @click="cancel" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">Batal</button>
        <button @click="confirm" class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">Lanjutkan</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
