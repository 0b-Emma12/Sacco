<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberCapital;
use App\Models\Setting;

class MemberCapitalService
{
    public static function calculate(Member $member): array
    {
        $savings = (float) $member->total_savings;
        $shares = (float) $member->share_capital;
        $sharePrice = (float) Setting::get('sys_share_price', 10000);

        $guaranteeLiability = 0.0;
        foreach ($member->guarantorPledges()
                     ->whereIn('status', ['active', 'frozen', 'partially_enforced'])
                     ->get() as $pledge) {
            $guaranteeLiability += max(0, (float) $pledge->pledged_amount - (float) $pledge->amount_enforced);
        }

        $loanLien = 0.0;
        foreach ($member->loans()
                     ->whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])
                     ->get() as $loan) {
            $loanLien += self::outstandingPrincipal($loan);
        }

        $lien = min($savings, $loanLien);
        $available = max(0, $savings - $lien - $guaranteeLiability);

        return [
            'share_count' => $sharePrice > 0 ? round($shares / $sharePrice, 4) : 0,
            'share_balance' => round($shares, 2),
            'savings_balance' => round($savings, 2),
            'mandatory_savings' => 0,
            'voluntary_savings' => round($savings, 2),
            'lien_amount' => round($lien, 2),
            'guarantee_liability' => round($guaranteeLiability, 2),
            'available_savings' => round($available, 2),
            'total_equity' => round($savings + $shares, 2),
        ];
    }

    public static function refresh(Member $member): MemberCapital
    {
        $member->refresh();

        return MemberCapital::updateOrCreate(
            ['member_id' => $member->id],
            self::calculate($member)
        );
    }

    public static function outstandingPrincipal($loan): float
    {
        if ((float) $loan->principal_amount <= 0) {
            return 0.0;
        }

        $schedules = $loan->schedules()->get();
        if ($schedules->isNotEmpty()) {
            return max(0, (float) $schedules->sum(fn ($s) => (float) $s->principal_due - (float) $s->principal_paid));
        }

        if ($loan->outstanding_principal !== null) {
            return max(0, (float) $loan->outstanding_principal);
        }

        $paid = (float) $loan->repayments()->sum('amount_paid');

        return max(0, (float) $loan->principal_amount - $paid);
    }
}
