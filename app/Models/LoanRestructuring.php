<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRestructuring extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'reason',
        'previous_tenure_months',
        'previous_installment',
        'outstanding_at_request',
        'new_tenure_months',
        'grace_period_months',
        'new_installment',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
    ];

    protected $casts = [
        'previous_installment' => 'decimal:2',
        'outstanding_at_request' => 'decimal:2',
        'new_installment' => 'decimal:2',
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
