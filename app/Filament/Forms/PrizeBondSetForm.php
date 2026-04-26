<?php

namespace App\Filament\Forms;

use App\Filament\Forms\BondHolderForm;
use App\Models\PrizeBondSet;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class PrizeBondSetForm
{
    public static function inputFields(?Closure $callback = null): array
    {
        $field = TextInput::make('name')
            ->label('Prize Bond Set Name')
            ->required()
            ->maxLength(255)
            ->columnSpan('full')
            ->placeholder('যেমনঃ সেট-১, জানুয়ারি ব্যাচ')
            ->helperText('বিক্রির সময় পুরো সেট একবারে ডিলিট করতে সহজ হবে')
            ->hintIcon('heroicon-o-information-circle')
            ->hintIconTooltip('প্রাইজ বন্ডগুলো ছোট ছোট সেটে ভাগ করুন। যখন কিছু বন্ড বিক্রি করতে চাইবেন, পুরো সেটটি একবারে ডিলিট করতে পারবেন — ডিলিটের জন্য আলাদা করে খুঁজতে হবে না।');

        if ($callback instanceof Closure) {
            $callback($field);
        }

        return [$field];
    }

    public static function selectFields(?Closure $callback = null): array
    {
        $user_id = auth()->id();

        $field = Select::make('prize_bond_set_id')
            ->label('Prize Bond Set')
            ->options(function (Get $get) use ($user_id) {
                $bond_holder_id = $get('bond_holder_id');

                return PrizeBondSet::where('user_id', $user_id)
                    ->where('bond_holder_id', $bond_holder_id)
                    ->orderBy('name', 'asc')
                    ->pluck('name', 'id');
            })
            ->required()
            ->createOptionForm(function (Get $get) {
                $bond_holder_id = $get('bond_holder_id');

                return [
                    ...BondHolderForm::selectFields(function (Select $field) use ($bond_holder_id) {
                        $field->default($bond_holder_id);
                    }),
                    ...self::inputFields(),
                ];
            })
            ->createOptionAction(function ($action) {
                $action->modalWidth('md')
                    ->modalHeading('Bond Holder')
                    ->modalIcon('heroicon-o-user-plus')
                    ->modalIconColor('primary');
            })
            ->createOptionUsing(function (array $data) use ($user_id) {
                $data['user_id'] = $user_id;
                $bond_holder = PrizeBondSet::create($data);

                return $bond_holder->getKey();
            });

        if ($callback instanceof Closure) {
            $callback($field);
        }

        return [$field];
    }
}
