<?php

namespace App\Filament\User\Resources\UserBondNumbers\Schemas;

use App\Filament\Forms\BondHolderForm;
use App\Filament\Forms\PrizeBondSetForm;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserBondNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...BondHolderForm::selectFields(function (Select $field) {
                    $field->live();
                }),

                ...PrizeBondSetForm::selectFields(function (Select $field) {
                    $field->required(false);
                }),

                Repeater::make('bond_numbers')
                    ->label('বন্ড নাম্বার')
                    ->schema([
                        TextInput::make('bond_number')
                            ->required()
                            ->validationAttribute('Bond Number')
                            ->rule('regex:/^0[0-9]{6}$/')
                            ->placeholder('e.g. 0123456')
                            ->helperText('Must start with 0 and be exactly 7 digits')
                            ->distinct(),
                    ])
                    ->addActionLabel('Add more Field')
                    ->minItems(1)
                    ->columnSpanFull()
                    ->grid([
                        'default' => 2,
                        'lg' => 4,
                    ])
                    ->reorderable(false)
                    ->deleteAction(
                        fn ($action) => $action->extraAttributes(
                            fn ($state) => count($state ?? []) <= 1
                                ? ['style' => 'visibility: hidden; pointer-events: none;']
                                : []
                        )
                    ),
            ]);
    }
}
