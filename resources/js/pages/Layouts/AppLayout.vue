<script lang="ts" setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isSidebarOpen = ref(false);

const logoutForm = useForm({});

function logout() {
    if (window.confirm('Apakah Anda yakin ingin logout?')) {
        logoutForm.post('/logout', {
            preserveScroll: true,
            onError: (errors) => {
                console.log('Logout error:', errors);
            },
        });
    }
}

function confirmLogout(e: Event) {
    if (!window.confirm('Apakah Anda yakin ingin logout?')) {
        e.preventDefault();
    }
}

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <Head title="Dashboard" />

        <!-- Sidebar -->
        <div
            :class="{
                'translate-x-0': isSidebarOpen,
                '-translate-x-full': !isSidebarOpen,
            }"
            class="fixed inset-y-0 left-0 z-50 w-64 transform bg-white shadow-lg transition-transform duration-300 ease-in-out lg:static lg:inset-0 lg:translate-x-0"
        >
            <div class="flex h-16 items-center justify-center bg-gray-900 text-white">
                <h1 class="text-xl font-bold">POS SBS</h1>
            </div>

            <nav class="mt-5 px-2">
                <Link
                    href="/dashboard"
                    class="group flex items-center rounded-md px-2 py-2 text-base leading-6 font-medium text-gray-900 transition duration-150 ease-in-out hover:bg-gray-50 hover:text-gray-900 focus:bg-gray-50 focus:outline-none"
                    :class="{ 'bg-gray-100': $page.component === 'Dashboard' }"
                >
                    <svg
                        class="mr-4 h-6 w-6 text-gray-500 transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                        />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                    </svg>
                    Dashboard
                </Link>
            </nav>
        </div>

        <!-- Overlay untuk mobile -->
        <div v-if="isSidebarOpen" @click="toggleSidebar" class="bg-opacity-75 fixed inset-0 z-40 bg-gray-600 lg:hidden"></div>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <div class="bg-white shadow-sm lg:hidden">
                <div class="flex items-center justify-between px-4 py-2">
                    <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700 focus:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold">POS SBS</h1>
                </div>
            </div>

            <!-- Header -->
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <h1 class="text-3xl font-bold text-gray-900">
                            <slot name="header">Dashboard</slot>
                        </h1>

                        <!-- User Menu -->
                        <div class="relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-700">{{ user?.nama }}</span>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    @click="confirmLogout"
                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-red-700"
                                >
                                    Logout
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom styles jika diperlukan */
</style>
