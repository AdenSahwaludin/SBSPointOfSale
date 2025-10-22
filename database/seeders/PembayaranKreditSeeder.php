<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PembayaranKreditSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Pelanggan::where('saldo_kredit', '>', 0)->get();
        foreach ($customers as $pelanggan) {
            // Buat 1-3 pembayaran acak untuk sebagian pelanggan
            if (random_int(0, 100) < 60) {
                $count = random_int(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $amount = (int)round(min($pelanggan->saldo_kredit, max(50000, $pelanggan->saldo_kredit * (random_int(10, 30) / 100))));
                    if ($amount <= 0)
                        break;

                    $tanggal = Carbon::now()->subDays(random_int(0, 30));
                    $idPembayaran = 'PAY-' . $tanggal->format('Ymd') . '-' . str_pad((string)random_int(1, 9999999), 7, '0', STR_PAD_LEFT);

                    // Create payment record in pembayaran table with tipe_pembayaran='kredit'
                    Pembayaran::create([
                        'id_pembayaran' => $idPembayaran,
                        'id_transaksi' => null,
                        'id_angsuran' => null,
                        'id_pelanggan' => $pelanggan->id_pelanggan,
                        'id_kasir' => '001-ADEN',
                        'metode' => ['tunai', 'transfer'][random_int(0, 1)],
                        'tipe_pembayaran' => 'kredit',
                        'jumlah' => $amount,
                        'tanggal' => $tanggal,
                        'keterangan' => 'Pembayaran angsuran kredit',
                    ]);

                    $pelanggan->decrement('saldo_kredit', $amount);
                    $pelanggan->refresh();
                    if ($pelanggan->saldo_kredit <= 0) {
                        $pelanggan->saldo_kredit = 0;
                        $pelanggan->save();
                        break;
                    }
                }
            }
        }
    }
}
