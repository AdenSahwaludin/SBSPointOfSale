<script lang="ts" setup>
import loginRoutes from '@/routes/login';
import { Head, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(loginRoutes.store.url(), {
        onSuccess: () => {
            router.visit('/dashboard');
        },
        onError: (e) => console.log('Login error:', e),
    });
}
</script>

<template>
    <Head title="Login" />
    <div class="flex min-h-screen items-center justify-center bg-gray-50 p-4">
        <form @submit.prevent="submit" class="w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow">
            <h1 class="text-xl font-semibold">Masuk</h1>

            <div>
                <label class="text-sm">Email atau ID Pengguna</label>
                <input
                    v-model="form.login"
                    class="mt-1 w-full rounded-lg border px-3 py-2"
                    placeholder="email@contoh.com atau 001-ADN"
                    autocomplete="username"
                />
                <div v-if="form.errors.login" class="mt-1 text-sm text-red-600">{{ form.errors.login }}</div>
            </div>

            <div>
                <label class="text-sm">Password</label>
                <input type="password" v-model="form.password" class="mt-1 w-full rounded-lg border px-3 py-2" autocomplete="current-password" />
                <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
            </div>

            <label class="inline-flex items-center space-x-2 text-sm">
                <input type="checkbox" v-model="form.remember" />
                <span>Ingat saya</span>
            </label>

            <button :disabled="form.processing" class="w-full rounded-lg bg-black py-2 text-white">
                {{ form.processing ? 'Memproses...' : 'Masuk' }}
            </button>
        </form>
    </div>
</template>
