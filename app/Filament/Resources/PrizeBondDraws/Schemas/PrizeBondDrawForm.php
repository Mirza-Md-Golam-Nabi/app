<?php

namespace App\Filament\Resources\PrizeBondDraws\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrizeBondDrawForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('draw_date')
                    ->required(),

                TextInput::make('draw_number')
                    ->required()
                    ->numeric(),

                FileUpload::make('result_image')
                    ->label('রেজাল্ট ইমেজ')
                    ->image()
                    ->disk('public')
                    ->directory('prize-bond-results/'.date('Y-m'))
                    ->visibility('private')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->imagePreviewHeight('250')
                    ->downloadable()
                    ->required(),
            ]);
    }
}
