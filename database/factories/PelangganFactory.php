<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    protected $model = Pelanggan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $sequence = 1;

        return [
            'id_pelanggan' => 'P'.str_pad($sequence++, 3, '0', STR_PAD_LEFT),
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telepon' => $this->faker->numerify('08##########'),
            'kota' => $this->faker->city(),
            'alamat' => $this->faker->address(),
            'aktif' => true,
            'trust_score' => $this->faker->numberBetween(50, 100),
            'credit_limit' => $this->faker->numberBetween(1000000, 10000000),
            'status_kredit' => 'aktif',
            'saldo_kredit' => 0,
        ];
    }

    /**
     * Pelanggan dengan status kredit nonaktif
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_kredit' => 'nonaktif',
            ];
        });
    }

    /**
     * Pelanggan dengan kredit limit tinggi
     */
    public function highCredit(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'credit_limit' => 50000000,
            ];
        });
    }
}
