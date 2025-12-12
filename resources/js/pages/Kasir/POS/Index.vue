<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import CreditContractModal from '@/components/CreditContractModal.vue';
import TransactionConfirmationModal from '@/components/TransactionConfirmationModal.vue';
import { useCurrencyFormat } from '@/composables/useCurrencyFormat';
import { useDebouncedSearch } from '@/composables/useDebouncedSearch';
import { setActiveMenuItem, useKasirMenuItems } from '@/composables/useKasirMenu';
import { useNotifications } from '@/composables/useNotifications';
import BaseLayout from '@/pages/Layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface Kategori {
    id_kategori: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    barcode?: string;
    nama: string;
    harga: number; // harga per unit/pieces
    harga_pack?: number; // harga grosir/pack (≥3 pcs atau kemasan besar)
    stok: number;
    satuan: string; // pcs, karton, pack
    isi_per_pack: number; // berapa pcs dalam 1 karton/pack
    kategori: Kategori;
}

interface Pelanggan {
    id_pelanggan: string;
    nama: string;
    email?: string;
    telepon?: string;
    credit_limit?: number; // sisa limit tersedia
}

interface CartItem {
    id_produk: string;
    nama: string;
    harga_satuan: number;
    jumlah: number;
    mode_qty: 'unit' | 'pack';
    subtotal: number;
    stok: number;
    satuan: string;
    isi_per_pack: number;
    diskon_item?: number;
    base_harga_unit?: number;
    harga_pack?: number; // untuk recalculate saat qty berubah
}

const props = defineProps<{
    produk: Produk[];
    kategori: Kategori[];
    pelanggan: Pelanggan[];
    metodeBayar: Record<string, string>;
}>();

const { addNotification } = useNotifications();

// Currency formatting
const { formatNumber, parseNumber } = useCurrencyFormat();

// State management
const selectedKategori = ref<number | null>(null);
const searchQuery = ref('');
const barcodeInput = ref('');
const cart = ref<CartItem[]>([]);
const selectedPelanggan = ref<string>('P001'); // Default to 'Umum'
const pelangganSearchQuery = ref('');
const showPelangganDropdown = ref(false);
const activePelangganIndex = ref(0);
const metodeBayar = ref<string>('TUNAI');
const jumlahBayar = ref<number>(0);
const jumlahBayarDisplay = ref<string>(''); // For formatted display
const dpBayar = ref<number>(0); // DP untuk kredit
const dpBayarDisplay = ref<string>(''); // For formatted display
const diskonGlobal = ref<number>(0);
const pajakRate = ref<number>(0);
const showCustomerInfo = ref<boolean>(false);
const showCustomerInfoModal = ref<boolean>(false);
const {
    query: productQuery,
    isSearching: isSearchingProducts,
    results: productResults,
    onInput: onProductSearchInput,
    clear: clearProductSearch,
    trigger: triggerProductSearch,
} = useDebouncedSearch<Produk>({
    wait: 300,
    minLength: 2,
    search: async (q: string) => {
        const response = await fetch(`/kasir/pos/search-produk?q=${encodeURIComponent(q)}`);
        if (!response.ok) throw new Error('Search failed');
        return (await response.json()) as Produk[];
    },
});
const searchResults = productResults; // alias
const isSearching = isSearchingProducts; // alias

// Modal confirmation
const showConfirmationModal = ref(false);
const showCreditModal = ref(false);
const pendingTransaction = ref<any>(null);
const creditConfig = ref<null | {
    tenor_bulan: number;
    bunga_persen: number;
    cicilan_bulanan: number;
    mulai_kontrak: string;
    jadwal: Array<{ periode_ke: number; jatuh_tempo: string; jumlah_tagihan: number }>;
}>(null);

// Form
const transactionForm = useForm({
    id_pelanggan: 'P001',
    items: [] as any[],
    metode_bayar: 'TUNAI',
    subtotal: 0,
    diskon: 0,
    pajak: 0,
    total: 0,
    jumlah_bayar: 0,
    dp: 0,
});

// Computed
const filteredProduk = computed(() => {
    // Jika ada search query, gunakan hasil dari API
    if (searchQuery.value && searchResults.value.length > 0) {
        return searchResults.value;
    }

    // Jika tidak ada search, filter by kategori dari props.produk
    let filtered = props.produk;

    if (selectedKategori.value) {
        filtered = filtered.filter((p) => p.kategori.id_kategori === selectedKategori.value);
    }

    return filtered;
});

// Selected customer and credit info
const selectedCustomer = computed<Pelanggan | undefined>(() => props.pelanggan.find((p) => p.id_pelanggan === selectedPelanggan.value));
const availableCredit = computed<number>(() => Number(selectedCustomer.value?.credit_limit || 0));
const minimalDp = computed<number>(() => {
    if (metodeBayar.value !== 'KREDIT') return 0;
    const t = Number(total.value || 0);
    const avail = availableCredit.value;
    return Math.max(0, t - avail);
});

const filteredPelanggan = computed(() => {
    const q = pelangganSearchQuery.value.trim().toLowerCase();
    if (!q) return props.pelanggan;
    return props.pelanggan.filter((p) => {
        const name = (p.nama || '').toLowerCase();
        const email = (p.email || '').toLowerCase();
        const telp = (p.telepon || '').toLowerCase();
        return name.includes(q) || email.includes(q) || telp.includes(q);
    });
});

