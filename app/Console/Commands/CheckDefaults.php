<?php

namespace App\Console\Commands;

use App\Models\GuarantorPledge;
use App\Models\Loan;
use App\Models\Member;
use App\Services\MemberCapitalService;
use App\Services\RepaymentScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckDefaults extends Command
{
    protected $signature = 'sacco:check-defaults {--mock-days= : Force the number of days overdue for testing}';
    protected $description = 'Applies sequential default recovery stages based on repayment schedule ageing';

    public function handle()
    {
        $this->info('Starting defaulted loans recovery check...');

        $loans = Loan::whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])->get();

        foreach ($loans as $loan) {
            $daysOverdue = (int) $this->option('mock-days');

            if (! $this->option('mock-days')) {
                if (! $loan->schedules()->exists()) {
                    RepaymentScheduleService::generate($loan);
                }
                $daysOverdue = RepaymentScheduleService::refreshOverdue($loan);
            }

            if ($daysOverdue <= 0) {
                continue;
            }

            $loan->update(['status' => 'defaulted']);

            if ($daysOverdue > 90) {
                $this->stageFour($loan);
            }
            if ($daysOverdue > 60) {
                $this->stageThree($loan);
            }
            if ($daysOverdue > 30) {
                $this->stageTwo($loan);
            }

            $this->stageOne($loan);
        }

        $this->info('Default recovery check completed.');

        return 0;
    }

    private function stageOne(Loan $loan): void
    {
        if ((int) $loan->default_stage < 1) {
            $loan->update(['default_stage' => 1]);
            $this->info("Loan {$loan->id}: Stage 1 - defaulter and guarantors flagged for reminders.");
        }
    }

    /**
     * Offset arrears against the borrower's own unencumbered savings.
     */
    private function stageTwo(Loan $loan): void
    {
        if ((int) $loan->default_stage >= 2) {
            return;
        }

        $loan->update(['default_stage' => 2]);

        $outstanding = RepaymentScheduleService::outstanding($loan);
        $member = $loan->member;
        $account = $member?->accounts()->where('is_active', true)->orderByDesc('balance')->first();
        if (! $member || ! $account || $outstanding <= 0) {
            return;
        }

        $seizable = $this->seizableSavings($member);
        $deduction = round(min($seizable, (float) $account->balance, $outstanding), 2);

        if ($deduction <= 0) {
            $this->info("Loan {$loan->id}: Stage 2 - no unencumbered savings available.");
            return;
        }

        DB::transaction(function () use ($loan, $member, $account, $deduction) {
            $account->balance -= $deduction;
            $account->save();

            $member->total_savings -= $deduction;
            $member->save();

            \App\Models\Transaction::create([
                'reference' => 'LIQ-'.strtoupper(Str::random(6)),
                'account_number' => $account->account_number,
                'transaction_type' => 'loan_recovery',
                'amount' => $deduction,
                'narration' => 'Stage 2 default recovery - borrower savings offset for Loan #'.$loan->id,
                'performed_by_username' => 'System',
            ]);

            RepaymentScheduleService::applyPayment(
                $loan, $deduction, '2000',
                'Stage 2 default recovery - Loan #'.$loan->id,
                'loan', $loan->id, 'System'
            );

            MemberCapitalService::refresh($member);
        });

        $this->info("Loan {$loan->id}: Stage 2 - UGX ".number_format($deduction, 2).' recovered from borrower savings.');
    }

    /**
     * Call on guarantors for the remaining arrears, capped by each pledge and
     * by the guarantor's unencumbered savings.
     */
    private function stageThree(Loan $loan): void
    {
        if ((int) $loan->default_stage >= 3) {
            return;
        }

        $loan->update(['default_stage' => 3]);

        foreach ($loan->guarantorPledges()->whereIn('status', ['frozen', 'active', 'partially_enforced'])->get() as $pledge) {
            $outstanding = RepaymentScheduleService::outstanding($loan);
            if ($outstanding <= 0) {
                break;
            }

            $guarantor = $pledge->guarantor;
            $account = $guarantor?->accounts()->where('is_active', true)->orderByDesc('balance')->first();
            if (! $guarantor || ! $account) {
                continue;
            }

            $remaining = $pledge->remainingLiability();
            $seizable = $this->seizableSavings($guarantor, $pledge);
            $deduction = round(min($remaining, $seizable, (float) $account->balance, $outstanding), 2);

            if ($deduction <= 0) {
                continue;
            }

            DB::transaction(function () use ($loan, $guarantor, $account, $pledge, $deduction) {
                $account->balance -= $deduction;
                $account->save();

                $guarantor->total_savings -= $deduction;
                $guarantor->save();

                $enforced = round((float) $pledge->amount_enforced + $deduction, 2);
                $pledge->update([
                    'amount_enforced' => $enforced,
                    'status' => $enforced + 0.005 >= (float) $pledge->pledged_amount ? 'liquidated' : 'partially_enforced',
                ]);

                \App\Models\Transaction::create([
                    'reference' => 'GLIQ-'.strtoupper(Str::random(6)),
                    'account_number' => $account->account_number,
                    'transaction_type' => 'loan_recovery',
                    'amount' => $deduction,
                    'narration' => 'Stage 3 default recovery - guarantor '.$guarantor->membership_number.' for Loan #'.$loan->id,
                    'performed_by_username' => 'System',
                ]);

                RepaymentScheduleService::applyPayment(
                    $loan, $deduction, '2000',
                    'Stage 3 guarantor recovery - Loan #'.$loan->id,
                    'loan', $loan->id, 'System'
                );

                MemberCapitalService::refresh($guarantor);
            });

            $this->info("Loan {$loan->id}: Stage 3 - UGX ".number_format($deduction, 2).' recovered from guarantor '.$guarantor->membership_number.'.');
        }
    }

    private function stageFour(Loan $loan): void
    {
        if ((int) $loan->default_stage < 4) {
            $loan->update(['default_stage' => 4]);
            $this->info("Loan {$loan->id}: Stage 4 - escalated for external debt collection.");
        }
    }

    /**
     * Savings the member can legally lose: total savings less their own
     * outstanding loan lien and all other guarantee obligations.
     */
    private function seizableSavings(Member $member, ?GuarantorPledge $exclude = null): float
    {
        $capital = MemberCapitalService::calculate($member);

        $otherGuarantees = 0.0;
        foreach ($member->guarantorPledges()
                     ->whereIn('status', ['active', 'frozen', 'partially_enforced'])
                     ->get() as $pledge) {
            if ($exclude && $pledge->id === $exclude->id) {
                continue;
            }
            $otherGuarantees += $pledge->remainingLiability();
        }

        return max(0, (float) $member->total_savings - $capital['lien_amount'] - $otherGuarantees);
    }
}
