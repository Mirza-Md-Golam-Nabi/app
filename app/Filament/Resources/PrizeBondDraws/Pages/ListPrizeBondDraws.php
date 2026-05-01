<?php
namespace App\Filament\Resources\PrizeBondDraws\Pages;

use App\Filament\Resources\PrizeBondDraws\PrizeBondDrawResource;
use App\Services\PrizeBond\ProcessPrizeBondResultsService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPrizeBondDraws extends ListRecords
{
    protected static string $resource = PrizeBondDrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Bond')
                ->icon(Heroicon::Plus)
                ->size('sm'),

            Action::make('check_bond')
                ->label('Check Bond')
                ->icon(Heroicon::MagnifyingGlass)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Check Bond Results')
                ->modalDescription('সব ড্র এর বিপরীতে user দের bond number check করা হবে।')
                ->modalSubmitActionLabel('শুরু করুন')
                ->action(function () {
                    // processed সব draw এর জন্য job dispatch
                    app(ProcessPrizeBondResultsService::class)->handle();

                    Notification::make()
                        ->title('Job শুরু হয়েছে')
                        ->body('Background এ চলছে, সম্পন্ন হলে result পাওয়া যাবে।')
                        ->success()
                        ->send();
                }),
        ];
    }
}
