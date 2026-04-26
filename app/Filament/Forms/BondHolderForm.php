<?php

namespace App\Filament\Forms;

use App\Models\BondHolder;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class BondHolderForm
{
    public static function inputFields(): array
    {
        return [
            TextInput::make('name')
                ->label('হোল্ডারের নাম')
                ->helperText('যেমন: আব্বু, আম্মু, ভাই')
                ->required()
                ->columnSpanFull()
                ->maxLength(255),
        ];
    }

    public static function selectFields(?Closure $callback = null): array
    {
        $user_id = auth()->id();

        $field = Select::make('bond_holder_id')
            ->label('Bond Holder Name')
            ->options(function () use ($user_id) {
                return BondHolder::where('user_id', $user_id)
                    ->orderBy('name', 'asc')
                    ->pluck('name', 'id');
            })
            ->required()
            ->createOptionForm(function () {
                return self::inputFields();
            })
            ->createOptionAction(function ($action) {
                $action->modalWidth('md')
                    ->modalHeading('Bond Holder')
                    ->modalIcon('heroicon-o-user-plus')
                    ->modalIconColor('primary');
            })
            ->createOptionUsing(function (array $data) use ($user_id) {
                $data['user_id'] = $user_id;
                $bond_holder = BondHolder::create($data);

                return $bond_holder->getKey();
            });

        if ($callback instanceof Closure) {
            $callback($field);
        }

        return [$field];
    }
}
