<?php

namespace Database\Factories;

use App\Models\GoodsIn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoodsIn>
 */
class GoodsInFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => 'draft',
            'tanggal_request' => now(),
        ];
    }
}
