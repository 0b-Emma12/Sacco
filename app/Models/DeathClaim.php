<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeathClaim extends Model
{
    use HasFactory;

    public const DOCUMENTS = [
        'death_certificate' => 'Death certificate',
        'claimant_nin' => "Claimant's National ID",
        'letters_of_administration' => 'Letters of administration (where applicable)',
        'beneficiary_nomination' => 'Beneficiary nomination form',
    ];

    protected $fillable = [
        'member_id',
        'date_of_death',
        'death_certificate_number',
        'claimant_name',
        'claimant_nin',
        'claimant_relationship',
        'claimant_phone',
        'documents',
        'share_balance',
        'savings_balance',
        'loan_outstanding',
        'guarantee_liability',
        'net_payable',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
        'payment_method',
        'payment_reference',
        'payment_date',
    ];

    protected $casts = [
        'date_of_death' => 'date',
        'documents' => 'array',
        'share_balance' => 'decimal:2',
        'savings_balance' => 'decimal:2',
        'loan_outstanding' => 'decimal:2',
        'guarantee_liability' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function checkedDocuments(): array
    {
        return is_array($this->documents) ? $this->documents : [];
    }
}
