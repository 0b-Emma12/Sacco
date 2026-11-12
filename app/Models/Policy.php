<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'effective_date',
        'summary',
        'change_reason',
        'impact_assessment',
        'status',
        'resolution_number',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
