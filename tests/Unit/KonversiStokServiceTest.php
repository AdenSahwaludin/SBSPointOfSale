<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Produk;
use App\Models\KonversiStok;
use App\Services\KonversiStokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class KonversiStokServiceTest extends TestCase
{
  use RefreshDatabase;

  private KonversiStokService $service;
  private $kategoriId;

  protected function setUp(): void
  {
    parent::setUp();
    $this->service = app(KonversiStokService::class);

    // Create kategori for testing
    DB::table('kategori')->insert([
      'id_kategori' => 'KAT1',
      'nama' => 'Test Kategori',
      'created_at' => now(),
      'updated_at' => now(),
    ]);
    $this->kategoriId = 'KAT1';
  }

  /**
   * Test partial conversion menggunakan buffer
   */
  public function test_partial_conversion_uses_buffer()
  {
    // Create produk karton dan pcs
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton A',
      'sku' => 'KARTON-A',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 10,
      'sisa_pcs_terbuka' => 50, // Sudah ada buffer 50 pcs
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs A',
      'sku' => 'PCS-A',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // Convert 30 pcs (dari buffer yang ada 50 pcs)
    $konversi = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      30,
      'parsial',
      120,
      'Test buffer usage'
    );

    // Verify hasil
    $this->assertEquals(30, $konversi->qty_to);
    $this->assertEquals(0, $konversi->packs_used); // Tidak perlu buka karton baru
    $this->assertEquals(30, $konversi->dari_buffer); // Ambil dari buffer
    $this->assertEquals(20, $konversi->sisa_buffer_after); // Buffer sisa 20 pcs

    // Verify produk states
    $produkKarton->refresh();
    $produkPcs->refresh();

    $this->assertEquals(10, $produkKarton->stok); // Stok karton tidak berubah
    $this->assertEquals(20, $produkKarton->sisa_pcs_terbuka); // Buffer berkurang 30
    $this->assertEquals(30, $produkPcs->stok); // Stok pcs bertambah 30
  }

  /**
   * Test partial conversion auto-open box ketika buffer tidak cukup
   */
  public function test_partial_conversion_auto_opens_box()
  {
    // Create produk karton dan pcs
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton B',
      'sku' => 'KARTON-B',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 10,
      'sisa_pcs_terbuka' => 30, // Buffer hanya 30 pcs
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs B',
      'sku' => 'PCS-B',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // Convert 200 pcs (30 dari buffer + 170 dari karton baru)
    // Butuh 2 karton: 30 (buffer) + 120 (karton 1) + 50 (dari karton 2) = 200
    // Sisa buffer: 120 (karton 2) - 50 = 70
    $konversi = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      200,
      'parsial',
      120,
      'Test auto-open box'
    );

    // Verify hasil
    $this->assertEquals(200, $konversi->qty_to);
    $this->assertEquals(2, $konversi->packs_used); // Perlu buka 2 karton (30 dari buffer + 120 + 50 dari karton 2)
    $this->assertEquals(30, $konversi->dari_buffer); // Ambil 30 dari buffer
    $this->assertEquals(70, $konversi->sisa_buffer_after); // Sisa 70 pcs di buffer (120-50=70 dari karton ke-2)

    // Verify produk states
    $produkKarton->refresh();
    $produkPcs->refresh();

    $this->assertEquals(8, $produkKarton->stok); // 10 - 2 karton = 8
    $this->assertEquals(70, $produkKarton->sisa_pcs_terbuka); // 70 sisa dari karton ke-2
    $this->assertEquals(200, $produkPcs->stok); // Bertambah 200 pcs
  }

  /**
   * Test full conversion
   */
  public function test_full_conversion()
  {
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton C',
      'sku' => 'KARTON-C',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 10,
      'sisa_pcs_terbuka' => 0,
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs C',
      'sku' => 'PCS-C',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // Mode PENUH: convert 5 karton
    $konversi = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      600, // 5 karton * 120 pcs/karton
      'penuh',
      120,
      'Test full conversion'
    );

    $produkKarton->refresh();
    $produkPcs->refresh();

    $this->assertEquals(5, $produkKarton->stok); // 10 - 5 = 5
    $this->assertEquals(0, $produkKarton->sisa_pcs_terbuka); // No buffer change
    $this->assertEquals(600, $produkPcs->stok); // 0 + 600 = 600
  }

  /**
   * Test reverse (undo) conversion
   */
  public function test_reverse_conversion()
  {
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton D',
      'sku' => 'KARTON-D',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 10,
      'sisa_pcs_terbuka' => 50,
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs D',
      'sku' => 'PCS-D',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // First conversion: convert 100 pcs
    // Buffer 50, need 100, so 50 from buffer + 50 from new karton
    // 1 karton = 120, so we get 120, use 50, buffer = 70
    $konversi = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      100,
      'parsial',
      120,
      'Initial conversion'
    );

    // Verify after conversion
    $produkKarton->refresh();
    $produkPcs->refresh();
    $this->assertEquals(100, $produkPcs->stok); // Only converted amount
    $this->assertEquals(70, $produkKarton->sisa_pcs_terbuka); // 50 (buffer used) + 120 (new) - 100 = 70

    // Reverse the conversion
    $this->service->reverse($konversi->id_konversi);

    // Verify after reverse
    $produkKarton->refresh();
    $produkPcs->refresh();

    $this->assertEquals(10, $produkKarton->stok); // Back to 10
    $this->assertEquals(50, $produkKarton->sisa_pcs_terbuka); // Buffer back to 50
    $this->assertEquals(0, $produkPcs->stok); // Back to 0

    // Verify record deleted
    $this->assertNull(KonversiStok::find($konversi->id_konversi));
  }

  /**
   * Test insufficient stock error
   */
  public function test_insufficient_stock_throws_exception()
  {
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton E',
      'sku' => 'KARTON-E',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 1, // Only 1 karton
      'sisa_pcs_terbuka' => 10,
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs E',
      'sku' => 'PCS-E',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // Try to convert more than available
    $this->expectException(\Exception::class);

    $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      500, // Need 4+ kartons, only have 1
      'parsial',
      120,
      'Should fail'
    );
  }

  /**
   * Test bulk reverse
   */
  public function test_bulk_reverse_conversions()
  {
    $produkKarton = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Karton F',
      'sku' => 'KARTON-F',
      'satuan' => 'karton',
      'isi_per_pack' => 120,
      'harga' => 10000,
      'harga_pack' => 12000,
      'stok' => 50,
      'sisa_pcs_terbuka' => 0,
    ]);

    $produkPcs = Produk::create([
      'id_kategori' => $this->kategoriId,
      'nama' => 'Pcs F',
      'sku' => 'PCS-F',
      'satuan' => 'pcs',
      'isi_per_pack' => 120,
      'harga' => 100,
      'harga_pack' => 120,
      'stok' => 0,
    ]);

    // Create 3 conversions
    $konversi1 = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      100,
      'parsial',
      120,
      'Conv 1'
    );

    $konversi2 = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      50,
      'parsial',
      120,
      'Conv 2'
    );

    $konversi3 = $this->service->convert(
      $produkKarton->id_produk,
      $produkPcs->id_produk,
      75,
      'parsial',
      120,
      'Conv 3'
    );

    // Bulk reverse all
    $this->service->bulkReverse([
      $konversi1->id_konversi,
      $konversi2->id_konversi,
      $konversi3->id_konversi,
    ]);

    // Verify all deleted
    $this->assertNull(KonversiStok::find($konversi1->id_konversi));
    $this->assertNull(KonversiStok::find($konversi2->id_konversi));
    $this->assertNull(KonversiStok::find($konversi3->id_konversi));

    // Verify produk states restored
    $produkKarton->refresh();
    $produkPcs->refresh();
    $this->assertEquals(50, $produkKarton->stok); // Back to original
    $this->assertEquals(0, $produkPcs->stok); // Back to 0
  }
}
