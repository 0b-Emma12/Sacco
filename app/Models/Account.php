<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Account extends Model {
    protected $fillable = ['member_id', 'product_id', 'account_number', 'balance', 'is_active', 'is_default', 'term_months', 'interest_rate', 'maturity_date'];
    public function member() { return $this->belongsTo(Member::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
