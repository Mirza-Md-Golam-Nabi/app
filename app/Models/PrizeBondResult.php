<?php

namespace App\Models;

use App\Models\PrizeBondDraw;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PrizeBondResult extends Model
{
    protected $fillable = [
        'draw_id',
        'user_id',
        'bond_number',
        'prize_rank',
        'is_notified',
        'read_at',
    ];

    public function draw()
    {
        return $this->belongsTo(PrizeBondDraw::class, 'draw_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
