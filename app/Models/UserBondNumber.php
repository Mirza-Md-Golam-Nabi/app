<?php

namespace App\Models;

use App\Models\BondHolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserBondNumber extends Model
{
    protected $fillable = [
        'user_id',
        'bond_holder_id',
        'bond_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bondHolder()
    {
        return $this->belongsTo(BondHolder::class);
    }
}
