<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingOrderRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'standing_order_id',
        'due_date',
        'amount',
        'status',
        'transaction_reference',
        'journal_reference',
        'note',
        'processed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function standingOrder()
    {
        return $this->belongsTo(StandingOrder::class);
    }

    public function isPosted(): bool
    {
        return $this->status === 'POSTED';
    }
}
