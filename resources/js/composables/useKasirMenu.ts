export interface MenuItem {
    name: string;
    href?: string;
    icon: string;
    active?: boolean;
    children?: MenuItem[];
}

export function useKasirMenuItems(): MenuItem[] {
    return [
        {
            name: 'Dashboard',
            href: '/kasir',
            icon: 'fas fa-tachometer-alt',
        },
        {
            name: 'Point of Sale',
            icon: 'fas fa-cash-register',
            href: '/kasir/pos',
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
            name: 'Pelanggan',
            href: '/kasir/customers',
            icon: 'fas fa-users',
        },
        {
            name: 'Profile',
            href: '/kasir/profile',
            icon: 'fas fa-user-circle',
        },
    ];
}

export function setActiveMenuItem(menuItems: MenuItem[], activeHref: string): MenuItem[] {
    return menuItems.map((item) => {
        if (item.children) {
            const updatedChildren = item.children.map((child) => ({
                ...child,
                active: child.href === activeHref,
            }));

            return {
                ...item,
                children: updatedChildren,
                active: updatedChildren.some((child) => child.active),
            };
        }

        return {
            ...item,
            active: item.href === activeHref,
        };
    });
}
