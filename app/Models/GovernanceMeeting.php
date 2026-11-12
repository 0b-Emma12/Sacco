<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernanceMeeting extends Model
{
    use HasFactory;

    public const TYPES = ['BOARD' => 'Board Meeting', 'AGM' => 'Annual General Meeting'];

    protected $fillable = [
        'type',
        'title',
        'meeting_date',
        'venue',
        'agenda',
        'attendance',
        'absent_with_apology',
        'resolutions',
        'action_items',
        'quorum_met',
        'members_present',
        'members_total',
        'dividend_on_shares',
        'interest_on_savings',
        'status',
        'prepared_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'quorum_met' => 'boolean',
        'dividend_on_shares' => 'decimal:2',
        'interest_on_savings' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
