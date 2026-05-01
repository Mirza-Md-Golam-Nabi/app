<?php
namespace App\Filament\Resources\PrizeBondDraws;

use App\Filament\Resources\PrizeBondDraws\Pages\CreatePrizeBondDraw;
use App\Filament\Resources\PrizeBondDraws\Pages\EditPrizeBondDraw;
use App\Filament\Resources\PrizeBondDraws\Pages\ListPrizeBondDraws;
use App\Filament\Resources\PrizeBondDraws\RelationManagers\WinningNumbersRelationManager;
use App\Filament\Resources\PrizeBondDraws\Schemas\PrizeBondDrawForm;
use App\Filament\Resources\PrizeBondDraws\Tables\PrizeBondDrawsTable;
use App\Models\PrizeBondDraw;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrizeBondDrawResource extends Resource
{
    protected static ?string $model = PrizeBondDraw::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'draw_number';

    public static function form(Schema $schema): Schema
    {
        return PrizeBondDrawForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrizeBondDrawsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WinningNumbersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPrizeBondDraws::route('/'),
            'create' => CreatePrizeBondDraw::route('/create'),
            'edit'   => EditPrizeBondDraw::route('/{record}/edit'),
        ];
    }
}
