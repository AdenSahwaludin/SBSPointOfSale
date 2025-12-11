# Goods In System Services Documentation

This document provides usage examples for the three service classes created for the Goods In system.

## 1. GoodsInService

Handles Purchase Order (PO) request management including creation, approval, and rejection.

### Available Methods

#### createPORequest($kasirId, $items)

Create a new PO request with status 'submitted'.

**Parameters:**

- `$kasirId` (int): The ID of the kasir creating the request
- `$items` (array): Array of items with format: `[['id_produk' => int, 'qty_request' => int], ...]`

**Returns:** `GoodsIn` model with loaded relationships

**Example:**

```php
use App\Services\GoodsInService;

$service = new GoodsInService();

$items = [
    ['id_produk' => 1, 'qty_request' => 50],
    ['id_produk' => 2, 'qty_request' => 30],
    ['id_produk' => 3, 'qty_request' => 100],
];

$po = $service->createPORequest($kasirId, $items);
// PO created with status 'submitted' and unique PO number
```

#### approvePO($poId, $adminId, $catatan)

Approve a pending PO request.

**Parameters:**

- `$poId` (int): The ID of the PO to approve
- `$adminId` (int): The ID of the admin approving the request
- `$catatan` (string|null): Optional approval notes

**Returns:** `GoodsIn` model with updated status

**Example:**

```php
$approvedPO = $service->approvePO($poId, $adminId, 'Approved for immediate procurement');
// PO status changed to 'approved' with timestamp and admin ID
```

#### rejectPO($poId, $adminId, $catatan)

Reject a pending PO request.

**Parameters:**

- `$poId` (int): The ID of the PO to reject
- `$adminId` (int): The ID of the admin rejecting the request
- `$catatan` (string|null): Optional rejection notes (recommended)

**Returns:** `GoodsIn` model with updated status

**Example:**

```php
$rejectedPO = $service->rejectPO($poId, $adminId, 'Budget constraints - resubmit next month');
// PO status changed to 'rejected' with reason
```

#### getPendingPOs()

Get all POs awaiting approval (status = 'submitted').

**Returns:** Collection of `GoodsIn` models

**Example:**

```php
$pendingPOs = $service->getPendingPOs();
// Returns all POs with 'submitted' status, ordered by request date
```

#### getPOsByKasir($kasirId)

Get all POs created by a specific kasir.

**Parameters:**

- `$kasirId` (int): The ID of the kasir

**Returns:** Collection of `GoodsIn` models

**Example:**

```php
$kasirPOs = $service->getPOsByKasir($kasirId);
// Returns all POs for this kasir, ordered by newest first
```

---

## 2. StockAdjustmentService

Handles stock adjustments with automatic stock quantity updates.

### Available Methods

#### createAdjustment($produkId, $tipe, $qtyAdjustment, $alasan, $penggunaId)

Create a stock adjustment and automatically update product stock.

**Parameters:**

- `$produkId` (int): The ID of the product to adjust
- `$tipe` (string): Adjustment type - must be one of:
    - **Positive adjustments** (add to stock):
        - `retur_pelanggan`: Customer return
        - `retur_gudang`: Warehouse return
        - `koreksi_plus`: Positive correction
    - **Negative adjustments** (subtract from stock):
        - `koreksi_minus`: Negative correction
        - `expired`: Expired goods
        - `rusak`: Damaged goods
    - **Special**:
        - `penyesuaian_opname`: Stock opname adjustment (can be + or -)
- `$qtyAdjustment` (int): Quantity to adjust (always positive number)
- `$alasan` (string): Reason for adjustment
- `$penggunaId` (int): The ID of the user making the adjustment

**Returns:** `StockAdjustment` model with loaded relationships

**Example:**

```php
use App\Services\StockAdjustmentService;

$service = new StockAdjustmentService();

// Customer returns 10 items - ADDS to stock
$adjustment = $service->createAdjustment(
    produkId: 1,
    tipe: 'retur_pelanggan',
    qtyAdjustment: 10,
    alasan: 'Pelanggan mengembalikan barang - salah beli',
    penggunaId: $userId
);
// Product stock increased by 10

// Found 5 expired items - SUBTRACTS from stock
$adjustment = $service->createAdjustment(
    produkId: 2,
    tipe: 'expired',
    qtyAdjustment: 5,
    alasan: 'Barang kadaluarsa - ditemukan saat stock opname',
    penggunaId: $userId
);
// Product stock decreased by 5

// Stock opname correction
$adjustment = $service->createAdjustment(
    produkId: 3,
    tipe: 'penyesuaian_opname',
    qtyAdjustment: -3, // Can be negative for penyesuaian_opname
    alasan: 'Penyesuaian hasil stock opname',
    penggunaId: $userId
);
```

**Important Notes:**

- The service automatically calculates whether to add or subtract from stock based on the adjustment type
- Prevents negative stock - throws exception if adjustment would result in negative stock
- All operations are wrapped in database transactions for data integrity
- Uses the `AdjustmentType` enum for type validation

---

## 3. InventoryReportService

