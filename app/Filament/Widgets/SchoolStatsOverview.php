<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Schools\SchoolResource;
use App\Models\School;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    /**
     * Totals across every active school, each counted by its latest report.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $schools = School::query()->active()->with('latestReport')->get();
        $overdue = $schools->filter(fn (School $school): bool => $school->isStale())->count();

        return [
            Stat::make('Active Schools', number_format($schools->count())),

            Stat::make('Total Students', number_format($schools->sum(fn (School $school): int => $school->latestReport?->total_students ?? 0)))
                ->description('প্রতিটা স্কুলের সর্বশেষ রিপোর্ট অনুযায়ী')
                ->color('success'),

            Stat::make('Overdue Schools', number_format($overdue))
                ->description(config('schools.stale_after_days').' দিনের মধ্যে কোনো রিপোর্ট আসেনি — তালিকা দেখতে ক্লিক করুন')
                ->color($overdue > 0 ? 'danger' : 'gray')
                ->url(SchoolResource::getUrl('index', ['filters' => ['overdue' => ['isActive' => true]]])),
        ];
    }
}
