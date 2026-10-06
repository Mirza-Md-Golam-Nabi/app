<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    protected static ?string $title = 'Report History';

    /**
     * Read-only: the history is whatever the school reported, never edited by
     * hand. One row per month — a later report in the same month replaces it.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reported_at')
            ->defaultSort('reported_at', 'desc')
            ->columns([
                TextColumn::make('period_label')
                    ->label('Month')
                    ->weight('bold'),

                TextColumn::make('reported_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('total_students')
                    ->label('Students')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('source')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Received At')
                    ->dateTime('d M Y, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
