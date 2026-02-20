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
                { name: 'Produk', href: '/admin/produk', icon: 'fas fa-boxes' },
                { name: 'Kategori', href: '/admin/kategori', icon: 'fas fa-tags' },
                { name: 'Pelanggan', href: '/admin/pelanggan', icon: 'fas fa-user-friends' },
            ],
        },
        {
            name: 'Persetujuan PO',
            href: '/admin/goods-in-approval',
            icon: 'fas fa-file-invoice',
        },
        {
            name: 'Riwayat PO',
            href: '/admin/goods-in-history',
            icon: 'fas fa-history',
        },
        {
            name: 'Transaksi',
            icon: 'fas fa-cash-register',
            children: [
                { name: 'Semua Transaksi', href: '/admin/transactions', icon: 'fas fa-receipt' },
                { name: 'Transaksi Kredit', href: '/admin/transactions/kredit', icon: 'fas fa-credit-card' },
            ],
        },
        {
            name: 'Laporan',
            icon: 'fas fa-chart-bar',
            children: [
                { name: 'Ringkasan Laporan', href: '/admin/reports', icon: 'fas fa-chart-line' },
                { name: 'Laporan Harian', href: '/admin/reports/daily', icon: 'fas fa-calendar-day' },
                { name: 'Laporan Mingguan', href: '/admin/reports/weekly', icon: 'fas fa-calendar-week' },
                { name: 'Laporan Bulanan', href: '/admin/reports/monthly', icon: 'fas fa-calendar-alt' },
            ],
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
            const activeChild = item.children.find((child: any) => {
                // Exact match takes precedence
                if (child.href === activePath) return true;
                // Only match sub-paths if the child href doesn't match exactly with another child
                // This prevents /admin/transactions/kredit from matching /admin/transactions
                const isExactMatch = item.children.some((c) => c.href === activePath);
                if (isExactMatch) return false;
                // Match patterns like /admin/transactions/123 but not /admin/transactions/kredit
                return activePath.startsWith(child.href + '/') && !child.href.endsWith('/');
            });
            return {
                ...item,
                active: !!activeChild,
                children: item.children.map((child: any) => ({
                    ...child,
                    active: (() => {
                        // Exact match
                        if (child.href === activePath) return true;
                        // Only check startsWith if no exact match exists
                        const exactMatches = item.children.filter((c) => c.href === activePath);
                        if (exactMatches.length > 0) return false;
                        // For patterns like /admin/transactions/123
                        return activePath.startsWith(child.href + '/');
                    })(),
                })),
            };
        }
        return {
            ...item,
            active: item.href === activePath,
        };
    });
}
