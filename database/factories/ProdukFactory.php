<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isiPerPack = fake()->numberBetween(1, 12);
        $harga = fake()->numberBetween(5000, 100000);

        return [
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{5}'),
            'barcode' => fake()->unique()->ean13(),
            'no_bpom' => fake()->optional()->regexify('[A-Z]{2}[0-9]{14}'),
            'nama' => fake()->words(3, true),
            'id_kategori' => Kategori::factory(),
            'satuan' => fake()->randomElement(['pcs', 'pack', 'karton']),
            'isi_per_pack' => $isiPerPack,
            'stok' => fake()->numberBetween(0, 100),
            'sisa_pcs_terbuka' => 0,
            'harga' => $harga,
            'harga_pack' => $harga * $isiPerPack * 0.9, // 10% discount for pack
        ];
    }
}
