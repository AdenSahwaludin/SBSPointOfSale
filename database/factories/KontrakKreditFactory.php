<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KontrakKredit>
 */
class KontrakKreditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pokokPinjaman = $this->faker->numberBetween(1000000, 10000000);
        $tenorBulan = $this->faker->randomElement([6, 12, 18, 24]);
        $bungaPersen = $this->faker->randomFloat(2, 0, 10);
        $cicilan = (int) round($pokokPinjaman * (1 + ($bungaPersen / 100)) / $tenorBulan);

        $pelanggan = \App\Models\Pelanggan::factory()->create();
        $transaksi = \App\Models\Transaksi::factory()->credit()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        return [
            'nomor_kontrak' => 'KK-'.date('Ymd').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'nomor_transaksi' => $transaksi->nomor_transaksi,
            'mulai_kontrak' => now(),
            'tenor_bulan' => $tenorBulan,
            'pokok_pinjaman' => $pokokPinjaman,
            'dp' => $this->faker->numberBetween(0, 1000000),
            'bunga_persen' => $bungaPersen,
            'cicilan_bulanan' => $cicilan,
            'status' => 'AKTIF',
            'score_snapshot' => $this->faker->numberBetween(50, 100),
        ];
    }
}
