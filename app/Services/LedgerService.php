<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Support\Str;
use RuntimeException;

class LedgerService
{
    /**
     * Post a balanced journal entry. Every line must contain a chart-of-accounts
     * code and either a debit or a credit amount.
     */
    public static function post(string $prefix, string $description, array $lines, string $referenceType = null, $referenceId = null): JournalEntry
    {
        $clean = [];

        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0) {
                throw new RuntimeException('Journal line amounts cannot be negative.');
            }
            if ($debit > 0 && $credit > 0) {
                throw new RuntimeException('A journal line cannot carry both a debit and a credit.');
            }
            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $clean[] = [
                'code' => (string) $line['code'],
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        if (count($clean) < 2) {
            throw new RuntimeException('A journal entry requires at least two non-zero lines.');
        }

        $totalDebit = round(array_sum(array_column($clean, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($clean, 'credit')), 2);

        if (abs($totalDebit - $totalCredit) > 0.005) {
            throw new RuntimeException("Unbalanced journal entry: debit {$totalDebit} vs credit {$totalCredit}.");
        }

        $accounts = ChartOfAccount::whereIn('code', array_column($clean, 'code'))->get()->keyBy('code');

        foreach ($clean as $line) {
            if (! $accounts->has($line['code'])) {
                throw new RuntimeException('Unknown chart-of-accounts code: '.$line['code']);
            }
        }

        $reference = strtoupper($prefix).'-'.strtoupper(Str::random(8));

        $entry = JournalEntry::create([
            'reference' => $reference,
            'description' => $description,
            'posted_by_name' => auth()->user() ? (auth()->user()->username ?? auth()->user()->email) : 'System',
            'posted_at' => now(),
            'is_reversed' => false,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);

        foreach ($clean as $line) {
            $entry->lineItems()->create([
                'account_code' => $line['code'],
                'account_name' => $accounts[$line['code']]->name,
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }

        return $entry;
    }

    /**
     * Reverse an entry by posting the mirror entry; the original is never deleted.
     */
    public static function reverse(JournalEntry $entry, string $reason): JournalEntry
    {
        if ($entry->is_reversed) {
            throw new RuntimeException('This journal entry has already been reversed.');
        }

        $lines = $entry->lineItems->map(fn ($line) => [
            'code' => $line->account_code,
            'debit' => $line->credit,
            'credit' => $line->debit,
        ])->all();

        $reversal = self::post(
            'REV',
            'Reversal of '.$entry->reference.': '.$reason,
            $lines
        );

        $entry->update(['is_reversed' => true]);

        return $reversal;
    }

    public static function accountBalance(string $code): float
    {
        $account = ChartOfAccount::where('code', $code)->first();
        if (! $account) {
            return 0.0;
        }

        $debits = (float) $account->journalLines()->sum('debit');
        $credits = (float) $account->journalLines()->sum('credit');

        return in_array($account->type, ['Asset', 'Expense'], true)
            ? $debits - $credits
            : $credits - $debits;
    }
}
