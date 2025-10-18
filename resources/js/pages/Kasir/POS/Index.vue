<script lang="ts" setup>
import BaseButton from '@/components/BaseButton.vue';
import TransactionConfirmationModal from '@/components/TransactionConfirmationModal.vue';
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

// State management
const selectedKategori = ref<number | null>(null);
const searchQuery = ref('');
const barcodeInput = ref('');
const cart = ref<CartItem[]>([]);
const selectedPelanggan = ref<string>('P001'); // Default to 'Umum'
const metodeBayar = ref<string>('TUNAI');
const jumlahBayar = ref<number>(0);
const diskonGlobal = ref<number>(0);
const pajakRate = ref<number>(0);
const searchResults = ref<Produk[]>([]); // Hasil search dari API
const isSearching = ref(false); // Loading state
const searchTimeout = ref<number | null>(null); // Debounce timer

// Modal confirmation
const showConfirmationModal = ref(false);
const pendingTransaction = ref<any>(null);

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
}

// Live search dengan debounce
function handleSearchInput() {
    // Clear previous timeout
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    // Reset jika query kosong
    if (!searchQuery.value || searchQuery.value.length < 2) {
        searchResults.value = [];
        isSearching.value = false;
        return;
    }

    // Set loading state
    isSearching.value = true;

    // Debounce 300ms
    searchTimeout.value = window.setTimeout(() => {
        performSearch();
    }, 300);
}

async function performSearch() {
    try {
        const response = await fetch(`/kasir/pos/search-produk?q=${encodeURIComponent(searchQuery.value)}`);
        if (!response.ok) throw new Error('Search failed');

        const data = await response.json();
        searchResults.value = data;
    } catch (error) {
        console.error('Search error:', error);
        addNotification({
            type: 'error',
            title: 'Pencarian gagal!',
        });
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
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

    // Prepare transaction data for confirmation
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
    };

    // Show confirmation modal
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
    } as Record<string, any>;

    fetch('/kasir/pos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(requestData),
    })
        .then((response) => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
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
            addNotification({
                type: 'error',
                title: 'Gagal menyimpan transaksi!',
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

    // Prepare receipt data for printing
    const receiptWindow = window.open('', '_blank');
    if (!receiptWindow) {
        addNotification({
            type: 'warning',
            title: 'Popup blocker mencegah pencetakan',
        });
        return;
    }

    const receiptHTML = generateReceiptHTML(pendingTransaction.value);
    receiptWindow.document.write(receiptHTML);
    receiptWindow.document.close();
    
    // Auto print after content loads
    receiptWindow.onload = () => {
        receiptWindow.print();
    };
}

function generateReceiptHTML(transaction: any): string {
    const itemsHTML = transaction.items
        .map((item: any) => {
            const subtotal = item.harga_jual * item.quantity;
            return `
                <tr>
                    <td style="padding: 4px 0; text-align: left;">${item.nama}</td>
                    <td style="padding: 4px 0; text-align: right;">${item.quantity}</td>
                    <td style="padding: 4px 0; text-align: right;">Rp ${item.harga_jual.toLocaleString('id-ID')}</td>
                    <td style="padding: 4px 0; text-align: right;">Rp ${subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        })
        .join('');

    const kembalian = transaction.metode_bayar === 'TUNAI' 
        ? (transaction.jumlah_bayar || 0) - transaction.total 
        : 0;

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
                    <td class="value">Rp ${transaction.subtotal.toLocaleString('id-ID')}</td>
                </tr>
                ${transaction.diskon > 0 ? `
                <tr>
                    <td class="label">Diskon:</td>
                    <td class="value" style="color: green;">-Rp ${transaction.diskon.toLocaleString('id-ID')}</td>
                </tr>
                ` : ''}
                ${transaction.pajak > 0 ? `
                <tr>
                    <td class="label">Pajak:</td>
                    <td class="value">Rp ${transaction.pajak.toLocaleString('id-ID')}</td>
                </tr>
                ` : ''}
                <tr class="total">
                    <td class="label">TOTAL:</td>
                    <td class="value">Rp ${transaction.total.toLocaleString('id-ID')}</td>
                </tr>
                ${transaction.metode_bayar === 'TUNAI' ? `
                <tr>
                    <td class="label">Dibayar:</td>
                    <td class="value">Rp ${(transaction.jumlah_bayar || 0).toLocaleString('id-ID')}</td>
                </tr>
                <tr class="total">
                    <td class="label">Kembalian:</td>
                    <td class="value">Rp ${kembalian.toLocaleString('id-ID')}</td>
                </tr>
                ` : ''}
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
    metodeBayar.value = 'TUNAI';
    diskonGlobal.value = 0;
    jumlahBayar.value = 0;
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

        <div class="flex h-[calc(100vh-8rem)] gap-6">
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
                                searchResults = [];
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
                    <div v-else class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-4">
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
                <div class="border-b border-gray-100 p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">Keranjang Belanja</h2>

                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Pelanggan</label>
                        <select
                            v-model="selectedPelanggan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="pelanggan in props.pelanggan" :key="pelanggan.id_pelanggan" :value="pelanggan.id_pelanggan">
                                {{ pelanggan.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div v-if="cart.length === 0" class="py-8 text-center text-gray-500">
                        <i class="fas fa-shopping-cart mb-4 text-4xl"></i>
                        <p>Keranjang kosong</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(item, index) in cart" :key="`${item.id_produk}-${item.mode_qty}`" class="rounded-lg bg-gray-50 p-3">
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
                                        class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-xs hover:bg-gray-300"
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
                                        class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-xs hover:bg-gray-300"
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
                            v-model.number="jumlahBayar"
                            type="number"
                            :min="total"
                            step="1000"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                            placeholder="0"
                        />
                        <div v-if="kembalian >= 0" class="mt-2 text-sm">
                            <span class="text-gray-600">Kembalian: </span>
                            <span class="font-bold text-green-600">{{ formatCurrency(kembalian) }}</span>
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
</style>