Provides inventory analytics and reporting functions.

### Available Methods

#### getProductsBelowROP()

Get all products where current stock is below or equal to the Reorder Point (ROP).

**Returns:** Collection of `Produk` models with kategori relationship loaded

**Example:**

```php
use App\Services\InventoryReportService;

$service = new InventoryReportService();

$lowStockProducts = $service->getProductsBelowROP();
// Returns products that need reordering, sorted by lowest stock first
foreach ($lowStockProducts as $produk) {
    echo "SKU: {$produk->sku} - Stock: {$produk->stok} (ROP: {$produk->rop})\n";
}
```

#### getProductsBelowROPByCategory()

Get products below ROP grouped by category with summary statistics.

**Returns:** Collection grouped by category with product details

**Example:**

```php
$categoryReport = $service->getProductsBelowROPByCategory();

foreach ($categoryReport as $category) {
    echo "Category: {$category['category_name']}\n";
    echo "Products needing reorder: {$category['product_count']}\n";

    foreach ($category['products'] as $product) {
        echo "  - {$product['nama']}: Stock {$product['stok']}, ";
        echo "Needs {$product['difference']} more to reach ROP\n";
    }
}
```

#### getABCAnalysis($startDate, $endDate)

Perform ABC Analysis to classify inventory based on sales value contribution.

**Parameters:**

- `$startDate` (string): Start date in Y-m-d format
- `$endDate` (string): End date in Y-m-d format

**Returns:** Collection of products with ABC classification

**Classification Logic:**

- **A items**: Top 20% of items by rank (high-value items)
- **B items**: Next 30% of items (medium-value items)
- **C items**: Remaining 50% of items (low-value items)

**Example:**

```php
// Analyze last 3 months
$startDate = now()->subMonths(3)->format('Y-m-d');
$endDate = now()->format('Y-m-d');

$abcAnalysis = $service->getABCAnalysis($startDate, $endDate);

// Class A items - focus inventory management efforts here
$classA = $abcAnalysis->where('abc_class', 'A');
echo "Class A Items (High Value): {$classA->count()}\n";
echo "Total Sales Value: Rp " . number_format($classA->sum('total_sales_value')) . "\n";

// Full report with details
foreach ($abcAnalysis as $item) {
    echo "{$item['abc_class']} - {$item['nama']} ({$item['kategori']})\n";
    echo "  Sales: Rp " . number_format($item['total_sales_value']);
    echo " ({$item['sales_percentage']}%)\n";
    echo "  Current Stock: {$item['current_stock']}\n";
}
```

**Use Cases:**

- **Class A**: Focus on these items - tight inventory control, frequent review
- **Class B**: Moderate control - regular review
- **Class C**: Minimal control - bulk ordering, less frequent review

---

## Controller Integration Example

Here's how to use these services in a controller:

```php
<?php

namespace App\Http\Controllers;

use App\Services\GoodsInService;
use App\Services\StockAdjustmentService;
use App\Services\InventoryReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(
        private GoodsInService $goodsInService,
        private StockAdjustmentService $stockAdjustmentService,
        private InventoryReportService $inventoryReportService
    ) {}

    public function createPurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id_produk' => 'required|exists:produk,id_produk',
            'items.*.qty_request' => 'required|integer|min:1',
        ]);

        try {
            $po = $this->goodsInService->createPORequest(
                auth()->id(),
                $validated['items']
            );

            return redirect()->back()->with('success', "PO {$po->nomor_po} created successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'tipe' => 'required|string',
            'qty_adjustment' => 'required|integer|min:1',
            'alasan' => 'required|string|max:500',
        ]);

        try {
            $adjustment = $this->stockAdjustmentService->createAdjustment(
                $validated['id_produk'],
                $validated['tipe'],
                $validated['qty_adjustment'],
                $validated['alasan'],
                auth()->id()
            );

            return redirect()->back()->with('success', 'Stock adjusted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function inventoryReports()
    {
        $lowStockProducts = $this->inventoryReportService->getProductsBelowROP();
        $lowStockByCategory = $this->inventoryReportService->getProductsBelowROPByCategory();

        // ABC Analysis for last quarter
        $abcAnalysis = $this->inventoryReportService->getABCAnalysis(
            now()->subMonths(3)->format('Y-m-d'),
            now()->format('Y-m-d')
        );

        return Inertia::render('Admin/Inventory/Reports', [
            'lowStockProducts' => $lowStockProducts,
            'lowStockByCategory' => $lowStockByCategory,
            'abcAnalysis' => $abcAnalysis,
        ]);
    }
}
```

---

## Error Handling

All services use exceptions for error handling:

- `\InvalidArgumentException`: Invalid input parameters
- `\LogicException`: Business logic violations (e.g., approving already approved PO)
- `\Illuminate\Database\Eloquent\ModelNotFoundException`: Record not found

Always wrap service calls in try-catch blocks for proper error handling.

---

## Transaction Safety

All write operations (`create*`, `approve*`, `reject*`) use database transactions to ensure data integrity. If any part of the operation fails, all changes are rolled back automatically.
