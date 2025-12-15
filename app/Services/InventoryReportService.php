<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\TransaksiDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    /**
     * Get all products where stock is below or equal to ROP (Reorder Point)
     *
     * @return Collection Collection of products below ROP with their category
     */
    public function getProductsBelowROP(): Collection
    {
        return Produk::with('kategori')
            ->whereColumn('stok', '<=', 'batas_stok_minimum')
            ->whereNotNull('batas_stok_minimum')
            ->where('batas_stok_minimum', '>', 0)
            ->orderBy('stok', 'asc')
            ->get();
    }

    /**
     * Get products below ROP grouped by category
     *
     * @return Collection Collection grouped by category with product count and details
     */
    public function getProductsBelowROPByCategory(): Collection
    {
        $products = $this->getProductsBelowROP();

        // Group products by category
        return $products->groupBy(function ($produk) {
            return $produk->kategori ? $produk->kategori->nama_kategori : 'Tanpa Kategori';
        })->map(function ($categoryProducts, $categoryName) {
            return [
                'category_name' => $categoryName,
                'product_count' => $categoryProducts->count(),
                'products' => $categoryProducts->map(function ($produk) {
                    return [
                        'id_produk' => $produk->id_produk,
                        'sku' => $produk->sku,
                        'nama' => $produk->nama,
                        'stok' => $produk->stok,
                        'batas_stok_minimum' => $produk->batas_stok_minimum,
                        'jumlah_restock' => $produk->jumlah_restock,
                        'difference' => $produk->batas_stok_minimum - $produk->stok,
                    ];
                }),
            ];
        })->values();
    }

    /**
     * Perform ABC Analysis based on sales value
     *
     * ABC Analysis classifies inventory items:
     * - A items: Top 20% of items contributing ~80% of sales value (high value)
     * - B items: Next 30% of items contributing ~15% of sales value (medium value)
     * - C items: Remaining 50% of items contributing ~5% of sales value (low value)
     *
     * @param  string  $startDate  Start date for analysis (Y-m-d format)
     * @param  string  $endDate  End date for analysis (Y-m-d format)
     * @return Collection Collection of products with ABC classification
     */
    public function getABCAnalysis(string $startDate, string $endDate): Collection
    {
        // Get sales data grouped by product
        $salesData = TransaksiDetail::select(
            'id_produk',
            DB::raw('SUM(jumlah) as total_qty_sold'),
            DB::raw('SUM(subtotal) as total_sales_value')
        )
            ->whereHas('transaksi', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('status', 'selesai');
            })
            ->groupBy('id_produk')
            ->havingRaw('SUM(subtotal) > 0')
            ->get();

        // If no sales data, return empty collection
        if ($salesData->isEmpty()) {
            return collect();
        }

        // Sort by sales value descending
        $salesData = $salesData->sortByDesc('total_sales_value')->values();

        // Calculate total sales value
        $totalSalesValue = $salesData->sum('total_sales_value');

        // Calculate cumulative percentage and assign ABC class
        $cumulativeValue = 0;
        $salesData = $salesData->map(function ($item, $index) use ($totalSalesValue, &$cumulativeValue, $salesData) {
            $cumulativeValue += $item->total_sales_value;
            $cumulativePercentage = ($cumulativeValue / $totalSalesValue) * 100;
            $itemRank = (($index + 1) / $salesData->count()) * 100;

            // Assign ABC class based on cumulative percentage
            if ($cumulativePercentage <= 80) {
                $abcClass = 'A';
            } elseif ($cumulativePercentage <= 95) {
                $abcClass = 'B';
            } else {
                $abcClass = 'C';
            }

            // Alternative classification based on item rank
            // This ensures more balanced distribution
            if ($itemRank <= 20) {
                $abcClass = 'A';
            } elseif ($itemRank <= 50) {
                $abcClass = 'B';
            } else {
                $abcClass = 'C';
            }

            return [
                'id_produk' => $item->id_produk,
                'total_qty_sold' => $item->total_qty_sold,
                'total_sales_value' => $item->total_sales_value,
                'sales_percentage' => round(($item->total_sales_value / $totalSalesValue) * 100, 2),
                'cumulative_percentage' => round($cumulativePercentage, 2),
                'abc_class' => $abcClass,
            ];
        });

        // Load product and category information
        $productIds = $salesData->pluck('id_produk');
        $products = Produk::with('kategori')
            ->whereIn('id_produk', $productIds)
            ->get()
            ->keyBy('id_produk');

        // Merge product information with sales data
        return $salesData->map(function ($item) use ($products) {
            $produk = $products->get($item['id_produk']);

            if (! $produk) {
                return null;
            }

            return [
                'id_produk' => $item['id_produk'],
                'sku' => $produk->sku,
                'nama' => $produk->nama,
                'kategori' => $produk->kategori ? $produk->kategori->nama_kategori : null,
                'total_qty_sold' => $item['total_qty_sold'],
                'total_sales_value' => $item['total_sales_value'],
                'sales_percentage' => $item['sales_percentage'],
                'cumulative_percentage' => $item['cumulative_percentage'],
                'abc_class' => $item['abc_class'],
                'current_stock' => $produk->stok,
                'batas_stok_minimum' => $produk->batas_stok_minimum,
            ];
        })->filter()->values();
    }
}
