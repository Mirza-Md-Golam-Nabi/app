<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'school_code',
        'base_url',
        'secret',
        'is_active',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Every school gets its own code and secret the moment it is added, so
     * they can be copied straight into that school's .env.
     */
    protected static function booted(): void
    {
        static::creating(function (School $school): void {
            if (blank($school->school_code)) {
                $school->school_code = self::generateCode();
            }

            if (blank($school->secret)) {
                $school->secret = Str::random(48);
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'SCH-'.Str::upper(Str::random(8));
        } while (self::where('school_code', $code)->exists());

        return $code;
    }

    // Relationships
    public function reports(): HasMany
    {
        return $this->hasMany(SchoolStudentReport::class);
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(SchoolStudentReport::class)->latestOfMany('reported_at');
    }

    // Scopes
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * True when the school has never reported, or not within the expected window.
     */
    public function isStale(): bool
    {
        $lastReportedAt = $this->latestReport?->reported_at;

        return $lastReportedAt === null
            || $lastReportedAt->lt(now()->subDays(config('schools.stale_after_days')));
    }

    /**
     * The three lines to paste into this school's .env.
     *
     * @return array<string, string>
     */
    public function envValues(): array
    {
        return [
            'CENTRAL_URL' => rtrim((string) config('app.url'), '/'),
            'CENTRAL_SCHOOL_ID' => $this->school_code,
            'CENTRAL_SECRET' => (string) $this->secret,
        ];
    }
}
