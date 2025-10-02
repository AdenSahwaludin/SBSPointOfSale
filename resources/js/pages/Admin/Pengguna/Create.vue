<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import { setActiveMenuItem, useAdminMenuItems } from '@/composables/useAdminMenu';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props
interface Props {
    nextPrefix: string;
}
const props = defineProps<Props>();
// Menu items dengan active state
const adminMenuItems = setActiveMenuItem(useAdminMenuItems(), '/admin/pengguna');

// Form management (suffix only)
const form = useForm({
    suffix: '',
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

// Handle phone input - only allow numbers
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
                <BaseButton @click="$inertia.visit('/admin/pengguna')" variant="secondary" icon="fas fa-arrow-left"> Kembali </BaseButton>
            </div>

            <!-- Form -->
            <div class="card-emerald">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- ID Pengguna -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> ID Pengguna * </label>
                            <div class="flex items-center">
                                <span class="mr-2 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 font-mono">
                                    {{ props.nextPrefix }}-
                                </span>
                                <input
                                    v-model="form.suffix"
                                    type="text"
                                    maxlength="4"
                                    class="flex-1 rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    placeholder="4 alfanumerik"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.suffix" class="mt-1 text-sm text-red-600">
                                {{ form.errors.suffix }}
                            </div>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-emerald-700"> Role * </label>
                            <select
                                v-model="form.role"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
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
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
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
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
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
                                maxlength="15"
                                pattern="[0-9]*"
                                @input="handlePhoneInput"
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
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
                                class="w-full rounded-lg border border-emerald-200 bg-white-emerald px-3 py-2 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
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
                        <BaseButton @click="$inertia.visit('/admin/pengguna')" variant="secondary"> Batal </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Pengguna</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </BaseLayout>
</template>
