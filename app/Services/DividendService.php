<?php

namespace App\Services;

use App\Models\JournalEntryLine;
use App\Models\Member;
use App\Models\MemberCapitalSnapshot;
use App\Models\MemberDividend;
use App\Models\Setting;
use App\Models\SurplusDistribution;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DividendService
{
    public static function compute(int $year): SurplusDistribution
    {
        $income = self::periodBalance(['4000', '4100', '4200'], 'credit', $year);
        $expenses = self::periodBalance(['5000', '5100', '5200'], 'debit', $year);
        $surplus = round($income - $expenses, 2);

        if ($surplus <= 0) {
            throw new RuntimeException("Financial year {$year} has no distributable surplus (income ".number_format($income, 2).', expenses '.number_format($expenses, 2).').');
        }

        $reservePct = (float) Setting::get('surplus_reserve_pct', 20);
        $educationPct = (float) Setting::get('surplus_education_pct', 5);
        $dividendSharePct = (float) Setting::get('surplus_dividend_share_pct', 60);

        $reserve = round($surplus * $reservePct / 100, 2);
        $education = round($surplus * $educationPct / 100, 2);
        $distributable = round($surplus - $reserve - $education, 2);
        $dividendPool = round($distributable * $dividendSharePct / 100, 2);
        $savingsPool = round($distributable - $dividendPool, 2);

        return SurplusDistribution::updateOrCreate(
            ['financial_year' => $year],
            [
                'total_loan_interest_income' => self::periodBalance(['4000'], 'credit', $year),
                'total_savings_interest_expense' => self::periodBalance(['5200'], 'debit', $year),
                'total_operational_expenses' => self::periodBalance(['5000', '5100'], 'debit', $year),
                'net_surplus' => $surplus,
                'statutory_reserve_pct' => $reservePct,
                'statutory_reserve_amount' => $reserve,
                'dividend_on_shares_pct' => $dividendSharePct,
                'interest_on_savings_pct' => round(100 - $dividendSharePct, 2),
                'education_welfare_pct' => $educationPct,
                'dividend_pool' => $dividendPool,
                'savings_interest_pool' => $savingsPool,
                'education_welfare_amount' => $education,
                'status' => 'DRAFT',
            ]
        );
    }

    /**
     * Credit member savings with dividends and savings interest, then post the ledger entry.
     */
    public static function post(SurplusDistribution $distribution, ?string $payoutDate = null): float
    {
        if ($distribution->status === 'COMPLETED') {
            throw new RuntimeException('This financial year has already been distributed.');
        }
        if ($distribution->status !== 'APPROVED') {
            throw new RuntimeException('The board must approve the distribution before payouts are posted.');
        }

        $members = Member::where('status', 'Active')->get();
        $year = (int) $distribution->financial_year;

        $rows = [];
        $totalShares = 0.0;
        $totalSavings = 0.0;
        foreach ($members as $member) {
            $account = $member->accounts()->where('is_active', true)->orderByDesc('balance')->first();
            if (! $account) {
                continue;
            }

            $avgShares = self::averageBalance($member, 'share_balance', $year, (float) $member->share_capital);
            $avgSavings = self::averageBalance($member, 'savings_balance', $year, (float) $member->total_savings);
            $rows[] = [$member, $account, $avgShares, $avgSavings];
            $totalShares += $avgShares;
            $totalSavings += $avgSavings;
        }

        if ($totalShares <= 0 && $totalSavings <= 0) {
            throw new RuntimeException('There is no member capital to distribute against.');
        }

        $paid = 0.0;
        $grossPaid = 0.0;
        $whtTotal = 0.0;
        $whtRate = (float) Setting::get('sys_wht_rate', 15);
        $dividendPool = (float) $distribution->dividend_pool;
        $savingsPool = (float) $distribution->savings_interest_pool;

        DB::transaction(function () use ($rows, $totalShares, $totalSavings, $dividendPool, $savingsPool, $distribution, $year, $payoutDate, $whtRate, &$paid, &$grossPaid, &$whtTotal) {
            foreach ($rows as [$member, $account, $avgShares, $avgSavings]) {
                $dividend = $totalShares > 0 ? round($dividendPool * $avgShares / $totalShares, 2) : 0.0;
                $interest = $totalSavings > 0 ? round($savingsPool * $avgSavings / $totalSavings, 2) : 0.0;
                $total = round($dividend + $interest, 2);

                if ($total <= 0) {
                    continue;
                }

                // WHT applies to dividends on shares only, not to interest on savings.
                $wht = round($dividend * $whtRate / 100, 2);
                $net = round($total - $wht, 2);
                $reference = 'DIV-'.strtoupper(Str::random(6));

                MemberDividend::create([
                    'distribution_id' => $distribution->id,
                    'member_id' => $member->id,
                    'avg_share_balance' => $avgShares,
                    'avg_savings_balance' => $avgSavings,
                    'dividend_earned' => $dividend,
                    'interest_earned' => $interest,
                    'withholding_tax' => $wht,
                    'total_payout' => $total,
                    'net_payout' => $net,
                    'wht_rate' => $whtRate,
                    'payout_method' => 'CASH',
                    'payout_status' => 'PAID',
                    'payout_date' => $payoutDate ?? now()->toDateString(),
                    'payout_reference' => $reference,
                ]);

                $account->balance += $net;
                $account->save();

                $member->total_savings += $net;
                $member->save();

                Transaction::create([
                    'reference' => $reference,
                    'account_number' => $account->account_number,
                    'transaction_type' => 'dividend',
                    'amount' => $net,
                    'narration' => 'Annual surplus distribution FY '.$year.' (gross '.number_format($total, 2).', WHT '.number_format($wht, 2).')',
                    'performed_by_username' => 'System',
                ]);

                MemberCapitalService::refresh($member);
                $paid += $net;
                $grossPaid += $total;
                $whtTotal += $wht;
            }

            if ($grossPaid > 0) {
                $lines = [
                    ['code' => '3100', 'debit' => round($grossPaid, 2)],
                    ['code' => '2000', 'credit' => round($paid, 2)],
                ];
                if ($whtTotal > 0) {
                    $lines[] = ['code' => '2400', 'credit' => round($whtTotal, 2)];
                }

                LedgerService::post('DIV', 'Surplus distribution FY '.$year.' (net of WHT)', $lines);
            }

            $distribution->update([
                'status' => 'COMPLETED',
                'agm_date' => $payoutDate ?? now()->toDateString(),
            ]);
        });

        return round($paid, 2);
    }

    private static function periodBalance(array $codes, string $side, int $year): float
    {
        $query = JournalEntryLine::whereIn('account_code', $codes)
            ->whereBetween('created_at', [$year.'-01-01 00:00:00', $year.'-12-31 23:59:59']);

        return $side === 'debit'
            ? (float) $query->sum(DB::raw('debit - credit'))
            : (float) $query->sum(DB::raw('credit - debit'));
    }

    private static function averageBalance(Member $member, string $column, int $year, float $fallback): float
    {
        $average = MemberCapitalSnapshot::where('member_id', $member->id)
            ->whereYear('snapshot_date', $year)
            ->avg($column);

        return $average !== null ? (float) $average : $fallback;
    }
}
