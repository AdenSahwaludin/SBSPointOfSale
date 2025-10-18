import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

type AnyObject = Record<string, any>;

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedData<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: PaginationLink[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

interface StatsSummary {
  total_transaksi: number;
  total_lunas: number;
  total_menunggu: number;
  total_batal: number;
  total_nilai: number;
}

interface UseTransactionsListOptions {
  baseUrl: string; // '/kasir/transactions' or '/kasir/transactions/today'
  withDateRange?: boolean;
}

export function useTransactionsList<T extends AnyObject>(
  props: { transaksi: PaginatedData<T>; stats: StatsSummary; filters?: AnyObject },
  options: UseTransactionsListOptions,
) {
  const withDateRange = !!options.withDateRange;

  // State
  const perPage = ref<number>(props.transaksi.per_page);
  const searchQuery = ref<string>(props.filters?.search || '');
  const selectedMetodeBayar = ref<string>(props.filters?.metode_bayar || 'all');
  const showFilters = ref<boolean>(false);
  const selectedTransaksi = ref<T | null>(null);
  const showDetailModal = ref<boolean>(false);
  const activeStatsTab = ref<string>(props.filters?.status || 'total_transaksi');

  // Date range only for riwayat (Index)
  const getDefault7DaysAgo = () => {
    const today = new Date();
    const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    return sevenDaysAgo.toISOString().split('T')[0];
  };
  const startDate = ref<string | undefined>(withDateRange ? props.filters?.start_date || getDefault7DaysAgo() : undefined);
  const endDate = ref<string | undefined>(withDateRange ? props.filters?.end_date || new Date().toISOString().split('T')[0] : undefined);

  // Computed
  const displayedTransaksi = computed(() => props.transaksi.data);

  const filteredTotalNilai = computed<number>(() => {
    // Sum only visible rows; coerce to numbers if formatted strings
    return displayedTransaksi.value.reduce((sum: number, t: any) => {
      const raw = t?.total;
      const value = typeof raw === 'number' ? raw : parseInt(String(raw).replace(/[^0-9-]/g, ''), 10) || 0;
      return sum + value;
    }, 0);
  });

  const formatCurrency = (amount: number): string => 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);

  const statsTabsData = computed(() => {
    // Count transaksi per status dari displayed table
    const counts = {
      total_transaksi: displayedTransaksi.value.length,
      total_lunas: displayedTransaksi.value.filter((t) => t.status_pembayaran === 'LUNAS').length,
      total_menunggu: displayedTransaksi.value.filter((t) => t.status_pembayaran === 'MENUNGGU').length,
      total_batal: displayedTransaksi.value.filter((t) => t.status_pembayaran === 'BATAL').length,
    };

    return [
      {
        id: 'total_transaksi',
        label: 'Semua',
        value: counts.total_transaksi,
        icon: 'fas fa-receipt',
        activeClass: 'bg-blue-50 text-blue-700',
        iconActiveClass: 'text-blue-600',
        iconInactiveClass: 'text-gray-500',
      },
      {
        id: 'total_lunas',
        label: 'Lunas',
        value: counts.total_lunas,
        icon: 'fas fa-check-circle',
        activeClass: 'bg-green-50 text-green-700',
        iconActiveClass: 'text-green-600',
        iconInactiveClass: 'text-gray-500',
      },
      {
        id: 'total_menunggu',
        label: 'Belum Bayar',
        value: counts.total_menunggu,
        icon: 'fas fa-clock',
        activeClass: 'bg-yellow-50 text-yellow-700',
        iconActiveClass: 'text-yellow-600',
        iconInactiveClass: 'text-gray-500',
      },
      {
        id: 'total_batal',
        label: 'Batal',
        value: counts.total_batal,
        icon: 'fas fa-ban',
        activeClass: 'bg-red-50 text-red-700',
        iconActiveClass: 'text-red-600',
        iconInactiveClass: 'text-gray-500',
      },
      {
        id: 'total_nilai',
        label: 'Total Nilai',
        value: formatCurrency(filteredTotalNilai.value),
        icon: 'fas fa-dollar-sign',
        activeClass: 'bg-emerald-50 text-emerald-700',
        iconActiveClass: 'text-emerald-600',
        iconInactiveClass: 'text-gray-500',
      },
    ];
  });

  // Methods
  const handleSearch = () => performSearch();

  const performSearch = () => {
    const statusMap: Record<string, string | undefined> = {
      total_transaksi: undefined,
      total_lunas: 'LUNAS',
      total_menunggu: 'MENUNGGU',
      total_batal: 'BATAL',
      total_nilai: undefined,
    };

    const params: AnyObject = {
      search: searchQuery.value,
      status: statusMap[activeStatsTab.value],
      metode_bayar: selectedMetodeBayar.value !== 'all' ? selectedMetodeBayar.value : undefined,
      per_page: perPage.value,
    };

    if (withDateRange) {
      params.start_date = startDate.value || undefined;
      params.end_date = endDate.value || undefined;
    }

    router.get(options.baseUrl, params, { preserveState: true, preserveScroll: true });
  };

  const changePerPage = () => {
    const statusMap: Record<string, string | undefined> = {
      total_transaksi: undefined,
      total_lunas: 'LUNAS',
      total_menunggu: 'MENUNGGU',
      total_batal: 'BATAL',
      total_nilai: undefined,
    };

    const params: AnyObject = {
      search: searchQuery.value,
      status: statusMap[activeStatsTab.value],
      metode_bayar: selectedMetodeBayar.value !== 'all' ? selectedMetodeBayar.value : undefined,
      per_page: perPage.value,
      page: 1,
    };

    if (withDateRange) {
      params.start_date = startDate.value || undefined;
      params.end_date = endDate.value || undefined;
    }

    router.get(options.baseUrl, params, { preserveState: true });
  };

  const goToPage = (url: string | null) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
  };

  const clearSearch = () => {
    searchQuery.value = '';
    performSearch();
  };

  const clearFilters = () => {
    selectedMetodeBayar.value = 'all';
    activeStatsTab.value = 'total_transaksi';
    if (withDateRange) {
      startDate.value = getDefault7DaysAgo();
      endDate.value = new Date().toISOString().split('T')[0];
    }
    performSearch();
  };

  const clearAll = () => {
    searchQuery.value = '';
    selectedMetodeBayar.value = 'all';
    activeStatsTab.value = 'total_transaksi';
    if (withDateRange) {
      startDate.value = getDefault7DaysAgo();
      endDate.value = new Date().toISOString().split('T')[0];
    }
    performSearch();
  };

  const viewDetail = (trx: T) => {
    selectedTransaksi.value = trx;
    showDetailModal.value = true;
  };

  const closeDetailModal = () => {
    showDetailModal.value = false;
    selectedTransaksi.value = null;
  };

  const getStatusBadgeClass = (status: string): string => {
    switch (status) {
      case 'LUNAS':
        return 'bg-green-100 text-green-800';
      case 'MENUNGGU':
        return 'bg-yellow-100 text-yellow-800';
      case 'BATAL':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return {
    // state
    perPage,
    searchQuery,
    selectedMetodeBayar,
    showFilters,
    selectedTransaksi,
    showDetailModal,
    activeStatsTab,
    startDate,
    endDate,

    // computed
    displayedTransaksi,
    filteredTotalNilai,
    statsTabsData,
    formatCurrency,

    // methods
    handleSearch,
    performSearch,
    changePerPage,
    goToPage,
    clearSearch,
    clearFilters,
    clearAll,
    viewDetail,
    closeDetailModal,
    getStatusBadgeClass,
  };
}

