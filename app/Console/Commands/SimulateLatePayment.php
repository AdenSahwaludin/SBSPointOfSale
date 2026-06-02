<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pelanggan;
use App\Models\KontrakKredit;
use App\Models\JadwalAngsuran;
use Carbon\Carbon;
use App\Services\TrustScoreService;
use App\Services\CreditLimitService;

class SimulateLatePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelanggan:p-nunggak {id_pelanggan? : ID Pelanggan (contoh: P002)} {--count=1 : Jumlah angsuran yang ingin dibuat nunggak}';
    //php artisan pelanggan:p-nunggak P003 --count=1
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi angsuran nunggak (P_tunggakan) untuk melihat efek terhadap Trust Score';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idPelanggan = $this->argument('id_pelanggan');

        if (!$idPelanggan) {
            $pelangganList = Pelanggan::all();
            $this->info("Daftar Pelanggan yang tersedia:");
            foreach ($pelangganList as $p) {
                $this->line("- {$p->id_pelanggan} : {$p->nama} (Score: {$p->trust_score})");
            }
            $idPelanggan = $this->ask('Masukkan ID Pelanggan untuk disimulasikan nunggak');
        }

        $pelanggan = Pelanggan::find($idPelanggan);

        if (!$pelanggan) {
            $this->error("Pelanggan dengan ID {$idPelanggan} tidak ditemukan!");
            return;
        }

        $count = (int) $this->option('count');

        // Cari jadwal angsuran yang belum dibayar (DUE)
        $jadwalList = JadwalAngsuran::whereHas('kontrakKredit', function ($query) use ($idPelanggan) {
                $query->where('id_pelanggan', $idPelanggan)
                      ->whereNotIn('status', ['BATAL', 'GAGAL']);
            })
            ->where('status', 'DUE')
            ->where('jatuh_tempo', '>=', Carbon::today()) // Yang belum nunggak
            ->orderBy('id_angsuran', 'asc')
            ->limit($count)
            ->get();

        if ($jadwalList->isEmpty()) {
            $this->warn("Tidak ada jadwal angsuran DUE (yang belum nunggak) untuk pelanggan ini.");
            $this->info("Pastikan pelanggan memiliki transaksi kredit aktif yang belum jatuh tempo.");
            return;
        }

        $this->info("Mengubah {$jadwalList->count()} jadwal angsuran menjadi NUNGGAK (memundurkan jatuh tempo)...");

        foreach ($jadwalList as $jadwal) {
            $newJatuhTempo = Carbon::now()->subMonths(1)->startOfDay(); // Jatuh tempo 1 bulan lalu
            
            $jadwal->jatuh_tempo = $newJatuhTempo;
            // Kita biarkan status tetap DUE (atau apa adanya), karena syarat tunggakan adalah status != PAID dan jatuh_tempo < hari ini
            
            $jadwal->save();
            $this->line("Jadwal ID {$jadwal->id_angsuran} di-set nunggak (Jatuh tempo: {$newJatuhTempo->toDateString()})");
        }

        // Kalkulasi ulang Trust Score
        $oldScore = $pelanggan->trust_score;
        $this->info("\nMenghitung ulang Trust Score...");
        
        $newScore = TrustScoreService::updateTrustScore($pelanggan);
        $pelanggan->refresh();
        
        // Kalkulasi ulang Credit Limit
        $limitBreakdown = CreditLimitService::calculateCreditLimit($pelanggan);
        $newLimit = $limitBreakdown['credit_limit'];
        $pelanggan->forceFill(['credit_limit' => $newLimit])->save();
        
        $this->info("\n=== HASIL SIMULASI P_TELAT ===");
        $this->info("Pelanggan: {$pelanggan->nama} ({$pelanggan->id_pelanggan})");
        
        if ($newScore < $oldScore) {
            $this->error("Trust Score: {$oldScore} -> {$newScore} (TURUN " . ($oldScore - $newScore) . " poin)");
        } else {
            $this->info("Trust Score: {$oldScore} -> {$newScore}");
        }
        
        $this->info("Credit Limit: Rp " . number_format($newLimit, 0, ',', '.'));
        
        // Tampilkan breakdown Trust Score
        $breakdown = TrustScoreService::calculateFullScore($pelanggan);
        $this->line("\nBreakdown Skor:");
        $this->line("- Baseline: {$breakdown['baseline']}");
        $this->line("- P_umur (+): {$breakdown['p_umur']}");
        $this->line("- P_tepat (+): {$breakdown['p_tepat']}");
        $this->line("- P_telat (-): {$breakdown['p_telat']} (Total Penalti Keterlambatan)");
        $this->line("- P_gagal (-): {$breakdown['p_gagal']}");
        $this->line("- P_tunggakan (-): {$breakdown['p_tunggakan']}");
        $this->line("- P_frekuensi (+): {$breakdown['p_frekuensi']}");
        $this->line("- P_nilai (+): {$breakdown['p_nilai']}");
        $this->info("Total: {$breakdown['total']}");
    }
}
