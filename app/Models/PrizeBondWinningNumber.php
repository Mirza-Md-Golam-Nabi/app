<?php

namespace App\Models;

use App\Models\PrizeBondDraw;
use Illuminate\Database\Eloquent\Model;

class PrizeBondWinningNumber extends Model
{
    protected $fillable = [
        'draw_id',
        'prize_rank',
        'winning_number',
    ];

    public function draw()
    {
        return $this->belongsTo(PrizeBondDraw::class, 'draw_id');
    }
}
