<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'description',
        'posted_by_name',
        'posted_at',
        'is_reversed',
    ];

    protected $casts = [
        'is_reversed' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function lineItems()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }
}
