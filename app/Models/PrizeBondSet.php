<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeBondSet extends Model
{
    protected $fillable = [
        'user_id',
        'bond_holder_id',
        'name',
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
