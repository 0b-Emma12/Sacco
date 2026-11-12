<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'date',
        'paid_to',
        'amount',
        'purpose',
        'receipt_attached',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'receipt_attached' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
