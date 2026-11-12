<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Services\LedgerService;
use Illuminate\Console\Command;

class BackfillLedger extends Command
{
    protected $signature = 'sacco:backfill-ledger {--force : Post entries even when the ledger already has data}';
    protected $description = 'Posts historical cash-book transactions into the double-entry ledger (idempotent)';

    public function handle()
    {
        if (JournalEntry::exists() && ! $this->option('force')) {
            $this->info('The ledger already contains entries; backfill skipped. Use --force to post only transactions that are not yet linked.');

            return 0;
        }

        $posted = 0;
        $skipped = 0;

        foreach (Transaction::orderBy('created_at')->get() as $transaction) {
            $linked = JournalEntry::where('reference_type', 'transaction')
                ->where('reference_id', $transaction->id)
                ->exists();

            if ($linked || JournalEntry::where('description', 'like', '%'.$transaction->reference.'%')->exists()) {
                $skipped++;
                continue;
            }

            $lines = $this->linesFor($transaction);
            if (! $lines) {
                $this->warn("Skipped transaction {$transaction->reference} ({$transaction->transaction_type}): no ledger mapping.");
                $skipped++;
                continue;
            }

            $entry = LedgerService::post(
                'BKF',
                'Backfill '.$transaction->transaction_type.' '.$transaction->reference,
                $lines,
                'transaction',
                $transaction->id
            );

            $timestamp = $transaction->created_at ?? now();
            $entry->update(['posted_at' => $timestamp, 'created_at' => $timestamp]);
            $entry->lineItems()->update(['created_at' => $timestamp, 'updated_at' => $timestamp]);

            $posted++;
        }

        $this->info("Ledger backfill complete: {$posted} posted, {$skipped} skipped.");

        return 0;
    }

    private function linesFor(Transaction $transaction): ?array
    {
        $amount = (float) $transaction->amount;
        if ($amount <= 0) {
            return null;
        }

        $narration = strtolower((string) $transaction->narration);

        switch ($transaction->transaction_type) {
            case 'deposit':
                return [
                    ['code' => '1000', 'debit' => $amount],
                    ['code' => '2000', 'credit' => $amount],
                ];
            case 'withdrawal':
                if (str_contains($narration, 'fee')) {
                    return [
                        ['code' => '2000', 'debit' => $amount],
                        ['code' => '4100', 'credit' => $amount],
                    ];
                }
                return [
                    ['code' => '2000', 'debit' => $amount],
                    ['code' => '1000', 'credit' => $amount],
                ];
            case 'share_transfer':
                return [
                    ['code' => '2000', 'debit' => $amount],
                    ['code' => '3000', 'credit' => $amount],
                ];
            case 'fee':
                return [
                    ['code' => '2000', 'debit' => $amount],
                    ['code' => '4100', 'credit' => $amount],
                ];
            case 'loan_disbursement':
                return [
                    ['code' => '1200', 'debit' => $amount],
                    ['code' => '2000', 'credit' => $amount],
                ];
            case 'loan_repayment':
                return [
                    ['code' => '1000', 'debit' => $amount],
                    ['code' => '1200', 'credit' => $amount],
                ];
            case 'loan_recovery':
                return [
                    ['code' => '2000', 'debit' => $amount],
                    ['code' => '1200', 'credit' => $amount],
                ];
            case 'dividend':
                return [
                    ['code' => '3100', 'debit' => $amount],
                    ['code' => '2000', 'credit' => $amount],
                ];
            default:
                return null;
        }
    }
}
