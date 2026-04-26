<?php

namespace App\Filament\User\Resources\UserBondNumbers\Pages;

use App\Filament\Forms\BondHolderForm;
use App\Filament\Forms\PrizeBondSetForm;
use App\Filament\User\Resources\UserBondNumbers\UserBondNumberResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class EditUserBondNumber extends EditRecord
{
    protected static string $resource = UserBondNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...BondHolderForm::selectFields(function (Select $field) {
                    $field->live();
                }),

                ...PrizeBondSetForm::selectFields(function (Select $field) {
                    $field->required(false);
                }),

                TextInput::make('bond_number')
                    ->required()
                    ->validationAttribute('Bond Number')
                    ->rule('regex:/^0[0-9]{6}$/')
                    ->rule(
                        Rule::unique('user_bond_numbers', 'bond_number')
                            ->where(fn ($query) => $query->where('user_id', $this->record->user_id)
                            )
                            ->ignore($this->record->id)
                    )
                    ->placeholder('e.g. 0123456')
                    ->helperText('Must start with 0 and be exactly 7 digits'),
            ]);
    }
}
