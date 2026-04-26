<?php

namespace App\Filament\User\Resources\PrizeBondSets\Pages;

use App\Filament\User\Resources\PrizeBondSets\PrizeBondSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePrizeBondSets extends ManageRecords
{
    protected static string $resource = PrizeBondSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->modalHeading('Bond Holder')
                ->modalIcon('heroicon-o-user-plus')
                ->modalWidth('md')
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
