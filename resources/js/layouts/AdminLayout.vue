<template>
    <BaseLayout :menuItems="adminMenuItems" userRole="admin">
        <slot />
    </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';

const adminMenuItems = [
    {
        icon: 'fa-home',
        label: 'Dashboard',
        route: 'admin.dashboard',
        active: route().current('admin.dashboard'),
    },
    {
        icon: 'fa-users',
        label: 'Manajemen Pengguna',
        route: 'admin.pengguna.index',
        active: route().current('admin.pengguna.*'),
    },
    {
        icon: 'fa-tags',
        label: 'Manajemen Kategori',
        route: 'admin.kategori.index',
        active: route().current('admin.kategori.*'),
    },
    {
        icon: 'fa-box',
        label: 'Manajemen Produk',
        route: 'admin.produk.index',
        active: route().current('admin.produk.*'),
    },
    {
        icon: 'fa-chart-bar',
        label: 'Laporan',
        route: 'admin.reports',
        active: route().current('admin.reports'),
    },
    {
        icon: 'fa-cog',
        label: 'Pengaturan',
        route: 'admin.settings',
        active: route().current('admin.settings'),
    },
];
</script>
