<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'principal_due',
        'interest_due',
        'total_due',
        'principal_paid',
        'interest_paid',
        'total_paid',
        'payment_status',
        'days_overdue',
        'penalty_charged',
    ];

    protected $casts = [
        'due_date' => 'date',
        'principal_due' => 'decimal:2',
        'interest_due' => 'decimal:2',
        'total_due' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'days_overdue' => 'integer',
        'penalty_charged' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('payment_status', ['PAID', 'WAIVED']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'OVERDUE');
    }
}
