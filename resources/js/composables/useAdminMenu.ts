// Composable untuk admin menu items
export function useAdminMenuItems() {
    return [
        {
            name: 'Dashboard',
            href: '/admin',
            icon: 'fas fa-tachometer-alt',
            active: false,
        },
        {
            name: 'Manajemen Data',
            icon: 'fas fa-database',
            children: [
                { name: 'Pengguna', href: '/admin/pengguna', icon: 'fas fa-users' },
                { name: 'Produk', href: '/admin/products', icon: 'fas fa-boxes' },
                { name: 'Kategori', href: '/admin/kategori', icon: 'fas fa-tags' },
            ],
        },
        {
            name: 'Transaksi',
            icon: 'fas fa-cash-register',
            children: [
                { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
                { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
                { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
            ],
        },
        {
            name: 'Laporan',
            href: '/admin/reports',
            icon: 'fas fa-chart-bar',
        },
        {
            name: 'Pengaturan',
            href: '/admin/settings',
            icon: 'fas fa-cog',
        },
    ];
}

// Set active menu item berdasarkan current path
export function setActiveMenuItem(menuItems: any[], activePath: string) {
    return menuItems.map((item) => {
        if (item.children) {
            const activeChild = item.children.find((child: any) => child.href === activePath || activePath.startsWith(child.href + '/'));
            return {
                ...item,
                active: !!activeChild, // Set parent as active if child is active
                children: item.children.map((child: any) => ({
                    ...child,
                    active: child.href === activePath || activePath.startsWith(child.href + '/'),
                })),
            };
        }
        return {
            ...item,
            active: item.href === activePath,
        };
    });
}
