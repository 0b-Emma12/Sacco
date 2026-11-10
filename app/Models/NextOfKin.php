<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    protected $table = 'next_of_kins';

    protected $fillable = [
        'member_id',
        'name',
        'relationship',
        'phone_number',
        'national_id',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
