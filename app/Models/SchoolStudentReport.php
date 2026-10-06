<?php

namespace App\Models;

use App\Enums\SchoolReportSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SchoolStudentReport extends Model
{
    protected $fillable = [
        'school_id',
        'total_students',
        'report_year',
        'report_month',
        'reported_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'total_students' => 'integer',
            'report_year' => 'integer',
            'report_month' => 'integer',
            'reported_at' => 'datetime',
            'source' => SchoolReportSource::class,
        ];
    }

    /**
     * The month this report stands for, e.g. "October 2026".
     */
    public function getPeriodLabelAttribute(): string
    {
        return Carbon::create($this->report_year, $this->report_month)->format('F Y');
    }

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
