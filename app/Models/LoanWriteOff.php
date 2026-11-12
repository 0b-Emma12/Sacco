<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanWriteOff extends Model
{
    use HasFactory;

    public const RECOVERY_EFFORTS = [
        'demand_letters' => 'Demand letters sent',
        'guarantors_contacted' => 'Guarantors contacted',
        'collateral_enforced' => 'Collateral enforcement attempted',
        'legal_action' => 'Legal action taken',
    ];

    protected $fillable = [
        'loan_id',
        'reason',
        'recovery_efforts',
        'amount_recoverable',
        'write_off_amount',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
    ];

    protected $casts = [
        'recovery_efforts' => 'array',
        'amount_recoverable' => 'decimal:2',
        'write_off_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }
}
