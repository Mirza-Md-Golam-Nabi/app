<?php

namespace App\Models;

use App\Models\PrizeBondSet;
use App\Models\User;
use App\Models\UserBondNumber;
use Illuminate\Database\Eloquent\Model;

class BondHolder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bondNumbers()
    {
        return $this->hasMany(UserBondNumber::class, 'bond_holder_id');
    }

    public function prizeBondSets()
    {
        return $this->hasMany(PrizeBondSet::class, 'bond_holder_id');
    }
}
