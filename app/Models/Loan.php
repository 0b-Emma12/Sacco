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
        'processing_fee',
        'insurance_fee',
        'interest_rate',
        'term_months',
        'status',
        'rejection_reason',
        'default_stage',
        'amount_paid',
        'outstanding_balance',
        'product_id',
        'interest_method',
        'total_repayable',
        'monthly_installment',
        'outstanding_principal',
        'disbursed_at',
        'maturity_date',
        'applied_by',
        'approved_by',
        'disbursed_by',
        'lien_amount_placed',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'term_months' => 'integer',
        'amount_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'total_repayable' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'lien_amount_placed' => 'decimal:2',
        'disbursed_at' => 'datetime',
        'maturity_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'product_id');
    }

    public function schedules()
    {
        return $this->hasMany(RepaymentSchedule::class)->orderBy('installment_number');
    }

    public function guarantorPledges()
    {
        return $this->hasMany(GuarantorPledge::class);
    }

    public function appraisal()
    {
        return $this->hasOne(LoanAppraisal::class);
    }

    public function collaterals()
    {
        return $this->hasMany(Collateral::class);
    }

    public function restructurings()
    {
        return $this->hasMany(LoanRestructuring::class)->orderByDesc('created_at');
    }

    public function writeOffs()
    {
        return $this->hasMany(LoanWriteOff::class)->orderByDesc('created_at');
    }

    public function latestWriteOff()
    {
        return $this->hasOne(LoanWriteOff::class)->latestOfMany();
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getTotalRepayableAttribute()
    {
        $stored = $this->attributes['total_repayable'] ?? null;
        if ($stored !== null) {
            return (float) $stored;
        }

        $p = (float) $this->principal_amount;
        $r = (float) $this->interest_rate / 100;
        $n = (int) $this->term_months;
        if ($r > 0 && $n > 0) {
            $growth = pow(1 + $r, $n);
            return round($p * $r * $growth / ($growth - 1) * $n, 2);
        }
        return $p;
    }

    public function getEffectiveOutstandingAttribute()
    {
        if ($this->relationLoaded('schedules') ? $this->schedules->isNotEmpty() : $this->schedules()->exists()) {
            return \App\Services\RepaymentScheduleService::outstanding($this);
        }

        $paid = (float) $this->repayments()->sum('amount_paid');
        return max(0, $this->total_repayable - $paid);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['disbursed', 'partially_paid', 'defaulted'], true);
    }
}
