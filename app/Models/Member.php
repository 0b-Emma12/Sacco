<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'membership_number',
        'salutation',
        'first_name',
        'middle_name',
        'last_name',
        'national_id',
        'phone_number',
        'email',
        'date_of_birth',
        'kra_pin',
        'country',
        'county',
        'city',
        'status',
        'date_joined',
        'total_savings',
        'share_capital',
        'credit_limit',
    ];

    protected $appends = ['available_savings'];

    public function guarantorPledges()
    {
        return $this->hasMany(GuarantorPledge::class, 'guarantor_member_id');
    }

    public function getAvailableSavingsAttribute()
    {
        $frozenAmount = $this->guarantorPledges()
                             ->where('status', 'frozen')
                             ->sum('pledged_amount');

        return max(0, $this->total_savings - $frozenAmount);
    }

    public function nextOfKins()
    {
        return $this->hasMany(NextOfKin::class);
    }

    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