function openPelangganDropdown() {
    showPelangganDropdown.value = true;
    // Set active index to selected pelanggan if visible, else 0
    const idx = filteredPelanggan.value.findIndex((p) => p.id_pelanggan === selectedPelanggan.value);
    activePelangganIndex.value = idx >= 0 ? idx : 0;
}

function handlePelangganKeydown(e: KeyboardEvent) {
    if (!showPelangganDropdown.value) return;
    const len = filteredPelanggan.value.length;
    if (len === 0) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activePelangganIndex.value = (activePelangganIndex.value + 1) % len;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activePelangganIndex.value = (activePelangganIndex.value - 1 + len) % len;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const p = filteredPelanggan.value[activePelangganIndex.value];
        if (p) {
            selectedPelanggan.value = p.id_pelanggan;
            pelangganSearchQuery.value = '';
            showPelangganDropdown.value = false;
        }
    } else if (e.key === 'Escape') {
        e.preventDefault();
        showPelangganDropdown.value = false;
    }
}

function closePelangganDropdownDelayed() {
    setTimeout(() => {
        showPelangganDropdown.value = false;
    }, 200);
}

function openCustomerInfoModal() {
    showCustomerInfoModal.value = true;
}

const subtotal = computed(() => {
    const result = cart.value.reduce((sum, item) => Number(sum) + Number(item.subtotal || 0), 0);
    // Debug logging
    console.log('Subtotal calculation:', {
        cart_items: cart.value.map((item) => ({
            nama: item.nama,
            harga_satuan: item.harga_satuan,
            jumlah: item.jumlah,
            subtotal: item.subtotal,
        })),
        total_subtotal: result,
    });
    return result;
});

const diskon = computed(() => {
    return (subtotal.value * diskonGlobal.value) / 100;
});

const pajak = computed(() => {
    return ((subtotal.value - diskon.value) * pajakRate.value) / 100;
});

const total = computed(() => {
    return subtotal.value - diskon.value + pajak.value;
});

const kembalian = computed(() => {
    if (metodeBayar.value === 'TUNAI' && jumlahBayar.value > 0) {
        return jumlahBayar.value - total.value;
    }
    return 0;
});

const totalItems = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.jumlah, 0);
});

// Methods
function toNumber(val: unknown): number {
    if (typeof val === 'number') return val;
    if (typeof val === 'string') {
        const cleaned = val.replace(/[^0-9-]/g, '');
        return cleaned ? parseInt(cleaned, 10) : 0;
    }
    return 0;
}

function getEffectiveUnitPiecePrice(produk: Produk, qtyPieces: number): number {
    // Jika produk punya harga_pack dan qty >= 3, gunakan harga_pack
    if (produk.harga_pack && qtyPieces >= 3) {
        return toNumber(produk.harga_pack);
    }

    // Fallback ke harga normal
    return toNumber(produk.harga);
}

function addToCart(produk: Produk, mode: 'unit' | 'pack' = 'unit') {
    const existingItemIndex = cart.value.findIndex((item) => item.id_produk === produk.id_produk && item.mode_qty === mode);

    // Untuk produk satuan karton/pack, paksa mode = 'pack'
    if ((produk.satuan === 'karton' || produk.satuan === 'pack') && mode === 'unit') {
        mode = 'pack';
    }

    const isiPerPack = Math.max(1, toNumber(produk.isi_per_pack));
    const initialQty = 1;
    const qtyPieces = mode === 'pack' ? initialQty * isiPerPack : initialQty;

    // Tentukan harga satuan berdasarkan mode
    let hargaSatuan: number;
    if (produk.satuan === 'karton' || produk.satuan === 'pack') {
        // Produk kemasan besar: langsung pakai harga produk (sudah harga per karton/pack)
        hargaSatuan = toNumber(produk.harga);
    } else if (mode === 'pack') {
        // Mode pack untuk produk satuan pcs: pakai harga_pack jika ada, atau hitung dari harga × isi_per_pack
        hargaSatuan = produk.harga_pack ? toNumber(produk.harga_pack) * isiPerPack : toNumber(produk.harga) * isiPerPack;
    } else {
        // Mode unit untuk produk pcs: cek apakah qty >= 3 untuk dapat harga_pack
        hargaSatuan = getEffectiveUnitPiecePrice(produk, qtyPieces);
    }

    // Debug logging
    console.log('Adding to cart:', {
        produk: produk.nama,
        mode,
        satuan: produk.satuan,
        harga_produk: produk.harga,
        harga_pack: produk.harga_pack,
        harga_satuan: hargaSatuan,
        isi_per_pack: produk.isi_per_pack,
        qty_pieces: qtyPieces,
    });

    if (existingItemIndex !== -1) {
        const item = cart.value[existingItemIndex];

        // Hitung maxQty berdasarkan satuan produk
        let maxQty: number;
        if (produk.satuan === 'karton' || produk.satuan === 'pack') {
            // Produk kemasan besar: stok langsung (sudah dalam unit karton/pack)
            maxQty = produk.stok;
        } else if (mode === 'pack') {
            // Produk pcs mode pack: stok dibagi isi_per_pack
            maxQty = Math.floor(produk.stok / isiPerPack);
        } else {
            // Produk pcs mode unit: stok langsung
            maxQty = produk.stok;
        }

        if (item.jumlah < maxQty) {
            const newJumlah = item.jumlah + 1;
            const totalPieces = mode === 'pack' ? newJumlah * isiPerPack : newJumlah;

            // Update harga satuan jika qty berubah threshold (contoh: 2→3 pcs dapat harga_pack)
            if (produk.satuan === 'pcs' && mode === 'unit') {
                item.harga_satuan = getEffectiveUnitPiecePrice(produk, totalPieces);
            }

            // Pastikan harga_pack tersimpan untuk recalculate
            if (!item.harga_pack && produk.harga_pack) {
                item.harga_pack = toNumber(produk.harga_pack);
            }

            item.jumlah = newJumlah;
            item.subtotal = Number(item.jumlah) * Number(item.harga_satuan) - Number(item.diskon_item || 0);
        } else {
            addNotification({
                type: 'warning',
                title: 'Stok tidak cukup!',
            });
        }
    } else {
        // Hitung maxQty berdasarkan satuan produk
        let maxQty: number;
        if (produk.satuan === 'karton' || produk.satuan === 'pack') {
            // Produk kemasan besar: stok langsung (sudah dalam unit karton/pack)
            maxQty = produk.stok;
        } else if (mode === 'pack') {
            // Produk pcs mode pack: stok dibagi isi_per_pack
            maxQty = Math.floor(produk.stok / isiPerPack);
        } else {
            // Produk pcs mode unit: stok langsung
            maxQty = produk.stok;
        }

        if (maxQty > 0) {
            cart.value.push({
                id_produk: produk.id_produk,
                nama: produk.nama,
                harga_satuan: hargaSatuan,
                jumlah: 1,
                mode_qty: mode,
                subtotal: Number(hargaSatuan),
                stok: produk.stok,
                satuan: produk.satuan,
                isi_per_pack: isiPerPack,
                base_harga_unit: toNumber(produk.harga),
                harga_pack: produk.harga_pack ? toNumber(produk.harga_pack) : undefined,
                diskon_item: 0,
            });
        } else {
            addNotification({
                type: 'warning',
                title: 'Produk tidak tersedia!',
            });
        }
    }
}

