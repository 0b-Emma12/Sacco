<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'status',
        'rejection_reason',
        'default_stage',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function guarantorPledges()
    {
        return $this->hasMany(GuarantorPledge::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getTotalRepayableAttribute()
    {
        $p = $this->principal_amount;
        $r = $this->interest_rate / 100;
        $n = $this->term_months;
        if ($r > 0 && $n > 0) {
            $emi = $p * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1);
            return round($emi * $n, 2);
        }
        return $p;
    }

    public function getEffectiveOutstandingAttribute()
    {
        $totalRepayable = $this->total_repayable;
        $paid = $this->repayments()->sum('amount_paid');
        return max(0, $totalRepayable - $paid);
    }
}
