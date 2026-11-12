<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuarantorPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'guarantor_member_id',
        'pledged_amount',
        'status',
        'guarantee_type',
        'amount_enforced',
        'consent_given',
        'consent_sms_sent',
        'consent_confirmed_at',
        'guarantor_signature',
    ];

    protected $casts = [
        'pledged_amount' => 'decimal:2',
        'amount_enforced' => 'decimal:2',
        'consent_given' => 'boolean',
        'consent_sms_sent' => 'boolean',
        'consent_confirmed_at' => 'datetime',
    ];

    public function remainingLiability(): float
    {
        if (! in_array($this->status, ['active', 'frozen', 'partially_enforced'], true)) {
            return 0.0;
        }

        return max(0, (float) $this->pledged_amount - (float) $this->amount_enforced);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function guarantor()
    {
        return $this->belongsTo(Member::class, 'guarantor_member_id');
    }
}
