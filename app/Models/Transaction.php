<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'account_number',
        'transaction_type',
        'amount',
        'narration',
        'payment_method',
        'external_reference',
        'performed_by_username',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_number', 'account_number');
    }

    public function scopeMatching(Builder $query, array $filters): Builder
    {
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('transaction_type', 'like', "%{$search}%")
                  ->orWhere('narration', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('performed_by_username', 'like', "%{$search}%");
            });
        }

        if (($type = (string) ($filters['type'] ?? '')) !== '') {
            $query->where('transaction_type', $type);
        }

        return $query;
    }
}
