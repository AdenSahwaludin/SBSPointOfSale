<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    telepon: string | null;
    saldo_kredit: number;
    credit_limit: number;
}

interface Props {
    pelanggan: Pelanggan;
}

const props = defineProps<Props>();
const { formatCurrency, formatNumber, parseNumber } = useCurrencyFormat();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pembayaran-kredit');

const form = useForm({
    id_pelanggan: props.pelanggan.id_pelanggan,
    jumlah_pembayaran: '',
    metode_pembayaran: 'tunai',
    keterangan: '',
});

const displayJumlah = ref('');

const remainingCredit = computed(() => props.pelanggan.credit_limit - props.pelanggan.saldo_kredit);

function handleJumlahInput(value: string) {
    const cleaned = parseNumber(value);
    displayJumlah.value = formatNumber(cleaned);
    form.jumlah_pembayaran = cleaned.toString();
}

function submit() {
    form.post('/kasir/pembayaran-kredit', {
        onSuccess: () => {
            form.reset();
            displayJumlah.value = '';
        },
    });
}
</script>

<template>
    <Head title="Input Pembayaran Kredit - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Input Pembayaran Kredit</h1>
                    <p class="text-emerald-600">{{ pelanggan.nama }}</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Customer Info Card -->
                <div class="card-emerald">
                    <h3 class="mb-4 font-bold text-emerald-800">Informasi Pelanggan</h3>

                    <div class="space-y-3">
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <div class="text-xs tracking-wider text-emerald-600 uppercase">ID Pelanggan</div>
                            <div class="mt-1 text-sm font-medium text-emerald-800">{{ pelanggan.id_pelanggan }}</div>
                        </div>

                        <div class="rounded-lg bg-emerald-50 p-3">
                            <div class="text-xs tracking-wider text-emerald-600 uppercase">Nama</div>
                            <div class="mt-1 text-sm font-medium text-emerald-800">{{ pelanggan.nama }}</div>
                        </div>

                        <div class="rounded-lg bg-emerald-50 p-3">
                            <div class="text-xs tracking-wider text-emerald-600 uppercase">Email</div>
                            <div class="mt-1 text-sm text-emerald-800">{{ pelanggan.email || '-' }}</div>
                        </div>

                        <div class="rounded-lg bg-emerald-50 p-3">
                            <div class="text-xs tracking-wider text-emerald-600 uppercase">Telepon</div>
                            <div class="mt-1 text-sm text-emerald-800">{{ pelanggan.telepon || '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Credit Info Card -->
                <div class="card-emerald">
                    <h3 class="mb-4 font-bold text-emerald-800">Informasi Kredit</h3>

                    <div class="space-y-3">
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                            <div class="text-xs tracking-wider text-blue-600 uppercase">Limit Kredit</div>
                            <div class="mt-1 text-lg font-bold text-blue-800">{{ formatCurrency(pelanggan.credit_limit) }}</div>
                        </div>

                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                            <div class="text-xs tracking-wider text-yellow-600 uppercase">Saldo Kredit (Terhutang)</div>
                            <div class="mt-1 text-lg font-bold text-yellow-800">{{ formatCurrency(pelanggan.saldo_kredit) }}</div>
                        </div>

                        <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                            <div class="text-xs tracking-wider text-green-600 uppercase">Sisa Kredit Tersedia</div>
                            <div class="mt-1 text-lg font-bold text-green-800">{{ formatCurrency(remainingCredit) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card-emerald">
                <h3 class="mb-6 font-bold text-emerald-800">Form Pembayaran</h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Jumlah Pembayaran -->
                    <div>
                        <label for="jumlah_pembayaran" class="mb-2 block text-sm font-medium text-emerald-700">
                            Jumlah Pembayaran (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="jumlah_pembayaran"
                            :value="displayJumlah"
                            @input="(e) => handleJumlahInput((e.target as HTMLInputElement).value)"
                            type="text"
                            required
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            placeholder="Masukkan jumlah pembayaran (cth: 1.000.000)"
                        />
                        <p class="mt-1 text-sm text-emerald-600">Maksimal: {{ formatCurrency(pelanggan.saldo_kredit) }}</p>
                        <div v-if="form.errors.jumlah_pembayaran" class="mt-1 text-sm text-red-600">
                            {{ form.errors.jumlah_pembayaran }}
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div>
                        <label for="metode_pembayaran" class="mb-2 block text-sm font-medium text-emerald-700">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="metode_pembayaran"
                            v-model="form.metode_pembayaran"
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        >
                            <option value="TUNAI" selected>Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="TRANSFER BCA">Transfer BCA</option>
                        </select>
                        <div v-if="form.errors.metode_pembayaran" class="mt-1 text-sm text-red-600">
                            {{ form.errors.metode_pembayaran }}
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="mb-2 block text-sm font-medium text-emerald-700"> Keterangan </label>
                        <textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            placeholder="Masukkan keterangan tambahan (opsional)"
                        ></textarea>
                        <div v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600">
                            {{ form.errors.keterangan }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3">
                        <BaseButton @click="$inertia.visit('/kasir/pembayaran-kredit')" type="button" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" icon="fas fa-check">
                            {{ form.processing ? 'Memproses...' : 'Simpan Pembayaran' }}
                        </BaseButton>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex gap-3">
                    <div class="text-blue-600">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-blue-800">Catatan Pembayaran</h4>
                        <ul class="mt-2 list-inside space-y-1 text-sm text-blue-700">
                            <li>• Pastikan jumlah pembayaran sesuai dengan bukti dari pelanggan</li>
                            <li>• Saldo kredit akan berkurang setelah pembayaran disimpan</li>
                            <li>• Simpan bukti pembayaran untuk referensi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
