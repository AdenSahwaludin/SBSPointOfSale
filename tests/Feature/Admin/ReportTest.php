<?php

use App\Models\User;
use App\Models\Transaksi;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPelanggan() {
    return Pelanggan::create([
        'id_pelanggan' => 'PLG-' . uniqid(),
        'nama' => 'Test Pelanggan',
        'alamat' => 'Test Alamat',
        'telepon' => '08123456789',
        'email' => 'test@example.com',
    ]);
}

function createTransaksi($overrides = []) {
    $user = User::factory()->create(['role' => 'kasir']);
    $pelanggan = Pelanggan::first() ?? createPelanggan();
    
    return Transaksi::create(array_merge([
        'nomor_transaksi' => 'TRX-' . uniqid(),
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'id_kasir' => $user->id_pengguna,
        'tanggal' => Carbon::now(),
        'total_item' => 1,
        'subtotal' => 100000,
        'diskon' => 0,
        'pajak' => 0,
        'biaya_pengiriman' => 0,
        'total' => 100000,
        'metode_bayar' => 'TUNAI',
        'status_pembayaran' => 'LUNAS',
        'jenis_transaksi' => 'TUNAI',
    ], $overrides));
}

test('admin can view reports page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->get('/admin/reports');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Index')
            ->has('stats')
            ->has('transaksi')
        );
});

test('reports page shows correct stats', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    createTransaksi(['status_pembayaran' => 'LUNAS', 'total' => 100000]);
    createTransaksi(['status_pembayaran' => 'LUNAS', 'total' => 100000]);
    createTransaksi(['status_pembayaran' => 'LUNAS', 'total' => 100000]);
    createTransaksi(['status_pembayaran' => 'MENUNGGU']);
    createTransaksi(['status_pembayaran' => 'MENUNGGU']);
    createTransaksi(['status_pembayaran' => 'BATAL']);

    $response = $this->actingAs($admin)
        ->get('/admin/reports');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_transaksi', 6)
        ->where('stats.total_lunas', 3)
        ->where('stats.total_menunggu', 2)
        ->where('stats.total_batal', 1)
        ->where('stats.total_pendapatan', 300000)
    );
});

test('reports page can filter by date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Transaction yesterday
    createTransaksi([
        'tanggal' => Carbon::yesterday(),
        'status_pembayaran' => 'LUNAS',
        'total' => 50000
    ]);

    // Transaction today
    createTransaksi([
        'tanggal' => Carbon::today(),
        'status_pembayaran' => 'LUNAS',
        'total' => 100000
    ]);

    // Filter for today
    $response = $this->actingAs($admin)
        ->get('/admin/reports?start_date=' . Carbon::today()->format('Y-m-d') . '&end_date=' . Carbon::today()->format('Y-m-d'));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_transaksi', 1)
        ->where('stats.total_pendapatan', 100000)
    );
});

test('reports page can filter by status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    createTransaksi(['status_pembayaran' => 'LUNAS']);
    createTransaksi(['status_pembayaran' => 'MENUNGGU']);

    $response = $this->actingAs($admin)
        ->get('/admin/reports?status=LUNAS');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_transaksi', 1)
        ->where('stats.total_lunas', 1)
    );
});