function updateQuantity(index: number, quantity: number) {
    const item = cart.value[index];
    const isiPerPack = Math.max(1, toNumber(item.isi_per_pack));

    // Hitung maxQty berdasarkan satuan dan mode
    let maxQty: number;
    if (item.satuan === 'karton' || item.satuan === 'pack') {
        // Produk kemasan besar: stok langsung
        maxQty = item.stok;
    } else if (item.mode_qty === 'pack') {
        // Produk pcs mode pack: stok dibagi isi_per_pack
        maxQty = Math.floor(item.stok / isiPerPack);
    } else {
        // Produk pcs mode unit: stok langsung
        maxQty = item.stok;
    }

    if (quantity <= 0) {
        removeFromCart(index);
    } else if (quantity <= maxQty) {
        item.jumlah = quantity;

        // Recalculate harga_satuan jika produk pcs mode unit dan qty melewati threshold
        if (item.satuan === 'pcs' && item.mode_qty === 'unit') {
            const totalPieces = quantity;
            // Jika ada harga_pack dan qty >= 3, gunakan harga_pack
            if (item.harga_pack && totalPieces >= 3) {
                item.harga_satuan = item.harga_pack;
            } else {
                item.harga_satuan = item.base_harga_unit || item.harga_satuan;
            }
        }

        item.subtotal = item.jumlah * item.harga_satuan - (item.diskon_item || 0);
    } else {
        addNotification({
            type: 'warning',
            title: 'Jumlah melebihi stok yang tersedia!',
        });
    }
}

function removeFromCart(index: number) {
    cart.value.splice(index, 1);
}

function clearCart() {
    cart.value = [];
    jumlahBayar.value = 0;
    jumlahBayarDisplay.value = '';
    dpBayar.value = 0;
    dpBayarDisplay.value = '';
}

// Live search (debounced) using composable
function handleSearchInput() {
    productQuery.value = searchQuery.value;
    onProductSearchInput();
}

function handleBarcodeInput() {
    if (!barcodeInput.value) return;

    const code = barcodeInput.value.trim();
    // Prefer server lookup by barcode to avoid mismatch with id_produk
    fetch(`/kasir/pos/produk?barcode=${encodeURIComponent(code)}`)
        .then((res) => {
            if (!res.ok) throw new Error('Produk tidak ditemukan');
            return res.json();
        })
        .then((produk) => {
            addToCart(produk);
            barcodeInput.value = '';
        })
        .catch(() => {
            addNotification({
                type: 'error',
                title: 'Produk dengan barcode tersebut tidak ditemukan!',
            });
        });
}

function processTransaction() {
    if (cart.value.length === 0) {
        addNotification({
            type: 'warning',
            title: 'Keranjang kosong!',
        });
        return;
    }

    if (metodeBayar.value === 'TUNAI' && jumlahBayar.value < total.value) {
        addNotification({
            type: 'warning',
            title: 'Jumlah bayar kurang!',
        });
        return;
    }

    // Kredit: DP boleh 0, biarkan server validasi limit dan kondisi lainnya

    // Prepare transaction data
    const selectedPelangganObj = props.pelanggan.find((p) => p.id_pelanggan === selectedPelanggan.value);

    pendingTransaction.value = {
        pelanggan_nama: selectedPelangganObj?.nama || 'Umum',
        metode_bayar: metodeBayar.value,
        items: cart.value,
        subtotal: subtotal.value,
        diskon: diskon.value,
        pajak: pajak.value,
        total: total.value,
        jumlah_bayar: metodeBayar.value === 'TUNAI' ? jumlahBayar.value : undefined,
        dp: metodeBayar.value === 'KREDIT' ? dpBayar.value : undefined,
    };

    // If credit, collect credit contract info first
    if (metodeBayar.value === 'KREDIT') {
        showCreditModal.value = true;
        return;
    }

    // Else show confirmation modal
    showConfirmationModal.value = true;
}

