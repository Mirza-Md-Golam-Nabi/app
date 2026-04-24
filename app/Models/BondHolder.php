<?php

namespace App\Models;

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
}
