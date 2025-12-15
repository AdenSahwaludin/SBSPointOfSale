<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaksi>
 */
class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(100000, 5000000);
        $diskon = (int) ($subtotal * $this->faker->randomFloat(2, 0, 0.2));
        $pajak = (int) (($subtotal - $diskon) * 0.1);
        $total = $subtotal - $diskon + $pajak;

        $pelanggan = Pelanggan::factory()->create();

        return [
            'nomor_transaksi' => Transaksi::generateNomorTransaksi($pelanggan->id_pelanggan),
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_kasir' => User::factory(),
            'tanggal' => $this->faker->dateTime(),
            'total_item' => $this->faker->numberBetween(1, 10),
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'pajak' => $pajak,
            'biaya_pengiriman' => 0,
            'total' => $total,
            'metode_bayar' => $this->faker->randomElement(['TUNAI', 'QRIS', 'TRANSFER BCA']),
            'status_pembayaran' => Transaksi::STATUS_LUNAS,
            'paid_at' => $this->faker->dateTime(),
            'jenis_transaksi' => Transaksi::JENIS_TUNAI,
            'dp' => 0,
            'tenor_bulan' => null,
            'bunga_persen' => 0,
            'cicilan_bulanan' => null,
            'ar_status' => 'NA',
            'id_kontrak' => null,
        ];
    }

    /**
     * State untuk transaksi KREDIT
     */
    public function credit(): static
    {
        return $this->state(function (array $attributes) {
            $total = $attributes['total'];
            $dp = (int) ($total * 0.1);
            $outstanding = $total - $dp;

            return [
                'metode_bayar' => 'KREDIT',
                'jenis_transaksi' => Transaksi::JENIS_KREDIT,
                'status_pembayaran' => Transaksi::STATUS_MENUNGGU,
                'paid_at' => null,
                'dp' => $dp,
                'tenor_bulan' => 12,
                'bunga_persen' => 5,
                'cicilan_bulanan' => (int) ($outstanding / 12),
                'ar_status' => 'AKTIF',
            ];
        });
    }

    /**
     * State untuk transaksi TUNAI
     */
    public function cash(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metode_bayar' => 'TUNAI',
                'jenis_transaksi' => Transaksi::JENIS_TUNAI,
                'status_pembayaran' => Transaksi::STATUS_LUNAS,
                'paid_at' => $this->faker->dateTime(),
            ];
        });
    }

    /**
     * State untuk transaksi dengan status MENUNGGU
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_pembayaran' => Transaksi::STATUS_MENUNGGU,
                'paid_at' => null,
            ];
        });
    }
}
