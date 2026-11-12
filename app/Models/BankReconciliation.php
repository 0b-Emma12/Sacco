<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_number',
        'period_from',
        'period_to',
        'statement_balance',
        'deposits_in_transit',
        'unpresented_cheques',
        'adjusted_bank_balance',
        'book_balance',
        'bank_credits_not_recorded',
        'bank_charges_not_recorded',
        'adjusted_book_balance',
        'difference',
        'reconciliation_items',
        'status',
        'prepared_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'statement_balance' => 'decimal:2',
        'deposits_in_transit' => 'decimal:2',
        'unpresented_cheques' => 'decimal:2',
        'adjusted_bank_balance' => 'decimal:2',
        'book_balance' => 'decimal:2',
        'bank_credits_not_recorded' => 'decimal:2',
        'bank_charges_not_recorded' => 'decimal:2',
        'adjusted_book_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function isReconciled(): bool
    {
        return $this->status === 'RECONCILED';
    }

    public function isBalanced(): bool
    {
        return abs((float) $this->difference) < 0.005;
    }
}
