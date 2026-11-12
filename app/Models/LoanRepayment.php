<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    protected $fillable = [
        'loan_id',
        'installment_number',
        'amount_paid',
        'principal_paid',
        'interest_paid',
        'balance_after',
        'narration',
        'recorded_by',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
