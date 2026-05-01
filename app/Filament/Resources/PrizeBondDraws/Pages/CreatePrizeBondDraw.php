<?php
namespace App\Filament\Resources\PrizeBondDraws\Pages;

use App\Filament\Resources\PrizeBondDraws\PrizeBondDrawResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrizeBondDraw extends CreateRecord
{
    protected static string $resource = PrizeBondDrawResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
