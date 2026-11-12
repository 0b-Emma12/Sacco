<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanAppraisal extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'appraised_by',
        'monthly_income',
        'monthly_expenses',
        'existing_monthly_obligations',
        'debt_service_ratio',
        'collateral_value',
        'guarantor_coverage',
        'risk_rating',
        'recommendation',
        'recommended_amount',
        'recommended_tenure_months',
        'conditions',
        'remarks',
        'appraised_at',
    ];

    protected $casts = [
        'monthly_income' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'existing_monthly_obligations' => 'decimal:2',
        'debt_service_ratio' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'guarantor_coverage' => 'decimal:2',
        'recommended_amount' => 'decimal:2',
        'recommended_tenure_months' => 'integer',
        'appraised_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function allowsApproval(): bool
    {
        return in_array($this->recommendation, ['APPROVE', 'APPROVE_WITH_CONDITIONS'], true);
    }
}
