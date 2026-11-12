<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    use HasFactory;

    public const STATUSES = ['REGISTERED', 'LIENED', 'RELEASED'];

    protected $fillable = [
        'member_id',
        'loan_id',
        'asset_type',
        'description',
        'registration_number',
        'estimated_value',
        'forced_sale_value',
        'valuer_name',
        'valuation_date',
        'location',
        'status',
        'registered_by',
        'released_at',
        'released_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'forced_sale_value' => 'decimal:2',
        'valuation_date' => 'date',
        'released_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function isHeld(): bool
    {
        return in_array($this->status, ['REGISTERED', 'LIENED'], true);
    }

    public function securityValue(): float
    {
        return (float) ($this->forced_sale_value ?? $this->estimated_value);
    }
}
