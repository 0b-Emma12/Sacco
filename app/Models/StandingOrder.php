<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingOrder extends Model
{
    use HasFactory;

    public const FREQUENCIES = [
        'WEEKLY' => 'Weekly',
        'FORTNIGHTLY' => 'Fortnightly',
        'MONTHLY' => 'Monthly',
        'QUARTERLY' => 'Quarterly',
    ];

    public const SOURCES = [
        'SALARY' => 'Employer salary deduction',
        'BANK_STANDING_ORDER' => 'Bank standing order',
        'MOBILE_MONEY' => 'Mobile money',
        'CASH_AT_COUNTER' => 'Cash at counter',
    ];

    public const DESTINATIONS = [
        'savings' => 'Savings account',
        'loan' => 'Loan repayment',
    ];

    protected $fillable = [
        'member_id',
        'mandate_reference',
        'destination_type',
        'account_id',
        'loan_id',
        'amount',
        'frequency',
        'start_date',
        'end_date',
        'next_run_date',
        'last_run_date',
        'runs_completed',
        'source',
        'payer_details',
        'payer_reference',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
        'member_instructions',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'runs_completed' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function runs()
    {
        return $this->hasMany(StandingOrderRun::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'SUSPENDED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function frequencyLabel(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? (string) $this->frequency;
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? (string) $this->source;
    }

    public function destinationLabel(): string
    {
        if ($this->destination_type === 'loan') {
            return 'Loan #'.($this->loan_id ?? '—');
        }

        return $this->account ? 'Account '.$this->account->account_number : 'Savings account';
    }
}
