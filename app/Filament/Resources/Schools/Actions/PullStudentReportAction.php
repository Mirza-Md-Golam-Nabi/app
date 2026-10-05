<?php

namespace App\Filament\Resources\Schools\Actions;

use App\Models\School;
use App\Services\School\PullSchoolStudentReportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

class PullStudentReportAction
{
    /**
     * Asks one school for its student count right now — used on the schools
     * table rows and on a school's own page.
     */
    public static function make(): Action
    {
        return Action::make('pullStudentReport')
            ->label('Fetch Now')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('info')
            ->visible(fn (School $record): bool => filled($record->base_url))
            ->action(function (School $record): void {
                try {
                    $report = app(PullSchoolStudentReportService::class)->handle($record);
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->title("{$record->name} থেকে সংখ্যা আনা যায়নি")
                        ->body(PullSchoolStudentReportService::describeFailure($exception))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title("{$record->name}: {$report->total_students} জন student")
                    ->success()
                    ->send();
            });
    }
}
