<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin reset password generates a random 20 character string', function () {
    $admin = User::factory()->admin()->create([
        'password' => 'secret-password',
    ]);

    $target = User::factory()->kasir()->create([
        'password' => 'old-password',
    ]);

    $this->actingAs($admin);

    $generatedPassword = null;

    $response = $this
        ->from("/admin/pengguna/{$target->id_pengguna}/edit")
        ->post("/admin/pengguna/{$target->id_pengguna}/reset-password");

    $response->assertRedirect("/admin/pengguna/{$target->id_pengguna}/edit");
    $response->assertSessionHas('success', 'Password berhasil direset');
    $response->assertSessionHas('generated_password', function (string $value) use (&$generatedPassword) {
        $generatedPassword = $value;

        return strlen($value) === 20 && preg_match('/^[A-Za-z0-9]{20}$/', $value) === 1;
    });

    expect($generatedPassword)->not->toBeNull();
    expect($generatedPassword)->not->toBe('123456');

    $target->refresh();

    expect(Hash::check($generatedPassword, $target->password))->toBeTrue();
});
