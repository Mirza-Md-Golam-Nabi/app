<?php

namespace App\Models;

use App\Enums\SchoolReportSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolStudentReport extends Model
{
    protected $fillable = [
        'school_id',
        'total_students',
        'reported_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'total_students' => 'integer',
            'reported_at' => 'datetime',
            'source' => SchoolReportSource::class,
        ];
    }

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
