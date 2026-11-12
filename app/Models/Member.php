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
        'gender',
        'common_bond_type',
        'common_bond_value',
        'kyc_verified',
        'kyc_status',
        'kyc_verified_at',
        'kyc_verified_by',
        'kyc_rejection_reason',
        'kyc_next_review_at',
        'kyc_documents',
        'kyc_notes',
        'exit_requested_at',
        'notice_period_end',
        'exit_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'kyc_verified' => 'boolean',
        'kyc_verified_at' => 'datetime',
        'kyc_next_review_at' => 'date',
        'kyc_documents' => 'array',
        'exit_requested_at' => 'datetime',
        'notice_period_end' => 'date',
        'exit_date' => 'date',
        'total_savings' => 'decimal:2',
        'share_capital' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    protected $appends = ['available_savings'];

    public function guarantorPledges()
    {
        return $this->hasMany(GuarantorPledge::class, 'guarantor_member_id');
    }

    public function capital()
    {
        return $this->hasOne(MemberCapital::class);
    }

    public function getAvailableSavingsAttribute()
    {
        return (new \App\Services\MemberCapitalService)->calculate($this)['available_savings'];
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

    public function standingOrders()
    {
        return $this->hasMany(StandingOrder::class);
    }

    public function kycGraceEndsAt(): ?\Carbon\Carbon
    {
        return $this->created_at
            ? $this->created_at->copy()->addDays((int) Setting::get('kyc_grace_days', 30))
            : null;
    }

    public function kycReviewDue(): bool
    {
        return $this->kyc_next_review_at && $this->kyc_next_review_at->isPast();
    }

    public function transactionsBlockedByKyc(): bool
    {
        if ($this->kyc_status === 'VERIFIED' || $this->kyc_verified) {
            return false;
        }

        $graceEnds = $this->kycGraceEndsAt();

        return $graceEnds !== null && $graceEnds->isPast();
    }

    public function kycDocuments(): array
    {
        return is_array($this->kyc_documents) ? $this->kyc_documents : [];
    }
}
