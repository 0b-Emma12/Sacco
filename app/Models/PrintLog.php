<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintLog extends Model
{
    protected $fillable = [
        'document_id',
        'document_type',
        'subject_type',
        'subject_id',
        'member_id',
        'printed_by_id',
        'printed_by_name',
        'watermark',
        'is_reprint',
        'verification_token',
        'meta',
    ];

    protected $casts = [
        'is_reprint' => 'boolean',
        'meta' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by_id');
    }
}
