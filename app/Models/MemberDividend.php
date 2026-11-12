<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberDividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'member_id',
        'avg_share_balance',
        'avg_savings_balance',
        'dividend_earned',
        'interest_earned',
        'withholding_tax',
        'total_payout',
        'net_payout',
        'wht_rate',
        'payout_method',
        'payout_status',
        'payout_date',
        'payout_reference',
    ];

    protected $casts = [
        'avg_share_balance' => 'decimal:2',
        'avg_savings_balance' => 'decimal:2',
        'dividend_earned' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'total_payout' => 'decimal:2',
        'net_payout' => 'decimal:2',
        'wht_rate' => 'decimal:2',
        'payout_date' => 'date',
    ];

    public function distribution()
    {
        return $this->belongsTo(SurplusDistribution::class, 'distribution_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
