<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    protected $fillable = ['loan_id', 'amount_paid', 'balance_after', 'narration', 'recorded_by'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
