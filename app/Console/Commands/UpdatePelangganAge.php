<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use Illuminate\Console\Command;

class UpdatePelangganAge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelanggan:p-umur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merubah umur akun pelanggan (created_at) ke 3 atau 6 bulan lalu secara interaktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idPelanggan = $this->ask('Masukkan ID Pelanggan (contoh: P001)');

        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            $this->error("Pelanggan dengan ID {$idPelanggan} tidak ditemukan!");
            return 1;
        }

        $this->info("Ditemukan pelanggan: {$pelanggan->nama} (Umur akun saat ini: {$pelanggan->created_at->diffInMonths(now())} bulan)");

        $age = $this->choice(
            'Pilih umur akun yang baru (bulan):',
            ['3', '6'],
            0
        );

        $newDate = now()->subMonths((int) $age);

        $pelanggan->created_at = $newDate;
        $pelanggan->save();

        $this->info("Berhasil! Umur akun {$pelanggan->nama} telah diubah menjadi {$age} bulan (Tanggal daftar: {$newDate->format('Y-m-d H:i:s')}).");

        return 0;
    }
}
