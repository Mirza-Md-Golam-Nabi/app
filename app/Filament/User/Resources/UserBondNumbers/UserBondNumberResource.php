<?php

namespace App\Filament\User\Resources\UserBondNumbers;

use App\Filament\User\Resources\UserBondNumbers\Pages\CreateUserBondNumber;
use App\Filament\User\Resources\UserBondNumbers\Pages\EditUserBondNumber;
use App\Filament\User\Resources\UserBondNumbers\Pages\ListUserBondNumbers;
use App\Filament\User\Resources\UserBondNumbers\Schemas\UserBondNumberForm;
use App\Filament\User\Resources\UserBondNumbers\Tables\UserBondNumbersTable;
use App\Models\UserBondNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserBondNumberResource extends Resource
{
    protected static ?string $model = UserBondNumber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'bond_number';

    public static function form(Schema $schema): Schema
    {
        return UserBondNumberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserBondNumbersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserBondNumbers::route('/'),
            'create' => CreateUserBondNumber::route('/create'),
            'edit' => EditUserBondNumber::route('/{record}/edit'),
        ];
    }
}
