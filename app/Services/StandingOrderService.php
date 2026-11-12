<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\StandingOrder;
use App\Models\StandingOrderRun;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StandingOrderService
{
    private const LOAN_SETTLED = 'The loan is already settled.';

    private const MAX_CATCH_UP = 12;

    public static function maxCatchUp(): int
    {
        return self::MAX_CATCH_UP;
    }

    public static function nextDate(Carbon $date, string $frequency): Carbon
    {
        $next = $date->copy();

        return match ($frequency) {
            'WEEKLY' => $next->addWeek(),
            'FORTNIGHTLY' => $next->addWeeks(2),
            'QUARTERLY' => $next->addMonthsNoOverflow(3),
            default => $next->addMonthNoOverflow(),
        };
    }

    /**
     * Post one due cycle of a mandate. The due date is the order's next run
     * date, so replaying a cycle is impossible (next_run_date moves forward
     * inside the same transaction and the run row is unique per due date).
     */
    public static function execute(StandingOrder $order, ?Carbon $asOf = null): StandingOrderRun
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        $order = StandingOrder::lockForUpdate()->findOrFail($order->id);

        if (! $order->isActive()) {
            throw new RuntimeException('Only an active standing order can be processed.');
        }
        if ($order->next_run_date->copy()->startOfDay()->gt($asOf)) {
            throw new RuntimeException('This standing order is not yet due (next run '.$order->next_run_date->format('d M Y').').');
        }
        if ($order->end_date && $order->next_run_date->gt($order->end_date)) {
            $order->update(['status' => 'COMPLETED']);
            throw new RuntimeException('The mandate end date has passed; the order is now completed.');
        }

        return DB::transaction(function () use ($order, $asOf) {
            $due = $order->next_run_date->copy();
            $amount = round((float) $order->amount, 2);

            if (StandingOrderRun::where('standing_order_id', $order->id)->whereDate('due_date', $due)->exists()) {
                throw new RuntimeException('This cycle ('.$due->format('d M Y').') has already been processed.');
            }

            $status = 'POSTED';
            $note = null;
            $transactionReference = null;
            $journalReference = null;
            $completed = false;
            $postedAmount = $amount;

            try {
                [$transactionReference, $journalReference, $postedAmount] = DB::transaction(fn () => $order->destination_type === 'loan'
                    ? self::postToLoan($order, $due)
                    : self::postToSavings($order, $due));
            } catch (ValidationException $e) {
                $status = 'FAILED';
                $note = implode(' ', Arr::flatten($e->errors()));
            } catch (RuntimeException $e) {
                $status = 'FAILED';
                $note = $e->getMessage();
                $completed = $note === self::LOAN_SETTLED;
            }

            $run = StandingOrderRun::create([
                'standing_order_id' => $order->id,
                'due_date' => $due->toDateString(),
                'amount' => round((float) $postedAmount, 2),
                'status' => $status,
                'transaction_reference' => $transactionReference,
                'journal_reference' => $journalReference,
                'note' => $note,
                'processed_at' => now(),
            ]);

            $next = self::nextDate($due, $order->frequency);

            $loanFinished = false;
            if ($order->destination_type === 'loan') {
                $loan = Loan::find($order->loan_id);
                $loanFinished = ! $loan || ! in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true);
            }

            $finished = $completed
                || ($order->end_date && $next->gt($order->end_date))
                || $loanFinished;

            $order->update([
                'last_run_date' => $status === 'POSTED' ? $due->toDateString() : $order->last_run_date,
                'runs_completed' => (int) $order->runs_completed + ($status === 'POSTED' ? 1 : 0),
                'next_run_date' => $next->toDateString(),
                'status' => $finished ? 'COMPLETED' : 'ACTIVE',
            ]);

            return $run;
        });
    }

    /**
     * Process every cycle that has fallen due up to the given date. The cap
     * stops a long-neglected mandate from mass-posting in one invocation.
     */
    public static function processDue(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        $summary = ['orders' => 0, 'posted' => 0, 'failed' => 0, 'amount' => 0.0];

        $orders = StandingOrder::where('status', 'ACTIVE')
            ->whereDate('next_run_date', '<=', $asOf->toDateString())
            ->orderBy('next_run_date')
            ->get();

        foreach ($orders as $order) {
            $summary['orders']++;

            for ($cycle = 0; $cycle < self::MAX_CATCH_UP; $cycle++) {
                $order->refresh();

                if (! $order->isActive() || $order->next_run_date->copy()->startOfDay()->gt($asOf)) {
                    break;
                }

                try {
                    $run = self::execute($order, $asOf);
                } catch (RuntimeException $e) {
                    break;
                }

                if ($run->isPosted()) {
                    $summary['posted']++;
                    $summary['amount'] = round($summary['amount'] + (float) $run->amount, 2);
                } else {
                    $summary['failed']++;
                }
            }
        }

        return $summary;
    }

    /**
     * @return array{0: string, 1: string, 2: float} transaction reference, journal reference, amount posted
     */
    private static function postToSavings(StandingOrder $order, Carbon $due): array
    {
        $member = $order->member;
        $account = $order->account;
        $amount = round((float) $order->amount, 2);

        if (! $member || ! $account) {
            throw new RuntimeException('The member or destination savings account no longer exists.');
        }
        if (! $account->is_active) {
            throw new RuntimeException('The destination savings account is inactive.');
        }
        if (in_array(strtolower((string) $member->status), ['exited', 'closed', 'deceased'], true)) {
            throw new RuntimeException('The member has left the SACCO; the mandate must be cancelled.');
        }
        if ($member->transactionsBlockedByKyc()) {
            throw new RuntimeException('KYC verification is outstanding; standing order blocked from '.$member->kycGraceEndsAt()->format('d M Y').'.');
        }

        $account->balance += $amount;
        $account->save();

        $member->total_savings += $amount;
        if (strtolower((string) $member->status) === 'dormant') {
            $member->status = 'Active';
        }
        $member->save();

        $narration = 'Standing order '.$order->mandate_reference.' ('.$order->frequencyLabel().') from '.$order->sourceLabel();
        $transaction = Transaction::create([
            'reference' => 'SO-'.strtoupper(Str::random(8)),
            'account_number' => $account->account_number,
            'transaction_type' => 'standing_order',
            'amount' => $amount,
            'narration' => $narration.' for '.$due->format('d M Y'),
            'performed_by_username' => 'System',
        ]);

        $entry = LedgerService::post(
            'SO',
            'Standing order '.$order->mandate_reference.' - '.$member->membership_number,
            [
                ['code' => '1000', 'debit' => $amount],
                ['code' => '2000', 'credit' => $amount],
            ],
            'standing_order',
            $order->id
        );

        MemberCapitalService::refresh($member);

        return [$transaction->reference, $entry->reference, $amount];
    }

    /**
     * @return array{0: string, 1: string, 2: float} transaction reference, journal reference, amount applied to the loan
     */
    private static function postToLoan(StandingOrder $order, Carbon $due): array
    {
        $member = $order->member;
        $loan = $order->loan;
        $amount = round((float) $order->amount, 2);

        if (! $member || ! $loan) {
            throw new RuntimeException('The member or destination loan no longer exists.');
        }
        if (! in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true)) {
            throw new RuntimeException(self::LOAN_SETTLED);
        }

        $outstanding = RepaymentScheduleService::outstanding($loan);
        if ($outstanding <= 0) {
            throw new RuntimeException(self::LOAN_SETTLED);
        }

        $applied = round(min($amount, $outstanding), 2);
        $narration = 'Standing order '.$order->mandate_reference.' to Loan #'.$loan->id.' ('.$due->format('d M Y').')';

        $result = RepaymentScheduleService::applyPayment(
            $loan,
            $applied,
            '1000',
            $narration,
            'standing_order',
            $order->id,
            'System'
        );

        if ($result['applied'] <= 0) {
            throw new RuntimeException('No amount could be applied to the loan.');
        }

        $transactionReference = null;
        $account = $member->accounts()->where('is_active', true)->orderByDesc('balance')->first();
        if ($account) {
            $transactionReference = Transaction::create([
                'reference' => 'SO-'.strtoupper(Str::random(8)),
                'account_number' => $account->account_number,
                'transaction_type' => 'loan_repayment',
                'amount' => $result['applied'],
                'narration' => $narration,
                'performed_by_username' => 'System',
            ])->reference;
        }

        $entry = \App\Models\JournalEntry::where('reference_type', 'standing_order')
            ->where('reference_id', $order->id)
            ->latest('id')
            ->first();

        MemberCapitalService::refresh($member);

        return [$transactionReference ?? '', $entry->reference ?? '', (float) $result['applied']];
    }
}
