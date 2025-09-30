<script lang="ts" setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface MenuItem {
    name: string;
    href?: string;
    icon: string;
    children?: MenuItem[];
    active?: boolean;
}

interface Props {
    title?: string;
    menuItems: MenuItem[];
    userRole: 'admin' | 'kasir';
}

const props = defineProps<Props>();
const page = usePage();
const user = computed(() => page.props.auth?.user);

const isSidebarOpen = ref(true);
const isMobile = ref(false);
const isProfileDropdownOpen = ref(false);
const expandedMenus = ref<Set<string>>(new Set());

function logout() {
    if (window.confirm('Apakah Anda yakin ingin logout?')) {
        router.post(
            '/logout',
            {},
            {
                preserveScroll: true,
                onStart: () => console.log('Starting logout...'),
                onSuccess: () => {
                    console.log('Logout berhasil, redirecting...');
                },
                onError: (errors: any) => {
                    console.log('Logout error:', errors);
                },
                onFinish: () => {
                    console.log('Logout finished');
                },
            },
        );
    }
}

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}

function toggleMenu(menuName: string) {
    if (expandedMenus.value.has(menuName)) {
        expandedMenus.value.delete(menuName);
    } else {
        expandedMenus.value.add(menuName);
    }
}

function checkMobile() {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) {
        isSidebarOpen.value = false;
    }
}

function closeSidebarOnMobile() {
    if (isMobile.value) {
        isSidebarOpen.value = false;
    }
}

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <Head :title="title || 'Dashboard'" />

        <!-- Sidebar -->
        <div
            v-show="!isMobile || isSidebarOpen"
            :class="{
                'w-64': isSidebarOpen,
                'w-16': !isSidebarOpen && !isMobile,
            }"
            class="fixed inset-y-0 left-0 z-50 border-r border-gray-200/50 bg-white/95 shadow-2xl backdrop-blur-xl transition-all duration-300 ease-in-out"
        >
            <!-- Logo Header -->
            <div class="relative flex h-16 items-center justify-center bg-gradient-to-r from-emerald-600 to-teal-700 text-white">
                <div v-if="isSidebarOpen" class="flex items-center space-x-3">
                    <div class="h-8 w-8">
                        <img
                            src="/assets/images/Logo_Cap_Daun_Kayu_Putih.png"
                            alt="Logo Sari Bumi Sakti"
                            class="h-8 w-8 object-contain drop-shadow-lg"
                        />
                    </div>
                    <span class="text-lg font-bold">SBS POS</span>
                </div>
                <div v-else class="h-8 w-8">
                    <img src="/assets/images/Logo_Cap_Daun_Kayu_Putih.png" alt="Logo Sari Bumi Sakti" class="h-8 w-8 object-contain drop-shadow-lg" />
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-6 space-y-2 px-3">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Menu dengan submenu -->
                    <div v-if="item.children && item.children.length > 0">
                        <button
                            @click="toggleMenu(item.name)"
                            class="group flex w-full items-center rounded-xl px-3 py-3 text-gray-700 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-700"
                        >
                            <i :class="item.icon" class="mr-3 h-5 w-5"></i>
                            <span v-if="isSidebarOpen" class="flex-1 text-left">{{ item.name }}</span>
                            <svg
                                v-if="isSidebarOpen"
                                :class="{ 'rotate-90': expandedMenus.has(item.name) }"
                                class="h-4 w-4 transition-transform duration-200"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Submenu -->
                        <div v-if="isSidebarOpen && expandedMenus.has(item.name)" class="mt-2 ml-8 space-y-1">
                            <Link
                                v-for="child in item.children"
                                :key="child.name"
                                :href="child.href || '#'"
                                @click="closeSidebarOnMobile"
                                class="flex items-center rounded-lg px-3 py-2 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-700"
                                :class="{ 'bg-emerald-100 text-emerald-700': child.active }"
                            >
                                <i :class="child.icon" class="mr-2 h-4 w-4"></i>
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Menu tanpa submenu -->
                    <Link
                        v-else
                        :href="item.href || '#'"
                        @click="closeSidebarOnMobile"
                        class="group flex items-center rounded-xl px-3 py-3 text-gray-700 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-700"
                        :class="{ 'bg-emerald-100 text-emerald-700': item.active }"
                    >
                        <i :class="item.icon" class="mr-3 h-5 w-5"></i>
                        <span v-if="isSidebarOpen">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>
        </div>

        <!-- Mobile overlay -->
        <div v-if="isMobile && isSidebarOpen" @click="toggleSidebar" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

        <!-- Main Content -->
        <div
            :class="{
                'ml-64': isSidebarOpen && !isMobile,
                'ml-16': !isSidebarOpen && !isMobile,
                'ml-0': isMobile,
            }"
            class="transition-all duration-300 ease-in-out"
        >
            <!-- Top Navigation -->
            <header class="sticky top-0 z-30 border-b border-gray-200/50 bg-white/80 shadow-sm backdrop-blur-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left side -->
                    <div class="flex items-center space-x-4">
                        <button @click="toggleSidebar" class="rounded-lg p-2 text-gray-600 transition-colors duration-200 hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-xl font-semibold text-gray-800">
                                <slot name="header">Dashboard</slot>
                            </h1>
                        </div>
                    </div>

                    <!-- Right side - User Profile -->
                    <div class="relative">
                        <button
                            @click="isProfileDropdownOpen = !isProfileDropdownOpen"
                            class="flex items-center space-x-3 rounded-lg p-2 transition-colors duration-200 hover:bg-gray-50"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 text-sm font-semibold text-white"
                            >
                                {{ user?.nama?.charAt(0).toUpperCase() }}
                            </div>
                            <div class="hidden text-left sm:block">
                                <p class="text-sm font-medium text-gray-700">{{ user?.nama }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ user?.role }}</p>
                            </div>
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Profile Dropdown -->
                        <div
                            v-if="isProfileDropdownOpen"
                            class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-gray-200 bg-white py-2 shadow-lg"
                        >
                            <template v-if="userRole === 'kasir'">
                                <Link
                                    href="/kasir/profile"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    @click="isProfileDropdownOpen = false"
                                >
                                    <i class="fas fa-user-edit mr-3 h-4 w-4"></i>
                                    Edit Profile
                                </Link>
                            </template>

                            <hr class="my-2 border-gray-200" />

                            <button @click="logout" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3 h-4 w-4"></i>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar untuk sidebar */
nav {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

nav::-webkit-scrollbar {
    width: 4px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}
</style>
