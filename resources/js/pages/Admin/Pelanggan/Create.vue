<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    nextId: string;
}

const props = defineProps<Props>();

const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pelanggan');

const form = useForm({
    id_pelanggan: props.nextId,
    nama: '',
    email: '',
    telepon: '',
    kota: '',
    alamat: '',
    aktif: true,
    trust_score: 50,
    credit_limit: 0,
});

// Handle telepon input - only allow numbers
function handlePhoneInput(event: Event) {
    const target = event.target as HTMLInputElement;
    const value = target.value;
    // Remove any non-numeric characters
    const numericValue = value.replace(/[^0-9]/g, '');
    // Update the form value with only numbers
    form.telepon = numericValue;
    // Update the input field display
    target.value = numericValue;
}

function submit() {
    form.post('/admin/pelanggan', {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Tambah Pelanggan - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Tambah Pelanggan</h1>
                    <p class="text-emerald-600">Tambahkan pelanggan baru ke sistem</p>
                </div>
                <BaseButton @click="$inertia.visit('/admin/pelanggan')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- ID Pelanggan -->
                    <div>
                        <label for="id_pelanggan" class="mb-2 block text-sm font-medium text-emerald-700">
                            ID Pelanggan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="id_pelanggan"
                            v-model="form.id_pelanggan"
                            type="text"
                            required
                            readonly
                            class="w-full rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                        />
                        <p class="mt-1 text-sm text-emerald-600">ID akan digenerate otomatis</p>
                        <div v-if="form.errors.id_pelanggan" class="mt-1 text-sm text-red-600">
                            {{ form.errors.id_pelanggan }}
                        </div>
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
                                pattern="[0-9]*"
                                maxlength="15"
                                @input="handlePhoneInput"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="081234567890"
                            />
                            <div v-if="form.errors.telepon" class="mt-1 text-sm text-red-600">
                                {{ form.errors.telepon }}
                            </div>
                        </div>
                    </div>

                    <!-- Kota -->
                    <div>
                        <label for="kota" class="mb-2 block text-sm font-medium text-emerald-700"> Kota </label>
                        <input
                            id="kota"
                            v-model="form.kota"
                            type="text"
                            class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            placeholder="Masukkan nama kota"
                        />
                        <div v-if="form.errors.kota" class="mt-1 text-sm text-red-600">
                            {{ form.errors.kota }}
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

                    <!-- Trust Score & Credit Limit -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="trust_score" class="mb-2 block text-sm font-medium text-emerald-700"> Trust Score (0-100) </label>
                            <input
                                id="trust_score"
                                v-model.number="form.trust_score"
                                type="number"
                                min="0"
                                max="100"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                            />
                            <div v-if="form.errors.trust_score" class="mt-1 text-sm text-red-600">
                                {{ form.errors.trust_score }}
                            </div>
                        </div>

                        <div>
                            <label for="credit_limit" class="mb-2 block text-sm font-medium text-emerald-700"> Credit Limit (Rp) </label>
                            <input
                                id="credit_limit"
                                v-model.number="form.credit_limit"
                                type="number"
                                min="0"
                                step="1000"
                                class="w-full rounded-lg border border-emerald-300 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                                placeholder="0"
                            />
                            <div v-if="form.errors.credit_limit" class="mt-1 text-sm text-red-600">
                                {{ form.errors.credit_limit }}
                            </div>
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div>
                        <label class="flex items-center">
                            <input
                                v-model="form.aktif"
                                type="checkbox"
                                class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span class="ml-2 text-sm text-emerald-700">Pelanggan aktif</span>
                        </label>
                        <div v-if="form.errors.aktif" class="mt-1 text-sm text-red-600">
                            {{ form.errors.aktif }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3">
                        <BaseButton @click="$inertia.visit('/admin/pelanggan')" type="button" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" icon="fas fa-save">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pelanggan' }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
