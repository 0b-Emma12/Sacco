<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    protected $table = 'employment_details';

    protected $fillable = [
        'member_id',
        'employment_type',
        'employer_name',
        'job_title',
        'monthly_income',
        'business_name',
        'business_type',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
