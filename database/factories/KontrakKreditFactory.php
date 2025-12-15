<?php

namespace Database\Factories;

use App\Models\KontrakKredit;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KontrakKredit>
 */
class KontrakKreditFactory extends Factory
{
    protected $model = KontrakKredit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $sequence = 1;

        return [
            'nomor_kontrak' => 'KT-'.date('Y-m').'-'.str_pad($sequence++, 4, '0', STR_PAD_LEFT),
            'id_pelanggan' => Pelanggan::factory(),
            'nomor_transaksi' => Transaksi::factory(),
            'mulai_kontrak' => Carbon::today(),
            'tenor_bulan' => $this->faker->randomElement([3, 6, 9, 12, 24]),
            'pokok_pinjaman' => $this->faker->numberBetween(500000, 5000000),
            'dp' => $this->faker->numberBetween(0, 1000000),
            'bunga_persen' => $this->faker->randomFloat(2, 0, 15),
            'cicilan_bulanan' => $this->faker->numberBetween(50000, 500000),
            'status' => 'AKTIF',
            'score_snapshot' => $this->faker->numberBetween(50, 100),
        ];
    }

    /**
     * Contract is settled/completed
     */
    public function settled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'SELESAI',
        ]);
    }

    /**
     * Contract is cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'BATAL',
        ]);
    }
}
