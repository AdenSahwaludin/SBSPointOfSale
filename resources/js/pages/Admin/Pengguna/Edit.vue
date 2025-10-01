<script lang="ts" setup>
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Pengguna {
    id_pengguna: string;
    nama: string;
    email: string;
    telepon?: string;
    role: 'admin' | 'kasir';
}

interface Props {
    pengguna: Pengguna;
}

const props = defineProps<Props>();

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pengguna');

// Form management dengan data existing
const form = useForm({
    nama: props.pengguna.nama,
    email: props.pengguna.email,
    telepon: props.pengguna.telepon || '',
    password: '',
    role: props.pengguna.role,
});

// Submit form
function submit() {
    form.patch(`/admin/pengguna/${props.pengguna.id_pengguna}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}
</script>

<template>
    <Head title="Edit Pengguna - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Edit Pengguna</h1>
                    <p class="text-emerald-600">Ubah data pengguna: {{ pengguna.nama }}</p>
                </div>
                <Link
                    href="/admin/pengguna"
                    class="flex items-center gap-2 rounded-lg bg-gray-soft-100 px-4 py-2 text-emerald-700 transition-all hover:bg-gray-soft-200 hover:scale-105 emerald-transition"
                >
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>

            <!-- Form -->
            <div class="rounded-lg border border-emerald-200 bg-white-emerald p-6 shadow-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Pengguna (Read Only) -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> ID Pengguna </label>
                            <input
                                :value="pengguna.id_pengguna"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500"
                                readonly
                            />
                            <p class="mt-1 text-xs text-gray-500">ID Pengguna tidak dapat diubah</p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Role * </label>
                            <select
                                v-model="form.role"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                required
                            >
                                <option value="kasir">Kasir</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                                {{ form.errors.role }}
                            </div>
                        </div>

                        <!-- Nama -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Nama Lengkap * </label>
                            <input
                                v-model="form.nama"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Nama lengkap"
                                required
                            />
                            <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nama }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Email * </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="email@contoh.com"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Telepon </label>
                            <input
                                v-model="form.telepon"
                                type="tel"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="08xxxxxxxxxx"
                            />
                            <div v-if="form.errors.telepon" class="mt-1 text-sm text-red-600">
                                {{ form.errors.telepon }}
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700"> Password Baru </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Kosongkan jika tidak ingin mengubah"
                            />
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link href="/admin/pengguna" class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200">
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-6 py-2 text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
