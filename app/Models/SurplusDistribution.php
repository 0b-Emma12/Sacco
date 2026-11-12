<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurplusDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year',
        'total_loan_interest_income',
        'total_savings_interest_expense',
        'total_operational_expenses',
        'net_surplus',
        'statutory_reserve_pct',
        'statutory_reserve_amount',
        'dividend_on_shares_pct',
        'interest_on_savings_pct',
        'education_welfare_pct',
        'dividend_pool',
        'savings_interest_pool',
        'education_welfare_amount',
        'agm_date',
        'approved_by_board',
        'approved_by_umra_reference',
        'status',
    ];

    protected $casts = [
        'total_loan_interest_income' => 'decimal:2',
        'total_savings_interest_expense' => 'decimal:2',
        'total_operational_expenses' => 'decimal:2',
        'net_surplus' => 'decimal:2',
        'statutory_reserve_pct' => 'decimal:2',
        'statutory_reserve_amount' => 'decimal:2',
        'dividend_on_shares_pct' => 'decimal:2',
        'interest_on_savings_pct' => 'decimal:2',
        'education_welfare_pct' => 'decimal:2',
        'dividend_pool' => 'decimal:2',
        'savings_interest_pool' => 'decimal:2',
        'education_welfare_amount' => 'decimal:2',
        'agm_date' => 'date',
    ];

    public function dividends()
    {
        return $this->hasMany(MemberDividend::class, 'distribution_id');
    }
}