function handleConfirmTransaction() {
    if (!pendingTransaction.value) return;

    const requestData = {
        id_pelanggan: selectedPelanggan.value,
        items: pendingTransaction.value.items,
        metode_bayar: pendingTransaction.value.metode_bayar,
        subtotal: pendingTransaction.value.subtotal,
        diskon: pendingTransaction.value.diskon,
        pajak: pendingTransaction.value.pajak,
        total: pendingTransaction.value.total,
        ...(pendingTransaction.value.metode_bayar === 'TUNAI' ? { jumlah_bayar: pendingTransaction.value.jumlah_bayar } : {}),
        ...(pendingTransaction.value.metode_bayar === 'KREDIT'
            ? {
                  dp: pendingTransaction.value.dp ?? 0,
                  tenor_bulan: creditConfig.value?.tenor_bulan,
                  bunga_persen: creditConfig.value?.bunga_persen,
                  cicilan_bulanan: creditConfig.value?.cicilan_bulanan,
                  mulai_kontrak: creditConfig.value?.mulai_kontrak,
              }
            : {}),
    } as Record<string, any>;

    fetch('/kasir/pos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    })
        .then(async (response) => {
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                const msg = data?.message || 'Gagal menyimpan transaksi!';
                throw new Error(msg);
            }
            return data;
        })
        .then((data) => {
            if (data.success) {
                addNotification({
                    type: 'success',
                    title: 'Transaksi berhasil disimpan!',
                });

                // Close modal and clear data
                showConfirmationModal.value = false;
                pendingTransaction.value = null;
                creditConfig.value = null;
                clearCart();
                resetForm();
            } else {
                addNotification({
                    type: 'error',
                    title: data.message || 'Gagal menyimpan transaksi!',
                });
            }
        })
        .catch((error) => {
            const msg = error?.message || 'Gagal menyimpan transaksi!';
            const isCreditLimit = /kredit\s*limit/i.test(msg);
            addNotification({
                type: isCreditLimit ? 'warning' : 'error',
                title: msg,
            });
            console.error('Transaction error:', error);
        });
}

function handleCancelTransaction() {
    showConfirmationModal.value = false;
    pendingTransaction.value = null;
    addNotification({
        type: 'info',
        title: 'Transaksi dibatalkan',
    });
}

function handlePrintReceipt() {
    if (!pendingTransaction.value) return;

    // Store transaction data for receipt before clearing
    const transactionForReceipt = { ...pendingTransaction.value };

    // First, submit the transaction like handleConfirmTransaction
    const requestData = {
        id_pelanggan: selectedPelanggan.value,
        items: pendingTransaction.value.items,
        metode_bayar: pendingTransaction.value.metode_bayar,
        subtotal: pendingTransaction.value.subtotal,
        diskon: pendingTransaction.value.diskon,
        pajak: pendingTransaction.value.pajak,
        total: pendingTransaction.value.total,
        ...(pendingTransaction.value.metode_bayar === 'TUNAI' ? { jumlah_bayar: pendingTransaction.value.jumlah_bayar } : {}),
        ...(pendingTransaction.value.metode_bayar === 'KREDIT'
            ? {
                  dp: pendingTransaction.value.dp ?? 0,
                  tenor_bulan: creditConfig.value?.tenor_bulan,
                  bunga_persen: creditConfig.value?.bunga_persen,
                  cicilan_bulanan: creditConfig.value?.cicilan_bulanan,
                  mulai_kontrak: creditConfig.value?.mulai_kontrak,
              }
            : {}),
    } as Record<string, any>;

    fetch('/kasir/pos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    })
        .then(async (response) => {
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                const msg = data?.message || 'Gagal menyimpan transaksi!';
                throw new Error(msg);
            }
            return data;
        })
        .then((data) => {
            if (data.success) {
                addNotification({
                    type: 'success',
                    title: 'Transaksi berhasil disimpan!',
                });

                // Close modal and clear data
                showConfirmationModal.value = false;
                pendingTransaction.value = null;
                creditConfig.value = null;
                clearCart();
                resetForm();

                // Then open receipt for printing
                const receiptWindow = window.open('', '_blank');
                if (!receiptWindow) {
                    addNotification({
                        type: 'warning',
                        title: 'Popup blocker mencegah pencetakan',
                    });
                    return;
                }

                const receiptHTML = generateReceiptHTML(transactionForReceipt);
                receiptWindow.document.write(receiptHTML);
                receiptWindow.document.close();

                // Auto print after content loads
                receiptWindow.onload = () => {
                    receiptWindow.print();
                };
            } else {
                addNotification({
                    type: 'error',
                    title: data.message || 'Gagal menyimpan transaksi!',
                });
            }
        })
        .catch((error) => {
            const msg = error?.message || 'Gagal menyimpan transaksi!';
            const isCreditLimit = /kredit\s*limit/i.test(msg);
            addNotification({
                type: isCreditLimit ? 'warning' : 'error',
                title: msg,
            });
            console.error('Transaction error:', error);
        });
}

