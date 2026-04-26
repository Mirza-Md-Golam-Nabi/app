<?php

namespace App\Filament\User\Resources\UserBondNumbers\Pages;

use App\Filament\User\Resources\UserBondNumbers\UserBondNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserBondNumbers extends ListRecords
{
    protected static string $resource = UserBondNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
