<?php

namespace App\Filament\Resources\Schools\Pages;

use App\Filament\Resources\Schools\SchoolResource;
use App\Filament\Widgets\SchoolStatsOverview;
use App\Models\School;
use App\Services\School\PullSchoolStudentReportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Throwable;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SchoolStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New School')
                ->icon(Heroicon::Plus)
                ->size('sm'),

            Action::make('pullAllStudentReports')
                ->label('Fetch All')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('info')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('সব স্কুল থেকে student সংখ্যা আনবেন?')
                ->modalDescription('প্রতিটা active স্কুলের সার্ভারকে এখনই জিজ্ঞেস করা হবে। স্কুল বেশি হলে একটু সময় লাগতে পারে।')
                ->modalSubmitActionLabel('শুরু করুন')
                ->action(function (PullSchoolStudentReportService $pullReport): void {
                    $schools = School::query()->active()->whereNotNull('base_url')->orderBy('name')->get();
                    $failures = [];

                    foreach ($schools as $school) {
                        try {
                            $pullReport->handle($school);
                        } catch (Throwable $exception) {
                            report($exception);

                            $failures[] = e($school->name).': '.e(PullSchoolStudentReportService::describeFailure($exception));
                        }
                    }

                    $succeeded = $schools->count() - count($failures);

                    $notification = Notification::make()
                        ->title("{$succeeded}টা স্কুল থেকে সংখ্যা এসেছে".($failures ? ', '.count($failures).'টা থেকে আসেনি' : ''))
                        ->body(implode('<br>', $failures));

                    ($failures ? $notification->warning()->persistent() : $notification->success())->send();
                }),
        ];
    }
}
