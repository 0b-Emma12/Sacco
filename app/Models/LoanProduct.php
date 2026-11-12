<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'interest_rate_monthly',
        'interest_method',
        'multiplier_factor',
        'max_tenure_months',
        'processing_fee_pct',
        'insurance_fee_pct',
        'collateral_required',
        'min_shares_required',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate_monthly' => 'decimal:2',
        'multiplier_factor' => 'decimal:2',
        'max_tenure_months' => 'integer',
        'processing_fee_pct' => 'decimal:2',
        'insurance_fee_pct' => 'decimal:2',
        'min_shares_required' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
