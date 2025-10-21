<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email: string | null;
    telepon: string | null;
    alamat: string | null;
    trust_score: number;
    credit_limit: number;
}

interface Props {
    pelanggan: Pelanggan;
}

const props = defineProps<Props>();

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/customers');

const form = useForm({
    nama: props.pelanggan.nama,
    email: props.pelanggan.email || '',
    telepon: props.pelanggan.telepon || '',
    alamat: props.pelanggan.alamat || '',
});

function submit() {
    form.patch(`/kasir/customers/${props.pelanggan.id_pelanggan}`, {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Edit Pelanggan - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Edit Pelanggan</h1>
                    <p class="text-emerald-600">Update informasi pelanggan</p>
                </div>
                <BaseButton @click="$inertia.visit('/kasir/customers')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- ID Pelanggan (Read-only) -->
                    <div>
                        <label for="id_pelanggan" class="mb-2 block text-sm font-medium text-emerald-700"> ID Pelanggan </label>
                        <input
                            id="id_pelanggan"
                            type="text"
                            readonly
                            disabled
                            :value="pelanggan.id_pelanggan"
                            class="w-full cursor-not-allowed rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800"
                        />
                    </div>

                    <!-- Nama -->
                    <div>
                        <label for="nama" class="mb-2 block text-sm font-medium text-emerald-700">
                            Nama Pelanggan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            required
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            placeholder="Masukkan nama pelanggan"
                        />
                        <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                            {{ form.errors.nama }}
                        </div>
                    </div>

                    <!-- Email & Telepon -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-emerald-700"> Email </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="email@contoh.com"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div>
                            <label for="telepon" class="mb-2 block text-sm font-medium text-emerald-700"> Telepon </label>
                            <input
                                id="telepon"
                                v-model="form.telepon"
                                type="tel"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="081234567890"
                            />
                            <div v-if="form.errors.telepon" class="mt-1 text-sm text-red-600">
                                {{ form.errors.telepon }}
                            </div>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label for="alamat" class="mb-2 block text-sm font-medium text-emerald-700"> Alamat </label>
                        <textarea
                            id="alamat"
                            v-model="form.alamat"
                            rows="3"
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            placeholder="Masukkan alamat lengkap"
                        ></textarea>
                        <div v-if="form.errors.alamat" class="mt-1 text-sm text-red-600">
                            {{ form.errors.alamat }}
                        </div>
                    </div>

                    <!-- Trust Score & Credit Limit (Read-only display) -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Trust Score</label>
                            <input
                                type="text"
                                readonly
                                disabled
                                :value="`${pelanggan.trust_score}/100`"
                                class="w-full cursor-not-allowed rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Credit Limit</label>
                            <input
                                type="text"
                                readonly
                                disabled
                                :value="
                                    new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0,
                                    }).format(pelanggan.credit_limit)
                                "
                                class="w-full cursor-not-allowed rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3">
                        <BaseButton @click="$inertia.visit('/kasir/customers')" type="button" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" icon="fas fa-save">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
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
                        <h4 class="font-medium text-blue-800">Pembatasan Edit</h4>
                        <p class="text-sm text-blue-700">
                            Anda dapat mengedit data dasar pelanggan (nama, email, telepon, alamat). Trust Score dan Credit Limit hanya dapat diubah
                            oleh Admin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
