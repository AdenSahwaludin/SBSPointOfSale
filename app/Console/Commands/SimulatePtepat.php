<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Models\JadwalAngsuran;
use App\Models\Pembayaran;
use App\Models\KontrakKredit;
use App\Models\Transaksi;
use App\Services\TrustScoreService;
use App\Services\CreditLimitService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulatePtepat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelanggan:p-tepat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi pembayaran angsuran kredit secara interaktif untuk melihat efek P_tepat/P_telat terhadap Trust Score';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("     SIMULASI P_TEPAT: PELUNASAN ANGSURAN KREDIT ");
        $this->info("=================================================");

        // 1. Pilih pelanggan
        $idPelanggan = $this->ask('Masukkan ID Pelanggan (contoh: P002)');
        $pelanggan = Pelanggan::find($idPelanggan);
        if (!$pelanggan) {
            $this->error("Pelanggan dengan ID '{$idPelanggan}' tidak ditemukan!");
            return 1;
        }

        $this->newLine();
        $this->info("Pelanggan Terpilih: {$pelanggan->nama} (ID: {$pelanggan->id_pelanggan})");
        $this->info("Trust Score saat ini : {$pelanggan->trust_score} / 100");
        $this->info("Credit Limit saat ini: Rp " . number_format($pelanggan->credit_limit, 0, ',', '.'));
        $this->info("Saldo Kredit saat ini: Rp " . number_format($pelanggan->saldo_kredit, 0, ',', '.'));

        // 2. Ambil angsuran yang statusnya DUE atau LATE
        $angsuranList = JadwalAngsuran::whereHas('kontrakKredit', function($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })->whereIn('status', ['DUE', 'LATE'])
          ->with('kontrakKredit')
          ->orderBy('jatuh_tempo')
          ->get();

        if ($angsuranList->isEmpty()) {
            $this->warn("\nPelanggan ini tidak memiliki angsuran aktif dengan status DUE atau LATE.");
            return 0;
        }

        // 3. Tampilkan angsuran aktif
        $this->newLine();
        $this->info("Daftar Angsuran Aktif (Belum Lunas):");
        $headers = ['Index', 'Nomor Kontrak', 'Periode Ke', 'Jatuh Tempo', 'Tagihan', 'Sisa Tagihan', 'Status'];
        $rows = [];
        foreach ($angsuranList as $index => $angsuran) {
            $sisa = $angsuran->jumlah_tagihan - $angsuran->jumlah_dibayar;
            $rows[] = [
                $index + 1,
                $angsuran->kontrakKredit->nomor_kontrak,
                $angsuran->periode_ke,
                $angsuran->jatuh_tempo->format('Y-m-d'),
                'Rp ' . number_format($angsuran->jumlah_tagihan, 0, ',', '.'),
                'Rp ' . number_format($sisa, 0, ',', '.'),
                $angsuran->status
            ];
        }

        $this->table($headers, $rows);

        // 4. Pilih angsuran
        $selectedIndex = (int) $this->ask("Pilih indeks angsuran yang ingin dilunasi (1-" . count($angsuranList) . ")") - 1;
        if ($selectedIndex < 0 || $selectedIndex >= count($angsuranList)) {
            $this->error("Pilihan indeks tidak valid!");
            return 1;
        }

        $angsuranSelected = $angsuranList[$selectedIndex];
        $kontrak = $angsuranSelected->kontrakKredit;

        $this->newLine();
        $this->info("Anda memilih Angsuran Periode Ke-{$angsuranSelected->periode_ke} untuk Kontrak {$kontrak->nomor_kontrak}.");

        // 5. Pilih status simulasi waktu pembayaran
        $timeChoice = $this->choice(
            "Simulasikan waktu pembayaran angsuran:",
            [
                'Tepat Waktu (paid_at <= jatuh_tempo)',
                'Terlambat (paid_at > jatuh_tempo, misal 5 hari telat)'
            ],
            0
        );

        $simulatedPaidAt = null;
        if ($timeChoice === 'Tepat Waktu (paid_at <= jatuh_tempo)') {
            // Tepat waktu: paid_at = jatuh_tempo
            $simulatedPaidAt = $angsuranSelected->jatuh_tempo;
            $this->info("Mensimulasikan pembayaran Tepat Waktu pada: " . $simulatedPaidAt->format('Y-m-d'));
        } else {
            // Terlambat: paid_at = jatuh_tempo + 5 hari
            $simulatedPaidAt = $angsuranSelected->jatuh_tempo->copy()->addDays(5);
            $this->info("Mensimulasikan pembayaran Terlambat pada: " . $simulatedPaidAt->format('Y-m-d'));
        }

        // Simpan nilai Trust Score & Credit Limit awal untuk perbandingan
        $oldScore = $pelanggan->trust_score;
        $oldLimit = $pelanggan->credit_limit;

        // 6. Jalankan transaksi pelunasan di database beneran
        DB::beginTransaction();
        try {
            $bayar = $angsuranSelected->jumlah_tagihan - $angsuranSelected->jumlah_dibayar;

            // a. Buat record pembayaran
            Pembayaran::create([
                'id_pembayaran' => Pembayaran::generateIdPembayaran(),
                'id_transaksi' => $kontrak->nomor_transaksi,
                'id_angsuran' => $angsuranSelected->id_angsuran,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_kasir' => '002-KSR', // Kasir default yang valid (max 8 karakter)
                'metode' => 'TUNAI',
                'tipe_pembayaran' => 'kredit',
                'jumlah' => $bayar,
                'tanggal' => $simulatedPaidAt,
                'keterangan' => 'Simulasi P_tepat (Tepat Waktu / Terlambat)',
            ]);

            // b. Update jadwal angsuran
            $angsuranSelected->jumlah_dibayar = $angsuranSelected->jumlah_tagihan;
            $angsuranSelected->status = 'PAID';
            $angsuranSelected->paid_at = $simulatedPaidAt;
            $angsuranSelected->save();

            // c. Update saldo kredit pelanggan di database
            $pelanggan->saldo_kredit = max(0, (float) $pelanggan->saldo_kredit - $bayar);
            $pelanggan->save();

            // d. Update status kontrak & transaksi jika seluruh angsuran lunas
            $unpaid = $kontrak->jadwalAngsuran()->where('status', '!=', 'PAID')->count();
            if ($unpaid === 0) {
                $kontrak->status = 'LUNAS';
                $kontrak->save();

                $trx = $kontrak->transaksi;
                if ($trx && $trx->status_pembayaran !== 'LUNAS') {
                    $trx->status_pembayaran = 'LUNAS';
                    $trx->paid_at = $simulatedPaidAt;
                    $trx->ar_status = 'LUNAS';
                    $trx->save();
                }
            }

            // e. Recalculate Trust Score & Credit Limit
            $newScore = TrustScoreService::updateTrustScore($pelanggan);
            
            $pelanggan->refresh(); // Refresh score agar sinkron
            $newLimit = CreditLimitService::updateCreditLimit($pelanggan);

            DB::commit();

            $this->newLine();
            $this->info("=================================================");
            $this->info("   PELUNASAN BERHASIL DIPROSES DI DATABASE!      ");
            $this->info("=================================================");

            // Tampilkan perbandingan before vs after
            $this->newLine();
            $this->line("Pelanggan : <fg=cyan>{$pelanggan->nama} ({$pelanggan->id_pelanggan})</>");
            $this->line("Angsuran  : Kontrak {$kontrak->nomor_kontrak} Periode Ke-{$angsuranSelected->periode_ke}");
            
            $scoreChange = $newScore - $oldScore;
            $limitChange = $newLimit - $oldLimit;

            $this->newLine();
            $this->line("📈 Perubahan Nilai Pelanggan:");
            $this->line("Trust Score : {$oldScore} → {$newScore} " . $this->formatChange($scoreChange));
            $this->line("Credit Limit: Rp " . number_format($oldLimit, 0, ',', '.') . " → Rp " . number_format($newLimit, 0, ',', '.') . " " . $this->formatChange($limitChange));

            // Tampilkan breakdown Trust Score baru
            $breakdown = TrustScoreService::calculateFullScore($pelanggan);
            $this->newLine();
            $this->line("📊 Breakdown Trust Score Baru:");
            $this->table(
                ['Komponen', 'Nilai Poin'],
                [
                    ['Baseline (Dasar)', '+50'],
                    ['Pumur (Umur Akun)', $this->formatPoints($breakdown['p_umur'])],
                    ['Ptepat (Tepat Waktu)', $this->formatPoints($breakdown['p_tepat'])],
                    ['Pfrekuensi (Belanja >= 3x/bln)', $this->formatPoints($breakdown['p_frekuensi'])],
                    ['Pnilai (Rata-rata > Median)', $this->formatPoints($breakdown['p_nilai'])],
                    ['Ptelat (Terlambat)', $this->formatPoints($breakdown['p_telat'])],
                    ['Pgagal (Gagal/VOID)', $this->formatPoints($breakdown['p_gagal'])],
                    ['Ptunggakan (Arrears)', $this->formatPoints($breakdown['p_tunggakan'])],
                    ['<fg=yellow;options=bold>TOTAL TRUST SCORE</>', "<fg=yellow;options=bold>{$breakdown['total']} / 100</>"],
                ]
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal memproses pelunasan angsuran: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Format points with color and sign.
     */
    private function formatPoints(int $points): string
    {
        if ($points > 0) {
            return "<fg=green>+{$points}</>";
        } elseif ($points < 0) {
            return "<fg=red>{$points}</>";
        }

        return "0";
    }

    /**
     * Format change with color and sign.
     */
    private function formatChange(int $change): string
    {
        if ($change > 0) {
            return "<fg=green>(+{$change})</>";
        } elseif ($change < 0) {
            return "<fg=red>({$change})</>";
        }

        return "<fg=gray>(tidak ada perubahan)</>";
    }
}
