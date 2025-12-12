<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\CreditLimitService;
use App\Services\TrustScoreService;
use Illuminate\Console\Command;

class RecalculateTrustScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trust-score:recalculate 
                            {--customer= : Specific customer ID to recalculate}
                            {--all : Recalculate for all customers}
                            {--dry-run : Show what would be updated without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate trust scores and credit limits for customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->option('customer');
        $all = $this->option('all');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }

        if ($customerId) {
            return $this->recalculateForCustomer($customerId, $dryRun);
        }

        if ($all) {
            return $this->recalculateForAll($dryRun);
        }

        $this->error('Please specify --customer=ID or --all');

        return Command::FAILURE;
    }

    /**
     * Recalculate for a specific customer.
     */
    private function recalculateForCustomer(string $customerId, bool $dryRun): int
    {
        $pelanggan = Pelanggan::find($customerId);

        if (! $pelanggan) {
            $this->error("Customer {$customerId} not found");

            return Command::FAILURE;
        }

        $this->info("Recalculating for: {$pelanggan->nama} ({$customerId})");

        $oldScore = $pelanggan->trust_score;
        $oldLimit = $pelanggan->credit_limit;

        $scoreBreakdown = TrustScoreService::calculateFullScore($pelanggan);
        $limitBreakdown = CreditLimitService::calculateCreditLimit($pelanggan);

        $newScore = $scoreBreakdown['total'];
        $newLimit = $limitBreakdown['credit_limit'];

        // Display breakdown
        $this->displayBreakdown($scoreBreakdown, $limitBreakdown, $oldScore, (int) $oldLimit);

        if (! $dryRun) {
            $pelanggan->forceFill([
                'trust_score' => $newScore,
                'credit_limit' => $newLimit,
            ])->save();
            $this->info('✅ Updated successfully');
        }

        return Command::SUCCESS;
    }

    /**
     * Recalculate for all customers.
     */
    private function recalculateForAll(bool $dryRun): int
    {
        $customers = Pelanggan::all();
        $total = $customers->count();

        if ($total === 0) {
            $this->warn('No customers found');

            return Command::SUCCESS;
        }

        $this->info("Processing {$total} customers...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $unchanged = 0;

        foreach ($customers as $pelanggan) {
            $oldScore = $pelanggan->trust_score;
            $oldLimit = $pelanggan->credit_limit;

            $scoreBreakdown = TrustScoreService::calculateFullScore($pelanggan);
            $limitBreakdown = CreditLimitService::calculateCreditLimit($pelanggan);

            $newScore = $scoreBreakdown['total'];
            $newLimit = $limitBreakdown['credit_limit'];

            $scoreChanged = $oldScore !== $newScore;
            $limitChanged = $oldLimit !== $newLimit;

            if ($scoreChanged || $limitChanged) {
                if (! $dryRun) {
                    $pelanggan->forceFill([
                        'trust_score' => $newScore,
                        'credit_limit' => $newLimit,
                    ])->save();
                }
                $updated++;
            } else {
                $unchanged++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Unchanged', $unchanged],
                ['Total', $total],
            ]
        );

        if ($dryRun) {
            $this->warn('⚠️  Changes not saved (dry-run mode)');
        } else {
            $this->info('✅ Recalculation completed');
        }

        return Command::SUCCESS;
    }

    /**
     * Display trust score and credit limit breakdown.
     */
    private function displayBreakdown(array $scoreBreakdown, array $limitBreakdown, int $oldScore, int|float $oldLimit): void
    {
        $this->newLine();

        // Trust Score Breakdown
        $this->line('<fg=cyan>📊 Trust Score Breakdown:</>');
        $this->table(
            ['Component', 'Points'],
            [
                ['Baseline', $this->formatPoints($scoreBreakdown['baseline'])],
                ['Account Age', $this->formatPoints($scoreBreakdown['account_age'])],
                ['Installment History', $this->formatPoints($scoreBreakdown['installment_history'])],
                ['Shopping Frequency', $this->formatPoints($scoreBreakdown['shopping_frequency'])],
                ['Transaction Value', $this->formatPoints($scoreBreakdown['transaction_value'])],
                ['Active Arrears', $this->formatPoints($scoreBreakdown['active_arrears'])],
                ['<fg=yellow>TOTAL</>', "<fg=yellow>{$scoreBreakdown['total']}/100</>"],
            ]
        );

        // Credit Limit Breakdown
        $this->line('<fg=cyan>💳 Credit Limit Breakdown:</>');
        $this->table(
            ['Method', 'Value'],
            [
                ['50% Largest Transaction', 'Rp '.number_format($limitBreakdown['breakdown']['method_1_half_largest'], 0, ',', '.')],
                ['50% Avg Top 3', 'Rp '.number_format($limitBreakdown['breakdown']['method_2_avg_top3'], 0, ',', '.')],
                ['30% Last 6 Months', 'Rp '.number_format($limitBreakdown['breakdown']['method_3_six_months'], 0, ',', '.')],
                ['<fg=blue>Selected Base</>', '<fg=blue>Rp '.number_format($limitBreakdown['limit_base'], 0, ',', '.').'</>'],
                ['Trust Factor', $limitBreakdown['trust_factor'].'x'],
                ['<fg=yellow>CREDIT LIMIT</>', '<fg=yellow>Rp '.number_format($limitBreakdown['credit_limit'], 0, ',', '.').'</>'],
            ]
        );

        // Changes Summary
        $scoreChange = $scoreBreakdown['total'] - $oldScore;
        $limitChange = $limitBreakdown['credit_limit'] - (int) $oldLimit;

        $this->line('<fg=cyan>📈 Changes:</>');
        $this->line("Trust Score: {$oldScore} → {$scoreBreakdown['total']} ".$this->formatChange($scoreChange));
        $this->line('Credit Limit: Rp '.number_format((int) $oldLimit, 0, ',', '.').' → Rp '.number_format($limitBreakdown['credit_limit'], 0, ',', '.').' '.$this->formatChange($limitChange));
        $this->newLine();
    }

    /**
     * Format points with sign.
     */
    private function formatPoints(int $points): string
    {
        if ($points > 0) {
            return "<fg=green>+{$points}</>";
        } elseif ($points < 0) {
            return "<fg=red>{$points}</>";
        }

        return (string) $points;
    }

    /**
     * Format change with color.
     */
    private function formatChange(int $change): string
    {
        if ($change > 0) {
            return "<fg=green>(+{$change})</>";
        } elseif ($change < 0) {
            return "<fg=red>({$change})</>";
        }

        return '<fg=gray>(no change)</>';
    }
}
