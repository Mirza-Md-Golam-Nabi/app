<?php

namespace App\Filament\Resources\Schools\Widgets;

use App\Models\School;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MonthlyStudentsChart extends ChartWidget
{
    /**
     * One hue for the single series — validated for 3:1 contrast on both the
     * light and the dark chart surface, so it needs no per-theme variant.
     */
    private const BAR_COLOUR = '#2a78d6';

    protected ?string $heading = 'মাসিক Student সংখ্যা';

    protected ?string $description = 'প্রতি মাসের সর্বশেষ রিপোর্ট অনুযায়ী। ফাঁকা মাস মানে সেই মাসে কোনো রিপোর্ট আসেনি। বারের ওপর মাউস রাখলে সংখ্যা দেখায়।';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = ['default' => 'full'];

    protected static bool $isLazy = false;

    public ?Model $record = null;

    /**
     * Open on the most recent year this school has reported in.
     */
    public function mount(): void
    {
        $this->filter ??= (string) ($this->years()[0] ?? now()->year);

        parent::mount();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return collect($this->years())
            ->mapWithKeys(fn (int $year): array => [(string) $year => (string) $year])
            ->all();
    }

    /**
     * All twelve months of the chosen year, a month with no report left empty
     * rather than drawn as zero — "no report" and "no students" are different.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $year = (int) ($this->filter ?: now()->year);

        $countsByMonth = $this->school()?->reports()
            ->where('report_year', $year)
            ->pluck('total_students', 'report_month')
            ->all() ?? [];

        $months = range(1, 12);

        return [
            'datasets' => [
                [
                    'label' => 'Students',
                    'data' => array_map(fn (int $month): ?int => $countsByMonth[$month] ?? null, $months),
                    'backgroundColor' => self::BAR_COLOUR,
                    'hoverBackgroundColor' => self::BAR_COLOUR,
                    'borderWidth' => 0,
                    'borderRadius' => ['topLeft' => 4, 'topRight' => 4],
                    'borderSkipped' => 'bottom',
                    'maxBarThickness' => 32,
                ],
            ],
            'labels' => array_map(fn (int $month): string => Carbon::create($year, $month)->format('M'), $months),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                // A single series: the heading names it, so no legend box.
                'legend' => ['display' => false],
                'tooltip' => ['displayColors' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'border' => ['display' => false],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }

    /**
     * Years this school has reports for, newest first — the current year is
     * always offered so a school with no reports yet still shows its axis.
     *
     * @return array<int, int>
     */
    private function years(): array
    {
        return collect($this->school()?->reports()->distinct()->pluck('report_year')->all() ?? [])
            ->push(now()->year)
            ->map(fn (int|string $year): int => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    private function school(): ?School
    {
        return $this->record instanceof School ? $this->record : null;
    }
}
