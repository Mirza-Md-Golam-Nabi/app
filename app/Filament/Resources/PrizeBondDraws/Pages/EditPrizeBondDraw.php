<?php

namespace App\Filament\Resources\PrizeBondDraws\Pages;

use App\Filament\Resources\PrizeBondDraws\PrizeBondDrawResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrizeBondDraw extends EditRecord
{
    protected static string $resource = PrizeBondDrawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
