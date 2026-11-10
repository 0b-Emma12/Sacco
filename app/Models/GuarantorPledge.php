<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuarantorPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'guarantor_member_id',
        'pledged_amount',
        'status',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function guarantor()
    {
        return $this->belongsTo(Member::class, 'guarantor_member_id');
    }
}
