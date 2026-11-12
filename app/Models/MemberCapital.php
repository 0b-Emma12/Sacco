<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberCapital extends Model
{
    use HasFactory;

    protected $table = 'member_capital';

    protected $fillable = [
        'member_id',
        'share_count',
        'share_balance',
        'savings_balance',
        'mandatory_savings',
        'voluntary_savings',
        'lien_amount',
        'guarantee_liability',
        'available_savings',
        'total_equity',
    ];

    protected $casts = [
        'share_count' => 'decimal:4',
        'share_balance' => 'decimal:2',
        'savings_balance' => 'decimal:2',
        'mandatory_savings' => 'decimal:2',
        'voluntary_savings' => 'decimal:2',
        'lien_amount' => 'decimal:2',
        'guarantee_liability' => 'decimal:2',
        'available_savings' => 'decimal:2',
        'total_equity' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
