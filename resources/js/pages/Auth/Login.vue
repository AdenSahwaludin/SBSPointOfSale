<script lang="ts" setup>
import loginRoutes from '@/routes/login';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    login: '',
    password: '',
    remember: true, // Default aktif
});

function submit() {
    form.post(loginRoutes.store.url(), {
        onSuccess: () => {
            // Redirect sudah dihandle di controller berdasarkan role
        },
        onError: (e) => console.log('Login error:', e),
    });
}
</script>

<template>
    <Head title="Login - Sari Bumi Sakti" />

    <!-- Background dengan image -->
    <div class="relative min-h-screen overflow-hidden bg-cover bg-center bg-no-repeat" style="background-image: url('/assets/images/Bg_Login.png')">
        <!-- Dark overlay untuk readability -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Subtle animated elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-80 w-80 animate-pulse rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 animate-pulse rounded-full bg-white/5 blur-3xl delay-1000"></div>
        </div>

        <!-- Main content -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4">
            <!-- Glassmorphism card -->
            <div class="w-full max-w-md">
                <!-- Logo dan branding -->
                <div class="mb-8 text-center">
                    <!-- Logo PNG yang lebih besar untuk halaman login -->
                </div>

                <!-- Login form glassmorphism -->
                <form @submit.prevent="submit" class="rounded-2xl border border-black/10 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
                    <div class="space-y-6">
                        <div class="mb-4 flex items-center justify-center">
                            <div class="relative text-center">
                                <img
                                    src="/assets/images/Logo_Cap_Daun_Kayu_Putih.png"
                                    alt="Logo Cap Daun Kayu Putih - Sari Bumi Sakti"
                                    class="mx-auto h-32 w-32 object-contain drop-shadow-2xl"
                                />
                                <!-- Glow effect yang subtle -->
                                <div
                                    class="absolute top-1/2 left-1/2 -z-10 h-24 w-24 -translate-x-1/2 -translate-y-1/2 animate-pulse rounded-full bg-green-500/20 blur-lg"
                                ></div>
                                <div
                                    class="absolute top-1/2 left-1/2 -z-20 h-24 w-24 -translate-x-1/2 -translate-y-1/2 animate-pulse rounded-full bg-white/20 blur-2xl delay-500"
                                ></div>
                                <h1 class="mt-4 text-3xl font-bold text-white drop-shadow-lg">Sari Bumi Sakti</h1>
                                <p class="mt-2 text-sm text-gray-200">Sistem Point of Sale</p>
                            </div>
                        </div>

                        <!-- Email/ID input -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-100"> Email atau ID Pengguna </label>
                            <input
                                v-model="form.login"
                                type="text"
                                class="w-full rounded-xl border border-white/30 bg-white/20 px-4 py-3 text-white placeholder-gray-300 backdrop-blur-sm transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                placeholder="email@contoh.com atau 001-ADN"
                                autocomplete="username"
                                required
                            />
                            <div v-if="form.errors.login" class="mt-1 rounded-lg bg-red-500/20 px-3 py-2 text-sm text-red-200 backdrop-blur-sm">
                                {{ form.errors.login }}
                            </div>
                        </div>

                        <!-- Password input -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-100"> Password </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-xl border border-white/30 bg-white/20 px-4 py-3 text-white placeholder-gray-300 backdrop-blur-sm transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                            />
                            <div v-if="form.errors.password" class="mt-1 rounded-lg bg-red-500/20 px-3 py-2 text-sm text-red-200 backdrop-blur-sm">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <!-- Remember me checkbox -->
                        <div class="flex items-center space-x-3">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                id="remember"
                                class="h-4 w-4 rounded border-white/30 bg-white/20 text-blue-500 focus:ring-2 focus:ring-blue-300"
                            />
                            <label for="remember" class="cursor-pointer text-sm text-gray-100 select-none"> Ingat saya</label>
                        </div>

                        <!-- Login button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full transform rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center">
                                <svg
                                    class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
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
                                Memproses...
                            </span>
                            <span v-else>Masuk ke Sistem</span>
                        </button>
                    </div>
                </form>

                <!-- Footer info -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-300">© {{ new Date().getFullYear() }} Sari Bumi Sakti</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom glassmorphism styles */
.backdrop-blur-xl {
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

/* Enhanced focus states */
input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Smooth animations */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
