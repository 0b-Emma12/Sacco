<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RepaymentScheduleService
{
    public static function generate(Loan $loan): void
    {
        if ($loan->schedules()->exists()) {
            return;
        }

        $n = max(1, (int) $loan->term_months);
        $principal = (float) $loan->principal_amount;
        $rate = (float) $loan->interest_rate / 100;
        $method = $loan->interest_method ?: 'REDUCING_BALANCE';
        $start = $loan->disbursed_at ? Carbon::parse($loan->disbursed_at) : now()->startOfDay();

        $rows = [];
        $balance = $principal;

        if ($method === 'FLAT') {
            $totalInterest = round($principal * $rate * $n, 2);
            $principalEach = round($principal / $n, 2);
            $interestEach = round($totalInterest / $n, 2);
            $shownInterest = 0.0;

            for ($i = 1; $i <= $n; $i++) {
                $principalDue = $i === $n ? round($balance, 2) : $principalEach;
                $interestDue = $i === $n ? round($totalInterest - $shownInterest, 2) : $interestEach;
                $shownInterest += $interestDue;
                $balance = round($balance - $principalDue, 2);

                $rows[] = [
                    'installment_number' => $i,
                    'due_date' => $start->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'principal_due' => $principalDue,
                    'interest_due' => $interestDue,
                    'total_due' => round($principalDue + $interestDue, 2),
                ];
            }
        } else {
            $emi = $rate > 0
                ? $principal * $rate * pow(1 + $rate, $n) / (pow(1 + $rate, $n) - 1)
                : $principal / $n;

            for ($i = 1; $i <= $n; $i++) {
                $interestDue = round($balance * $rate, 2);
                $principalDue = $i === $n ? round($balance, 2) : round($emi - $interestDue, 2);
                $principalDue = max(0, $principalDue);
                $balance = round($balance - $principalDue, 2);

                $rows[] = [
                    'installment_number' => $i,
                    'due_date' => $start->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'principal_due' => $principalDue,
                    'interest_due' => $interestDue,
                    'total_due' => round($principalDue + $interestDue, 2),
                ];
            }
        }

        $totalRepayable = round(array_sum(array_column($rows, 'total_due')), 2);

        $loan->schedules()->createMany($rows);

        $loan->update([
            'monthly_installment' => round($totalRepayable / $n, 2),
            'total_repayable' => $totalRepayable,
            'outstanding_balance' => $totalRepayable,
            'outstanding_principal' => $principal,
            'maturity_date' => end($rows)['due_date'],
        ]);
    }

    public static function outstanding(Loan $loan): float
    {
        $sum = (float) $loan->schedules()->sum(\DB::raw('total_due - total_paid'));

        return max(0, round($sum, 2));
    }

    public static function principalOutstanding(Loan $loan): float
    {
        $sum = (float) $loan->schedules()->sum(\DB::raw('principal_due - principal_paid'));

        return max(0, round($sum, 2));
    }

    public static function reschedule(Loan $loan, int $months, int $graceMonths = 0): float
    {
        $months = max(1, $months);
        $graceMonths = max(0, $graceMonths);

        $principal = self::principalOutstanding($loan);
        if ($principal <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'restructuring' => 'This loan has no outstanding principal to reschedule.',
            ]);
        }

        $paidCount = $loan->schedules()->where('payment_status', 'PAID')->count();
        $loan->schedules()->where('payment_status', '!=', 'PAID')->delete();

        $rate = (float) $loan->interest_rate / 100;
        $method = $loan->interest_method ?: 'REDUCING_BALANCE';
        $start = now()->startOfDay();

        $rows = [];
        $balance = $principal;

        if ($method === 'FLAT') {
            $totalInterest = round($principal * $rate * $months, 2);
            $principalEach = round($principal / $months, 2);
            $interestEach = round($totalInterest / $months, 2);
            $shownInterest = 0.0;

            for ($i = 1; $i <= $months; $i++) {
                $principalDue = $i === $months ? round($balance, 2) : $principalEach;
                $interestDue = $i === $months ? round($totalInterest - $shownInterest, 2) : $interestEach;
                $shownInterest += $interestDue;
                $balance = round($balance - $principalDue, 2);
                $rows[] = [
                    'installment_number' => $paidCount + $i,
                    'due_date' => $start->copy()->addMonthsNoOverflow($graceMonths + $i)->toDateString(),
                    'principal_due' => $principalDue,
                    'interest_due' => $interestDue,
                    'total_due' => round($principalDue + $interestDue, 2),
                ];
            }
        } else {
            $emi = $rate > 0
                ? $principal * $rate * pow(1 + $rate, $months) / (pow(1 + $rate, $months) - 1)
                : $principal / $months;

            for ($i = 1; $i <= $months; $i++) {
                $interestDue = round($balance * $rate, 2);
                $principalDue = $i === $months ? round($balance, 2) : max(0, round($emi - $interestDue, 2));
                $balance = round($balance - $principalDue, 2);
                $rows[] = [
                    'installment_number' => $paidCount + $i,
                    'due_date' => $start->copy()->addMonthsNoOverflow($graceMonths + $i)->toDateString(),
                    'principal_due' => $principalDue,
                    'interest_due' => $interestDue,
                    'total_due' => round($principalDue + $interestDue, 2),
                ];
            }
        }

        $newTotal = round(array_sum(array_column($rows, 'total_due')), 2);
        $installment = round($newTotal / $months, 2);

        $loan->schedules()->createMany($rows);

        $paidToDate = (float) $loan->amount_paid;

        $loan->update([
            'term_months' => $paidCount + $months,
            'monthly_installment' => $installment,
            'total_repayable' => round($paidToDate + $newTotal, 2),
            'outstanding_balance' => $newTotal,
            'outstanding_principal' => $principal,
            'maturity_date' => end($rows)['due_date'],
            'status' => $paidToDate > 0 ? 'partially_paid' : 'disbursed',
            'default_stage' => 0,
        ]);

        return $installment;
    }

    /**
     * Apply a payment to the oldest installments, interest first.
     *
     * @return array{applied:float,interest:float,principal:float,remaining:float,installment_number:?int}
     */
    public static function allocate(Loan $loan, float $amount): array
    {
        $remaining = round(max(0, $amount), 2);
        $interestPortion = 0.0;
        $principalPortion = 0.0;
        $lastInstallment = null;

        foreach ($loan->schedules()->orderBy('installment_number')->get() as $schedule) {
            if ($remaining <= 0) {
                break;
            }

            $before = $remaining;
            $dueInterest = round((float) $schedule->interest_due - (float) $schedule->interest_paid, 2);
            if ($dueInterest > 0) {
                $pay = min($remaining, $dueInterest);
                $schedule->interest_paid = round((float) $schedule->interest_paid + $pay, 2);
                $interestPortion += $pay;
                $remaining = round($remaining - $pay, 2);
            }

            $duePrincipal = round((float) $schedule->principal_due - (float) $schedule->principal_paid, 2);
            if ($remaining > 0 && $duePrincipal > 0) {
                $pay = min($remaining, $duePrincipal);
                $schedule->principal_paid = round((float) $schedule->principal_paid + $pay, 2);
                $principalPortion += $pay;
                $remaining = round($remaining - $pay, 2);
            }

            $schedule->total_paid = round((float) $schedule->interest_paid + (float) $schedule->principal_paid, 2);
            self::markScheduleStatus($schedule);
            $schedule->save();

            if ($remaining < $before) {
                $lastInstallment = (int) $schedule->installment_number;
            }
        }

        $applied = round($amount - $remaining, 2);

        $loan->amount_paid = round((float) $loan->amount_paid + $applied, 2);
        $loan->outstanding_balance = self::outstanding($loan);
        $loan->outstanding_principal = self::principalOutstanding($loan);

        if ($loan->outstanding_balance <= 0.005) {
            $loan->status = 'paid';
        } elseif ($applied > 0) {
            $loan->status = 'partially_paid';
        }

        $loan->save();

        return [
            'applied' => $applied,
            'interest' => round($interestPortion, 2),
            'principal' => round($principalPortion, 2),
            'remaining' => $loan->outstanding_balance,
            'installment_number' => $lastInstallment,
        ];
    }

    public static function applyPayment(
        Loan $loan,
        float $amount,
        string $debitCode,
        string $narration,
        string $referenceType,
        $referenceId,
        string $performedBy
    ): array {
        self::generate($loan);

        $result = self::allocate($loan, $amount);

        if ($result['applied'] > 0) {
            LoanRepayment::create([
                'loan_id' => $loan->id,
                'installment_number' => $result['installment_number'] ?? null,
                'amount_paid' => $result['applied'],
                'principal_paid' => $result['principal'],
                'interest_paid' => $result['interest'],
                'balance_after' => $result['remaining'],
                'narration' => $narration,
                'recorded_by' => $performedBy,
            ]);

            LedgerService::post('RPY', $narration, [
                ['code' => $debitCode, 'debit' => $result['applied']],
                ['code' => '1200', 'credit' => $result['principal']],
                ['code' => '4000', 'credit' => $result['interest']],
            ], $referenceType, $referenceId);

            if ($loan->fresh()->status === 'paid') {
                $loan->guarantorPledges()->update(['status' => 'released']);
            }

            MemberCapitalService::refresh($loan->member);
        }

        return $result;
    }

    /**
     * Recalculate per-installment days overdue and statuses.
     */
    public static function refreshOverdue(Loan $loan): int
    {
        $maxDays = 0;
        $today = now()->startOfDay();

        foreach ($loan->schedules()->get() as $schedule) {
            if (in_array($schedule->payment_status, ['PAID', 'WAIVED'], true)) {
                $schedule->days_overdue = 0;
                $schedule->save();
                continue;
            }

            $due = Carbon::parse($schedule->due_date)->startOfDay();
            $days = $due->lt($today) ? (int) $due->diffInDays($today) : 0;
            $schedule->days_overdue = $days;
            self::markScheduleStatus($schedule);
            $schedule->save();
            $maxDays = max($maxDays, $days);
        }

        return $maxDays;
    }

    private static function markScheduleStatus($schedule): void
    {
        $paid = (float) $schedule->total_paid;
        $due = (float) $schedule->total_due;
        $dueDate = Carbon::parse($schedule->due_date)->startOfDay();

        if ($paid >= $due - 0.005) {
            $schedule->payment_status = 'PAID';
            $schedule->days_overdue = 0;
        } elseif ($dueDate->lt(now()->startOfDay())) {
            $schedule->payment_status = 'OVERDUE';
            $schedule->days_overdue = (int) $dueDate->diffInDays(now()->startOfDay());
        } elseif ($paid > 0) {
            $schedule->payment_status = 'PARTIAL';
            $schedule->days_overdue = 0;
        } else {
            $schedule->payment_status = 'PENDING';
            $schedule->days_overdue = 0;
        }
    }
}
