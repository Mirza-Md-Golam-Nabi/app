<?php
namespace App\Filament\Resources\PrizeBondDraws\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WinningNumbersRelationManager extends RelationManager
{
    protected static string $relationship = 'winningNumbers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prize_rank')
                    ->options([
                        '1st' => '১ম পুরস্কার',
                        '2nd' => '২য় পুরস্কার',
                        '3rd' => '৩য় পুরস্কার',
                        '4th' => '৪র্থ পুরস্কার',
                        '5th' => '৫ম পুরস্কার',
                    ])
                    ->columnSpan('full')
                    ->required(),
                TextInput::make('winning_number')
                    ->required()
                    ->columnSpan('full')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('winning_number')
            ->columns([
                TextColumn::make('prize_rank')
                    ->searchable(),
                TextColumn::make('winning_number')
                    ->searchable(),
            ])
            ->paginated(['all'])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->modalWidth('md'),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('md'),
                // DissociateAction::make(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
