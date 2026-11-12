<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transferor_member_id',
        'transferee_member_id',
        'shares_count',
        'share_value',
        'consideration',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'decision_notes',
        'transfer_date',
        'certificate_reference',
    ];

    protected $casts = [
        'shares_count' => 'decimal:4',
        'share_value' => 'decimal:2',
        'consideration' => 'decimal:2',
        'approved_at' => 'datetime',
        'transfer_date' => 'date',
    ];

    public function transferor()
    {
        return $this->belongsTo(Member::class, 'transferor_member_id');
    }

    public function transferee()
    {
        return $this->belongsTo(Member::class, 'transferee_member_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
