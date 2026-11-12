<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashReplenishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'reference',
        'notes',
        'replenished_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
