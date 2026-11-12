<?php

namespace App\Console\Commands;

use App\Services\DividendService;
use Illuminate\Console\Command;
use RuntimeException;

class DistributeSurplus extends Command
{
    protected $signature = 'sacco:distribute-surplus {year : Financial year to distribute} {--post : Credit member accounts and post the ledger entry}';
    protected $description = 'Computes the annual surplus distribution from the ledger and optionally pays dividends and savings interest';

    public function handle()
    {
        $year = (int) $this->argument('year');
        if ($year < 2000) {
            $this->error('Provide a valid financial year.');
            return 1;
        }

        try {
            $distribution = DividendService::compute($year);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->table(['Item', 'Amount (UGX)'], [
            ['Interest income', number_format($distribution->total_loan_interest_income, 2)],
            ['Savings interest expense', number_format($distribution->total_savings_interest_expense, 2)],
            ['Operational expenses', number_format($distribution->total_operational_expenses, 2)],
            ['Net surplus', number_format($distribution->net_surplus, 2)],
            ['Statutory reserve', number_format($distribution->statutory_reserve_amount, 2)],
            ['Education / welfare', number_format($distribution->education_welfare_amount, 2)],
            ['Dividend pool', number_format($distribution->dividend_pool, 2)],
            ['Savings interest pool', number_format($distribution->savings_interest_pool, 2)],
        ]);

        if (! $this->option('post')) {
            $this->info("Draft distribution saved for {$year}. Approve it in Treasury > Dividends, then post payouts.");
            return 0;
        }

        try {
            $paid = DividendService::post($distribution);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->info("Surplus distribution for {$year} completed. UGX ".number_format($paid, 2).' credited to members.');

        return 0;
    }
}
