<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        return [
            'id_pengguna' => sprintf('%03d-USER', $counter++),
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telepon' => fake()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'kasir',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State for kasir role
     */
    public function kasir(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_pengguna' => sprintf('%03d-KSR', rand(100, 999)),
            'role' => 'kasir',
        ]);
    }

    /**
     * State for admin role
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_pengguna' => sprintf('%03d-ADM', rand(100, 999)),
            'role' => 'admin',
        ]);
    }
}
