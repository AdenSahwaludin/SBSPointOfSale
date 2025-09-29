<script lang="ts" setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const kasirMenuItems = [
    {
        name: 'Dashboard',
        href: '/kasir',
        icon: 'fas fa-tachometer-alt',
    },
    {
        name: 'Point of Sale',
        href: '/kasir/pos',
        icon: 'fas fa-cash-register',
    },
    {
        name: 'Transaksi',
        icon: 'fas fa-receipt',
        children: [
            { name: 'Riwayat Transaksi', href: '/kasir/transactions', icon: 'fas fa-history' },
            { name: 'Transaksi Hari Ini', href: '/kasir/transactions/today', icon: 'fas fa-calendar-day' },
        ],
    },
    {
        name: 'Produk',
        href: '/kasir/products',
        icon: 'fas fa-boxes',
    },
    {
        name: 'Profile',
        href: '/kasir/profile',
        icon: 'fas fa-user-circle',
        active: true,
    },
];

const activeTab = ref('profile');
const showChangePassword = ref(false);

const profileForm = useForm({
    nama: user.value?.nama || '',
    email: user.value?.email || '',
    telepon: user.value?.telepon || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updateProfile() {
    profileForm.patch('/kasir/profile', {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
        },
    });
}

function updatePassword() {
    passwordForm.patch('/kasir/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            showChangePassword.value = false;
        },
    });
}

function setActiveTab(tab: string) {
    activeTab.value = tab;
}
</script>

<template>
    <BaseLayout title="Profile - Sari Bumi Sakti" :menuItems="kasirMenuItems" userRole="kasir">
        <template #header> Profile Saya </template>

        <Head title="Profile" />

        <div class="mx-auto max-w-4xl">
            <!-- Profile Header -->
            <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-center space-x-6">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 text-2xl font-bold text-white"
                    >
                        {{ user?.nama?.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ user?.nama }}</h2>
                        <p class="text-gray-600 capitalize">{{ user?.role }}</p>
                        <p class="mt-1 text-sm text-gray-500">ID: {{ user?.id_pengguna }}</p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="setActiveTab('profile')"
                            :class="{
                                'border-teal-500 text-teal-600': activeTab === 'profile',
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'profile',
                            }"
                            class="border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap focus:outline-none"
                        >
                            <i class="fas fa-user mr-2"></i>
                            Informasi Profile
                        </button>
                        <button
                            @click="setActiveTab('security')"
                            :class="{
                                'border-teal-500 text-teal-600': activeTab === 'security',
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'security',
                            }"
                            class="border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap focus:outline-none"
                        >
                            <i class="fas fa-shield-alt mr-2"></i>
                            Keamanan
                        </button>
                    </nav>
                </div>

                <!-- Profile Tab -->
                <div v-if="activeTab === 'profile'" class="p-6">
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Nama -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Nama Lengkap </label>
                                <input
                                    v-model="profileForm.nama"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 transition-colors focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                    placeholder="Masukkan nama lengkap"
                                />
                                <div v-if="profileForm.errors.nama" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.nama }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Email </label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 transition-colors focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                    placeholder="email@contoh.com"
                                />
                                <div v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.email }}
                                </div>
                            </div>

                            <!-- Telepon -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> Nomor Telepon </label>
                                <input
                                    v-model="profileForm.telepon"
                                    type="tel"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 transition-colors focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                    placeholder="08xxx-xxxx-xxxx"
                                />
                                <div v-if="profileForm.errors.telepon" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.telepon }}
                                </div>
                            </div>

                            <!-- ID Pengguna (Read-only) -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700"> ID Pengguna </label>
                                <input
                                    :value="user?.id_pengguna"
                                    type="text"
                                    disabled
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-gray-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">ID pengguna tidak dapat diubah</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:from-teal-600 hover:to-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="profileForm.processing" class="flex items-center">
                                    <svg
                                        class="mr-3 -ml-1 h-4 w-4 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Menyimpan...
                                </span>
                                <span v-else>
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan Perubahan
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div v-if="activeTab === 'security'" class="p-6">
                    <div class="space-y-6">
                        <!-- Current Security Info -->
                        <div class="rounded-xl border border-teal-200 bg-teal-50 p-4">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-teal-600"></i>
                                <div>
                                    <h4 class="font-medium text-teal-900">Informasi Keamanan</h4>
                                    <p class="mt-1 text-sm text-teal-700">
                                        Terakhir login:
                                        {{ user?.terakhir_login ? new Date(user.terakhir_login).toLocaleString('id-ID') : 'Tidak diketahui' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="rounded-xl border border-gray-200 p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Ubah Password</h3>
                                    <p class="mt-1 text-sm text-gray-600">Pastikan password yang kuat untuk keamanan akun</p>
                                </div>
                                <button
                                    @click="showChangePassword = !showChangePassword"
                                    class="px-4 py-2 text-sm font-medium text-teal-600 hover:text-teal-700"
                                >
                                    {{ showChangePassword ? 'Batal' : 'Ubah Password' }}
                                </button>
                            </div>

                            <form v-if="showChangePassword" @submit.prevent="updatePassword" class="space-y-4">
                                <!-- Current Password -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Password Saat Ini </label>
                                    <input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                        placeholder="Masukkan password saat ini"
                                        required
                                    />
                                    <div v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">
                                        {{ passwordForm.errors.current_password }}
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Password Baru </label>
                                    <input
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                        placeholder="Masukkan password baru"
                                        required
                                    />
                                    <div v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">
                                        {{ passwordForm.errors.password }}
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700"> Konfirmasi Password Baru </label>
                                    <input
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-teal-500"
                                        placeholder="Ulangi password baru"
                                        required
                                    />
                                    <div v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                        {{ passwordForm.errors.password_confirmation }}
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        :disabled="passwordForm.processing"
                                        class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:from-teal-600 hover:to-emerald-700 disabled:opacity-50"
                                    >
                                        <span v-if="passwordForm.processing" class="flex items-center">
                                            <svg
                                                class="mr-3 -ml-1 h-4 w-4 animate-spin text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            Mengubah...
                                        </span>
                                        <span v-else>
                                            <i class="fas fa-key mr-2"></i>
                                            Ubah Password
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
