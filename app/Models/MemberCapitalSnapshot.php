<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberCapitalSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'snapshot_date',
        'share_balance',
        'savings_balance',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'share_balance' => 'decimal:2',
        'savings_balance' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
