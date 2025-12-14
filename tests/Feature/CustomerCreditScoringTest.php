<?php

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\CustomerCreditScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Customer Credit Scoring Service', function () {
    beforeEach(function () {
        // Create test kasir user
        User::create([
            'id_pengguna' => '001-KASIR',
            'username' => 'kasir_test',
            'nama' => 'Kasir Test',
            'role' => 'kasir',
            'password' => bcrypt('password'),
            'aktif' => true,
        ]);
    });

    describe('Auto-Increase Credit', function () {
        it('does not increase credit for customers with trust score below 70', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS001',
                'nama' => 'Low Score Customer',
                'trust_score' => 50, // Below 70
                'credit_limit' => 1000000,
            ]);

            $result = CustomerCreditScoringService::autoIncreaseCredit($pelanggan);

            expect($result['limit_increased'])->toBeFalse();
            expect($result['new_limit'])->toBe(1000000);
            expect($result['increase_amount'])->toBe(0);
        });

        it('increases credit after 3+ transactions in 6 months', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS002',
                'nama' => 'Active Customer',
                'trust_score' => 75,
                'credit_limit' => 500000, // Lower initial limit
                'created_at' => now()->subMonths(6),
            ]);

            // Create 3 larger transactions to ensure calculation gives result > 500k
            for ($i = 1; $i <= 3; $i++) {
                Transaksi::create([
                    'nomor_transaksi' => "INV-CS002-{$i}",
                    'id_pelanggan' => 'CS002',
                    'id_kasir' => '001-KASIR',
                    'tanggal' => now()->subMonths(6 - $i),
                    'total_item' => 1,
                    'subtotal' => 1500000,  // Increased from 500k
                    'total' => 1500000,
                    'jenis_transaksi' => 'TUNAI',
                    'status_pembayaran' => 'LUNAS',
                ]);
            }

            $result = CustomerCreditScoringService::autoIncreaseCredit($pelanggan);

            expect($result['limit_increased'])->toBeTrue();
            expect($result['new_limit'])->toBeGreaterThan(500000);
            expect($result['increase_amount'])->toBeGreaterThan(0);
        });

        it('increases more credit for 6+ transactions (15% bonus)', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS003',
                'nama' => 'Very Active Customer',
                'trust_score' => 80,
                'credit_limit' => 2000000,
                'created_at' => now()->subMonths(6),
            ]);

            // Create 6 transactions
            for ($i = 1; $i <= 6; $i++) {
                Transaksi::create([
                    'nomor_transaksi' => "INV-CS003-{$i}",
                    'id_pelanggan' => 'CS003',
                    'id_kasir' => '001-KASIR',
                    'tanggal' => now()->subMonths(6 - $i),
                    'total_item' => 1,
                    'subtotal' => 750000,
                    'total' => 750000,
                    'jenis_transaksi' => 'TUNAI',
                    'status_pembayaran' => 'LUNAS',
                ]);
            }

            $result = CustomerCreditScoringService::autoIncreaseCredit($pelanggan);

            expect($result['limit_increased'])->toBeTrue();
            expect($result['increase_amount'])->toBeGreaterThan(0);
        });

        it('applies trust score multiplier for high trust score', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS004',
                'nama' => 'Premium Customer',
                'trust_score' => 90, // 1.5x multiplier
                'credit_limit' => 3000000, // Reasonable initial limit
                'created_at' => now()->subMonths(6),
            ]);

            // Create 6 transactions with good value
            for ($i = 1; $i <= 6; $i++) {
                Transaksi::create([
                    'nomor_transaksi' => "INV-CS004-{$i}",
                    'id_pelanggan' => 'CS004',
                    'id_kasir' => '001-KASIR',
                    'tanggal' => now()->subMonths(6 - $i),
                    'total_item' => 1,
                    'subtotal' => 1500000,
                    'total' => 1500000,
                    'jenis_transaksi' => 'TUNAI',
                    'status_pembayaran' => 'LUNAS',
                ]);
            }

            $result = CustomerCreditScoringService::autoIncreaseCredit($pelanggan);

            expect($result['limit_increased'])->toBeTrue();
            // With 90+ trust score, bonus should be 1.5x higher
            expect($result['increase_amount'])->toBeGreaterThan(0);
        });
    });

    describe('Restore Credit Balance', function () {
        it('restores saldo kredit when payment is made', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS005',
                'nama' => 'Credit Customer',
                'trust_score' => 75,
                'credit_limit' => 5000000,
                'saldo_kredit' => 3000000, // Outstanding debt
            ]);

            $result = CustomerCreditScoringService::restoreCreditBalance($pelanggan, 1000000);

            expect($result['saldo_restored'])->toBeTrue();
            expect($result['original_saldo'])->toBe(3000000);
            expect($result['new_saldo'])->toBe(2000000);
            expect($result['amount_restored'])->toBe(1000000);

            // Verify in database
            $pelanggan->refresh();
            expect((int) $pelanggan->saldo_kredit)->toBe(2000000);
        });

        it('prevents negative saldo kredit when payment exceeds outstanding', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS006',
                'nama' => 'Credit Customer',
                'trust_score' => 75,
                'credit_limit' => 5000000,
                'saldo_kredit' => 1000000,
            ]);

            // Try to pay more than outstanding
            $result = CustomerCreditScoringService::restoreCreditBalance($pelanggan, 2000000);

            expect($result['new_saldo'])->toBe(0); // Should not go negative
            $pelanggan->refresh();
            expect((int) $pelanggan->saldo_kredit)->toBe(0);
        });
    });

    describe('Detailed Score Breakdown', function () {
        it('returns comprehensive credit score breakdown', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS007',
                'nama' => 'Breakdown Test',
                'trust_score' => 80,
                'credit_limit' => 5000000,
                'saldo_kredit' => 2000000,
                'status_kredit' => 'aktif',
                'created_at' => now()->subMonths(3),
            ]);

            // Create 4 transactions
            for ($i = 1; $i <= 4; $i++) {
                Transaksi::create([
                    'nomor_transaksi' => "INV-CS007-{$i}",
                    'id_pelanggan' => 'CS007',
                    'id_kasir' => '001-KASIR',
                    'tanggal' => now()->subDays(30 - $i * 5),
                    'total_item' => 1,
                    'subtotal' => 1000000,
                    'total' => 1000000,
                    'jenis_transaksi' => 'TUNAI',
                    'status_pembayaran' => 'LUNAS',
                ]);
            }

            $breakdown = CustomerCreditScoringService::getDetailedScoreBreakdown($pelanggan);

            expect($breakdown)->toHaveKeys([
                'trust_score',
                'credit_limit',
                'saldo_kredit',
                'available_credit',
                'transactions_6_months',
                'membership_days',
                'status_kredit',
                'eligibility',
                'limit_breakdown',
                'trust_factor',
            ]);

            expect($breakdown['trust_score'])->toBe(80);
            expect($breakdown['credit_limit'])->toBe(5000000);
            expect($breakdown['saldo_kredit'])->toBe(2000000);
            expect($breakdown['available_credit'])->toBe(3000000);
            expect($breakdown['transactions_6_months'])->toBe(4);
        });
    });

    describe('Credit Eligibility Check', function () {
        it('returns ineligible for trust score below 50', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS008',
                'nama' => 'Ineligible Customer',
                'trust_score' => 40, // Below 50
                'credit_limit' => 1000000,
                'saldo_kredit' => 0,
            ]);

            $result = CustomerCreditScoringService::isCreditEligible($pelanggan);

            expect($result['eligible'])->toBeFalse();
            expect($result['available_limit'])->toBe(0);
            expect($result['message'])->toContain('terlalu rendah');
        });

        it('returns ineligible when credit limit is fully used', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS009',
                'nama' => 'Maxed Out Customer',
                'trust_score' => 75,
                'credit_limit' => 5000000,
                'saldo_kredit' => 5000000, // Fully used
            ]);

            $result = CustomerCreditScoringService::isCreditEligible($pelanggan);

            expect($result['eligible'])->toBeFalse();
            expect($result['available_limit'])->toBe(0);
            expect($result['message'])->toContain('Credit limit habis');
        });

        it('returns eligible with available limit', function () {
            $pelanggan = Pelanggan::create([
                'id_pelanggan' => 'CS010',
                'nama' => 'Eligible Customer',
                'trust_score' => 75,
                'credit_limit' => 5000000,
                'saldo_kredit' => 2000000, // Only half used
            ]);

            $result = CustomerCreditScoringService::isCreditEligible($pelanggan);

            expect($result['eligible'])->toBeTrue();
            expect($result['available_limit'])->toBe(3000000);
            expect($result['message'])->toContain('memenuhi syarat');
        });
    });
});
