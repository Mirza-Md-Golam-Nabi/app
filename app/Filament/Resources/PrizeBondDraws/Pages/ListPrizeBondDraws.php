<?php
namespace App\Filament\Resources\PrizeBondDraws\Pages;

use App\Filament\Resources\PrizeBondDraws\PrizeBondDrawResource;
use Filament\Actions\CreateAction;
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
        ];
    }
}
