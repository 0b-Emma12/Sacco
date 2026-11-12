<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleLabelAttribute(): string
    {
        return [
            'AD' => 'Administrator',
            'MA' => 'Manager',
            'OP' => 'Operation Manager',
            'FI' => 'Finance Officer',
            'LO' => 'Loan Officer',
            'AC' => 'Accountant',
        ][$this->role] ?? (string) $this->role;
    }

    public function canApproveLoans(): bool
    {
        return in_array($this->role, ['AD', 'MA'], true);
    }

    public function canDisburseLoans(): bool
    {
        return in_array($this->role, ['AD', 'MA', 'FI'], true);
    }

    public function canAppraiseLoans(): bool
    {
        return in_array($this->role, ['AD', 'MA', 'LO'], true);
    }

    public function canVerifyKyc(): bool
    {
        return in_array($this->role, ['AD', 'MA', 'OP'], true);
    }
}
