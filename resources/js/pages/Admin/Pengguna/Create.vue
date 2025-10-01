<script lang="ts" setup>
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pengguna');

// Form management
const form = useForm({
    id_pengguna: '',
    nama: '',
    email: '',
    telepon: '',
    password: '',
    password_confirmation: '',
    role: 'kasir',
});

// Submit form
function submit() {
    form.post('/admin/pengguna', {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}
</script>

<template>
    <Head title="Tambah Pengguna - Admin" />

    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-800">Tambah Pengguna</h1>
                    <p class="text-emerald-600">Buat akun pengguna baru</p>
                </div>
                <Link
                    href="/admin/pengguna"
                    class="btn-emerald-secondary flex items-center gap-2 transition-all hover:scale-105 emerald-transition"
                >
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Pengguna -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Pengguna * </label>
                            <input
                                v-model="form.id_pengguna"
                                type="text"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
                                placeholder="Contoh: 003-USR"
                                required
                            />
                            <div v-if="form.errors.id_pengguna" class="mt-1 text-sm text-red-600">
                                {{ form.errors.id_pengguna }}
                            </div>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Role * </label>
                            <select
                                v-model="form.role"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
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
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Nama Lengkap * </label>
                            <input
                                v-model="form.nama"
                                type="text"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
                                placeholder="Nama lengkap"
                                required
                            />
                            <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nama }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Email * </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
                                placeholder="email@contoh.com"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Telepon </label>
                            <input
                                v-model="form.telepon"
                                type="tel"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
                                placeholder="08xxxxxxxxxx"
                            />
                            <div v-if="form.errors.telepon" class="mt-1 text-sm text-red-600">
                                {{ form.errors.telepon }}
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Password * </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-lg border border-emerald-200 px-3 py-2 focus-emerald transition-all emerald-transition"
                                placeholder="Minimal 6 karakter"
                                required
                            />
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 border-t border-emerald-200 pt-6">
                        <Link href="/admin/pengguna" class="btn-emerald-secondary transition-all hover:scale-105 emerald-transition">
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="btn-emerald-primary disabled:cursor-not-allowed disabled:opacity-50 transition-all hover:scale-105 hover:shadow-emerald emerald-hover-glow"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