function generateReceiptHTML(transaction: any): string {
    const itemsHTML = transaction.items
        .map((item: any) => {
            const qty = Number(item.jumlah || item.quantity || 0);
            const unitPrice = Number(item.harga_satuan || item.harga_jual || 0);
            const subtotal = Number(item.subtotal != null ? item.subtotal : unitPrice * qty);
            return `
                <tr>
                    <td style="padding: 4px 0; text-align: left;">${item.nama}</td>
                    <td style="padding: 4px 0; text-align: right;">${qty}</td>
                    <td style="padding: 4px 0; text-align: right;">Rp ${unitPrice.toLocaleString('id-ID')}</td>
                    <td style="padding: 4px 0; text-align: right;">Rp ${subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        })
        .join('');

    const kembalian = transaction.metode_bayar === 'TUNAI' ? Number(transaction.jumlah_bayar || 0) - Number(transaction.total || 0) : 0;

    return `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Struk Transaksi</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    max-width: 300px;
                    margin: 0;
                    padding: 10px;
                    line-height: 1.4;
                }
                .header {
                    text-align: center;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .divider {
                    border-bottom: 1px dashed #000;
                    margin: 5px 0;
                }
                table {
                    width: 100%;
                    margin: 10px 0;
                }
                td {
                    padding: 2px 0;
                    font-size: 12px;
                }
                .label {
                    text-align: left;
                    width: 40%;
                }
                .value {
                    text-align: right;
                    width: 60%;
                }
                .total {
                    font-weight: bold;
                    font-size: 14px;
                }
                .footer {
                    text-align: center;
                    font-size: 11px;
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                === STRUK TRANSAKSI ===
            </div>
            <div class="divider"></div>
            
            <table>
                <tr>
                    <td class="label">Pelanggan:</td>
                    <td class="value">${transaction.pelanggan_nama}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal:</td>
                    <td class="value">${new Date().toLocaleString('id-ID')}</td>
                </tr>
                <tr>
                    <td class="label">Metode:</td>
                    <td class="value">${transaction.metode_bayar}</td>
                </tr>
            </table>
            
            <div class="divider"></div>
            <strong>DETAIL BARANG</strong>
            <div class="divider"></div>
            
            <table>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="text-align: left; padding: 4px 0;">Produk</th>
                    <th style="text-align: right; padding: 4px 0;">Qty</th>
                    <th style="text-align: right; padding: 4px 0;">Harga</th>
                    <th style="text-align: right; padding: 4px 0;">Total</th>
                </tr>
                ${itemsHTML}
            </table>
            
            <div class="divider"></div>
            
            <table>
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">Rp ${Number(transaction.subtotal || 0).toLocaleString('id-ID')}</td>
                </tr>
                ${
                    Number(transaction.diskon || 0) > 0
                        ? `
                <tr>
                    <td class="label">Diskon:</td>
                    <td class="value" style="color: green;">-Rp ${Number(transaction.diskon).toLocaleString('id-ID')}</td>
                </tr>
                `
                        : ''
                }
                ${
                    Number(transaction.pajak || 0) > 0
                        ? `
                <tr>
                    <td class="label">Pajak:</td>
                    <td class="value">Rp ${Number(transaction.pajak).toLocaleString('id-ID')}</td>
                </tr>
                `
                        : ''
                }
                <tr class="total">
                    <td class="label">TOTAL:</td>
                    <td class="value">Rp ${Number(transaction.total || 0).toLocaleString('id-ID')}</td>
                </tr>
                ${
                    transaction.metode_bayar === 'KREDIT'
                        ? `
                <tr>
                    <td class="label">DP:</td>
                    <td class="value">Rp ${Number(transaction.dp || 0).toLocaleString('id-ID')}</td>
                </tr>
                <tr>
                    <td class="label">Sisa:</td>
                    <td class="value">Rp ${Math.max(0, Number(transaction.total || 0) - Number(transaction.dp || 0)).toLocaleString('id-ID')}</td>
                </tr>
                `
                        : ''
                }
                ${
                    transaction.metode_bayar === 'TUNAI'
                        ? `
                <tr>
                    <td class="label">Dibayar:</td>
                    <td class="value">Rp ${Number(transaction.jumlah_bayar || 0).toLocaleString('id-ID')}</td>
                </tr>
                ${
                    kembalian > 0
                        ? `
                <tr class="total">
                    <td class="label">Kembalian:</td>
                    <td class="value">Rp ${Number(kembalian).toLocaleString('id-ID')}</td>
                </tr>`
                        : ''
                }
                `
                        : ''
                }
            </table>
            
            <div class="divider"></div>
            <div class="footer">
                Terima kasih atas pembelian Anda
                <br>Semoga puas dengan layanan kami
            </div>
        </body>
        </html>
    `;
}

function resetForm() {
    selectedPelanggan.value = 'P001';
    pelangganSearchQuery.value = '';
    metodeBayar.value = 'TUNAI';
    diskonGlobal.value = 0;
    jumlahBayar.value = 0;
    jumlahBayarDisplay.value = '';
    dpBayar.value = 0;
    dpBayarDisplay.value = '';
}

// Format currency
function formatCurrency(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Auto focus barcode input on mount
onMounted(() => {
    const barcodeEl = document.getElementById('barcode-input') as HTMLInputElement;
    if (barcodeEl) {
        barcodeEl.focus();
    }
});

// Keyboard shortcuts
onMounted(() => {
    const handleKeydown = (e: KeyboardEvent) => {
        // F1 - Focus barcode input
        if (e.key === 'F1') {
            e.preventDefault();
            const barcodeEl = document.getElementById('barcode-input') as HTMLInputElement;
            if (barcodeEl) {
                barcodeEl.focus();
            }
        }

        // F2 - Process transaction
        if (e.key === 'F2') {
            e.preventDefault();
            processTransaction();
        }

        // F3 - Clear cart
        if (e.key === 'F3') {
            e.preventDefault();
            clearCart();
        }
    };

    document.addEventListener('keydown', handleKeydown);

    return () => {
        document.removeEventListener('keydown', handleKeydown);
    };
});

const kasirMenuItems = setActiveMenuItem(useKasirMenuItems(), '/kasir/pos');
</script>

<template>
    <Head title="Point of Sale - Kasir" />

    <BaseLayout :menuItems="kasirMenuItems" userRole="kasir">
        <template #header> Point of Sale </template>

        <div class="flex h-[calc(100vh-6rem)] gap-6">
            <!-- Left Panel - Products -->
            <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <!-- Header Controls -->
                <div class="border-b border-gray-100 p-6">
                    <!-- Barcode Scanner -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700"> <i class="fas fa-barcode mr-2"></i>Scan Barcode (F1) </label>
                        <input
                            id="barcode-input"
                            v-model="barcodeInput"
                            @keyup.enter="handleBarcodeInput"
                            type="text"
                            placeholder="Scan atau ketik barcode..."
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- Search and Filter -->
                    <div class="flex gap-4">
                        <div class="relative flex-1">
                            <input
                                v-model="searchQuery"
                                @input="handleSearchInput"
                                type="text"
                                placeholder="Cari nama, SKU, atau barcode produk..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            />
                            <!-- Loading indicator -->
                            <div v-if="isSearching" class="absolute top-1/2 right-3 -translate-y-1/2">
                                <svg class="h-5 w-5 animate-spin text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </div>
                            <!-- Search icon -->
                            <div v-else class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <select
                            v-model="selectedKategori"
                            @change="
                                searchQuery = '';
                                clearProductSearch();
                            "
                            class="rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option :value="null">Semua Kategori</option>
                            <option v-for="kategori in props.kategori" :key="kategori.id_kategori" :value="kategori.id_kategori">
                                {{ kategori.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Search info -->
                    <div v-if="searchQuery && !isSearching" class="mt-3 text-sm text-gray-600">
                        <span v-if="filteredProduk.length > 0">
                            Ditemukan {{ filteredProduk.length }} produk untuk "<strong>{{ searchQuery }}</strong
                            >"
                        </span>
                        <span v-else class="text-orange-600">
                            Tidak ada produk ditemukan untuk "<strong>{{ searchQuery }}</strong
                            >"
                        </span>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Loading state -->
                    <div v-if="isSearching" class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <svg
                                class="mx-auto h-12 w-12 animate-spin text-emerald-500"
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
                            <p class="mt-4 text-gray-600">Mencari produk...</p>
                        </div>
                    </div>

                    <!-- Products grid -->
                    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-1 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="produk in filteredProduk"
                            :key="produk.id_produk"
                            class="cursor-pointer rounded-xl border-2 border-transparent bg-gray-50 p-4 transition-all hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-md"
                            @click="addToCart(produk)"
                        >
                            <div class="text-center">
                                <div class="mb-2 flex items-center justify-center">
                                    <span class="rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-600">
                                        {{ produk.kategori.nama }}
                                    </span>
                                </div>
                                <h3 class="mb-2 line-clamp-2 min-h-[2.5rem] font-semibold text-gray-900">{{ produk.nama }}</h3>
                                <p class="mb-3 text-xl font-bold text-emerald-600">{{ formatCurrency(produk.harga) }}</p>

                                <!-- Stock Info -->
                                <div class="mb-3 flex items-center justify-center gap-2">
                                    <span class="text-sm text-gray-500">Stok:</span>
                                    <span
                                        :class="[
                                            'text-sm font-semibold',
                                            produk.stok === 0
                                                ? 'text-red-600'
                                                : (produk.satuan === 'pcs' && produk.stok < 50) ||
                                                    ((produk.satuan === 'karton' || produk.satuan === 'pack') && produk.stok < 5)
                                                  ? 'text-orange-600'
                                                  : 'text-green-600',
                                        ]"
                                    >
                                        {{ produk.stok }} {{ produk.satuan }}
                                    </span>
                                </div>

                                <!-- Satuan Info -->
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-600">
                                    <template v-if="produk.satuan === 'karton' || produk.satuan === 'pack'">
                                        <i class="fas fa-box text-blue-500"></i>
                                        <span class="capitalize">{{ produk.satuan }}</span>
                                    </template>
                                    <template v-else>
                                        <i class="fas fa-cube text-emerald-500"></i>
                                        <span>Unit (pcs)</span>
                                        <span v-if="produk.isi_per_pack > 1" class="text-gray-400">•</span>
                                        <template v-if="produk.isi_per_pack > 1">
                                            <i class="fas fa-box text-blue-500"></i>
                                            <span>Pack ({{ produk.isi_per_pack }} pcs)</span>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart & Checkout -->
            <div class="flex w-[28rem] flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <!-- Cart Header -->
                <div class="border-b border-gray-100 px-6 pt-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">Keranjang Belanja</h2>

                    <!-- Customer Selection -->
                    <div class="relative mb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-full border border-emerald-200 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                @click="openCustomerInfoModal()"
                                title="Tampilkan informasi pelanggan"
                            >
                                <i class="fas fa-info-circle"></i>
                                <span class="ml-1 hidden sm:inline">Info</span>
                            </button>
                        </div>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="pelangganSearchQuery"
                                @focus="openPelangganDropdown()"
                                @blur="closePelangganDropdownDelayed()"
                                @keydown="handlePelangganKeydown"
                                :placeholder="props.pelanggan.find((p) => p.id_pelanggan === selectedPelanggan)?.nama || 'Cari pelanggan...'"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            />
                            <button
                                v-if="pelangganSearchQuery"
                                @click="
                                    pelangganSearchQuery = '';
                                    openPelangganDropdown();
                                "
                                type="button"
                                class="absolute top-1/2 right-8 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                aria-label="Clear"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                            <i class="fas fa-search absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"></i>

                            <!-- Dropdown -->
                            <div
                                v-if="showPelangganDropdown"
                                class="absolute top-full right-0 left-0 z-10 mt-1 max-h-48 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg"
                            >
                                <div v-if="filteredPelanggan.length === 0" class="px-3 py-2 text-sm text-gray-500">Tidak ada pelanggan ditemukan</div>
                                <button
                                    v-for="(pelanggan, idx) in filteredPelanggan"
                                    :key="pelanggan.id_pelanggan"
                                    @click="
                                        selectedPelanggan = pelanggan.id_pelanggan;
                                        pelangganSearchQuery = '';
                                        showPelangganDropdown = false;
                                    "
                                    :class="[
                                        'w-full px-3 py-2 text-left text-sm transition-colors hover:bg-emerald-50',
                                        selectedPelanggan === pelanggan.id_pelanggan || activePelangganIndex === idx
                                            ? 'bg-emerald-100 font-semibold text-emerald-700'
                                            : 'text-gray-900',
                                    ]"
                                >
                                    <div class="font-medium">{{ pelanggan.nama }}</div>
                                    <div class="text-xs text-gray-500" v-if="pelanggan.email || pelanggan.telepon">
                                        {{ pelanggan.email || pelanggan.telepon }}
                                    </div>
                                </button>
                            </div>
                        </div>
                        <!-- Customer Info (collapsible) -->
                        <Transition name="collapse">
                            <div
                                v-if="selectedCustomer && showCustomerInfo"
                                id="customer-info-panel"
                                class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ selectedCustomer.nama }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ selectedCustomer.id_pelanggan }}</div>
                                        <div v-if="selectedCustomer.telepon || selectedCustomer.email" class="mt-1 text-xs text-gray-600">
                                            {{ selectedCustomer.telepon || selectedCustomer.email }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">Limit Tersedia</div>
                                        <div class="font-bold text-emerald-600">{{ formatCurrency(availableCredit) }}</div>
                                    </div>
                                </div>
                                <div v-if="metodeBayar === 'KREDIT'" class="mt-2 rounded bg-amber-50 p-2 text-xs text-amber-800">
                                    <div>
                                        Minimal DP jika melebihi limit:
                                        <span class="font-semibold">{{ formatCurrency(minimalDp) }}</span>
                                    </div>
                                    <div v-if="minimalDp > 0" class="mt-1">Transaksi melampaui limit, tambahkan DP minimal tersebut.</div>
                                    <div v-else class="mt-1">DP tidak diperlukan berdasarkan limit tersedia.</div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto px-6 pb-6">
                    <div v-if="cart.length === 0" class="py-8 text-center text-gray-500">
                        <i class="fas fa-shopping-cart mb-4 text-4xl"></i>
                        <p>Keranjang kosong</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="(item, index) in cart"
                            :key="`${item.id_produk}-${item.mode_qty}`"
                            class="rounded-lg border border-emerald-200 bg-emerald-50 p-4"
                        >
                            <div class="mb-2 flex items-start justify-between">
                                <h4 class="line-clamp-2 text-sm font-medium text-gray-900">{{ item.nama }}</h4>
                                <button @click="removeFromCart(index)" class="text-xs text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ formatCurrency(item.harga_satuan) }} / {{ item.mode_qty }}
                                    <span v-if="item.mode_qty === 'pack'">({{ item.isi_per_pack }} {{ item.satuan }})</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="updateQuantity(index, item.jumlah - 1)"
                                        class="flex h-6 w-6 items-center justify-center rounded bg-emerald-100 text-xs hover:bg-emerald-200"
                                    >
                                        -
                                    </button>
                                    <input
                                        :value="item.jumlah"
                                        @input="updateQuantity(index, parseInt(($event.target as HTMLInputElement).value) || 0)"
                                        type="number"
                                        min="1"
                                        class="w-12 rounded border text-center text-xs"
                                    />
                                    <button
                                        @click="updateQuantity(index, item.jumlah + 1)"
                                        class="flex h-6 w-6 items-center justify-center rounded bg-emerald-100 text-xs hover:bg-emerald-200"
                                    >
                                        +
                                    </button>
                                </div>
                                <span class="text-sm font-bold text-emerald-600">
                                    {{ formatCurrency(item.subtotal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Section -->
                <div class="space-y-4 border-t border-gray-100 p-6">
                    <!-- Discount -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Diskon (%)</span>
                        <input
                            v-model.number="diskonGlobal"
                            @input="diskonGlobal = Math.min(diskonGlobal, 100)"
                            type="number"
                            min="0"
                            max="100"
                            class="w-20 rounded border border-gray-300 px-2 py-1 text-right text-sm"
                        />
                    </div>

                    <!-- Summary -->
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal ({{ totalItems }} item)</span>
                            <span>{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div v-if="diskon > 0" class="flex justify-between text-red-600">
                            <span>Diskon</span>
                            <span>-{{ formatCurrency(diskon) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pajak ({{ pajakRate }}%)</span>
                            <span>{{ formatCurrency(pajak) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                            <span>Total</span>
                            <span class="text-emerald-600">{{ formatCurrency(total) }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Metode Bayar</label>
                        <select
                            v-model="metodeBayar"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="(label, value) in props.metodeBayar" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Cash Payment -->
                    <div v-if="metodeBayar === 'TUNAI'">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Jumlah Bayar</label>
                        <input
                            :value="jumlahBayarDisplay"
                            @input="
                                (e) => {
                                    const input = (e.target as HTMLInputElement).value;
                                    jumlahBayarDisplay = formatNumber(parseNumber(input));
                                    jumlahBayar = parseNumber(input);
                                }
                            "
                            @blur="
                                () => {
                                    jumlahBayarDisplay = formatNumber(jumlahBayar);
                                }
                            "
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            placeholder="0"
                        />
                        <div v-if="kembalian >= 0" class="mt-2 text-sm">
                            <span class="text-gray-600">Kembalian: </span>
                            <span class="font-bold text-green-600">{{ formatCurrency(kembalian) }}</span>
                        </div>
                    </div>

                    <!-- Credit (Kredit) DP Input -->
                    <div v-if="metodeBayar === 'KREDIT'" class="space-y-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">DP (Uang Muka)</label>
                        <input
                            :value="dpBayarDisplay"
                            @input="
                                (e) => {
                                    const input = (e.target as HTMLInputElement).value;
                                    dpBayarDisplay = formatNumber(parseNumber(input));
                                    dpBayar = parseNumber(input);
                                }
                            "
                            @blur="
                                () => {
                                    dpBayarDisplay = formatNumber(dpBayar);
                                }
                            "
                            type="text"
                            :max="Math.max(0, Number(total) - 1)"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            placeholder="0"
                        />
                        <div class="text-sm">
                            <span class="text-gray-600">Sisa Tagihan: </span>
                            <span class="font-bold text-red-600">{{ formatCurrency(Math.max(0, Number(total) - Number(dpBayar || 0))) }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <BaseButton
                            @click="processTransaction"
                            :disabled="cart.length === 0 || transactionForm.processing"
                            variant="primary"
                            class="w-full"
                            :loading="transactionForm.processing"
                        >
                            <i class="fas fa-credit-card mr-2"></i>
                            Bayar (F2)
                        </BaseButton>

                        <BaseButton @click="clearCart" variant="outline" class="w-full">
                            <i class="fas fa-trash mr-2"></i>
                            Clear (F3)
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Confirmation Modal -->
        <TransactionConfirmationModal
            :show="showConfirmationModal"
            :transaction="pendingTransaction"
            @confirm="handleConfirmTransaction"
            @cancel="handleCancelTransaction"
            @print="handlePrintReceipt"
        />
        <!-- Credit Contract Modal (opens before confirmation when KREDIT) -->
        <CreditContractModal
            :show="showCreditModal"
            :total="Number(total)"
            :dp="Number(dpBayar || 0)"
            @cancel="
                () => {
                    showCreditModal = false;
                }
            "
            @confirm="
                (cfg) => {
                    creditConfig = cfg;
                    showCreditModal = false;
                    showConfirmationModal = true;
                }
            "
        />

        <!-- Customer Info Modal -->
        <div
            v-if="showCustomerInfoModal && selectedCustomer"
            class="absolute top-20 right-0 z-50 w-80 rounded-lg border border-gray-200 bg-white p-4 shadow-lg"
        >
            <div class="space-y-3">
                <!-- Header -->
                <div class="flex items-center gap-3 border-b border-gray-200 pb-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <i class="fas fa-user text-emerald-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900">{{ selectedCustomer.nama }}</h3>
                        <p class="text-xs text-gray-500">ID: {{ selectedCustomer.id_pelanggan }}</p>
                    </div>
                    <button
                        @click="showCustomerInfoModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Customer Details -->
                <div class="space-y-2 text-xs">
                    <!-- Email -->
                    <div v-if="selectedCustomer.email" class="flex items-start gap-2">
                        <i class="fas fa-envelope mt-0.5 text-emerald-600 flex-shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-medium text-gray-500">Email</p>
                            <p class="text-gray-900">{{ selectedCustomer.email }}</p>
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div v-if="selectedCustomer.telepon" class="flex items-start gap-2">
                        <i class="fas fa-phone mt-0.5 text-emerald-600 flex-shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-medium text-gray-500">Telepon</p>
                            <p class="text-gray-900">{{ selectedCustomer.telepon }}</p>
                        </div>
                    </div>

                    <!-- Credit Limit -->
                    <div class="flex items-start gap-2">
                        <i class="fas fa-credit-card mt-0.5 text-emerald-600 flex-shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-medium text-gray-500">Limit Tersedia</p>
                            <p class="font-bold text-emerald-600">{{ formatCurrency(availableCredit) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
/* Smooth collapse for customer info */
.collapse-enter-active,
.collapse-leave-active {
    transition:
        max-height 0.25s ease,
        opacity 0.25s ease;
}
.collapse-enter-from,
.collapse-leave-to {
    max-height: 0;
    opacity: 0;
}
.collapse-enter-to,
.collapse-leave-from {
    max-height: 400px; /* enough for content */
    opacity: 1;
}
</style>
