<?php

namespace App\Models;

use App\Enums\OcrStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PrizeBondDraw extends Model
{
    protected $fillable = [
        'draw_date',
        'draw_number',
        'result_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'draw_date' => 'date',
            'status' => OcrStatus::class,
        ];
    }

    // Relationships
    public function results()
    {
        return $this->hasMany(PrizeBondResult::class, 'draw_id');
    }

    public function winningNumbers()
    {
        return $this->hasMany(PrizeBondWinningNumber::class, 'draw_id');
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', OcrStatus::PENDING);
    }

    public function scopeProcessed(Builder $query)
    {
        return $query->where('status', OcrStatus::PROCESSED);
    }

    // Accessors
    public function getFormattedDrawNumberAttribute(): string
    {
        return "{$this->draw_number}তম ড্র";
    }
}
