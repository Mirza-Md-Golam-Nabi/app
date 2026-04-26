<?php

namespace App\Filament\User\Resources\PrizeBondSets;

use App\Filament\Forms\BondHolderForm;
use App\Filament\User\Resources\PrizeBondSets\Pages\ManagePrizeBondSets;
use App\Models\PrizeBondSet;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrizeBondSetResource extends Resource
{
    protected static ?string $model = PrizeBondSet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...BondHolderForm::selectFields(function (Select $field) {
                    $field
                        ->columnSpanFull();
                }),
                TextInput::make('name')
                    ->label('Prize Bond Set Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder('যেমনঃ সেট-১, জানুয়ারি ব্যাচ')
                    ->helperText('বিক্রির সময় পুরো সেট একবারে ডিলিট করতে সহজ হবে')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('প্রাইজ বন্ডগুলো ছোট ছোট সেটে ভাগ করুন। যখন কিছু বন্ড বিক্রি করতে চাইবেন, পুরো সেটটি একবারে ডিলিট করতে পারবেন — ডিলিটের জন্য আলাদা করে খুঁজতে হবে না।'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(function (Builder $query) {
                $query->join('bond_holders', 'prize_bond_sets.bond_holder_id', '=', 'bond_holders.id')
                    ->where('prize_bond_sets.user_id', auth()->id())
                    ->orderBy('bond_holders.name', 'asc')
                    ->orderBy('prize_bond_sets.name', 'asc')
                    ->select('prize_bond_sets.*', 'bond_holders.name as holder_name');
            })
            ->columns([
                TextColumn::make('holder_name')
                    ->label('Bond Holder Name')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('bond_holders.name', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('bond_holders.name', $direction);
                    }),
                TextColumn::make('name')
                    ->label('Set Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('md'),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePrizeBondSets::route('/'),
        ];
    }
}
