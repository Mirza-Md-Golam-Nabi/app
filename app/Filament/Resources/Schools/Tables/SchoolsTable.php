<?php

namespace App\Filament\Resources\Schools\Tables;

use App\Filament\Resources\Schools\Actions\PullStudentReportAction;
use App\Models\School;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('latestReport'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('school_code')
                    ->label('School ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->toggleable(),

                TextColumn::make('latestReport.total_students')
                    ->label('Students')
                    ->numeric()
                    ->placeholder('—')
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('latestReport.reported_at')
                    ->label('Last Report')
                    ->dateTime('d M Y, h:i A')
                    ->description(fn (School $record): ?string => $record->latestReport?->reported_at->diffForHumans())
                    ->placeholder('কখনো আসেনি')
                    ->color(fn (School $record): ?string => $record->isStale() ? 'danger' : null),

                TextColumn::make('report_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (School $record): string => self::reportStatus($record))
                    ->color(fn (string $state): string => match ($state) {
                        'Up to date' => 'success',
                        'Inactive' => 'gray',
                        default => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                PullStudentReportAction::make()
                    ->iconButton()
                    ->tooltip('এখনই স্কুল থেকে student সংখ্যা আনুন'),

                EditAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * "Overdue" means no report within the expected monthly window — the
     * school's server or scheduler probably needs a look.
     */
    private static function reportStatus(School $record): string
    {
        return match (true) {
            ! $record->is_active => 'Inactive',
            $record->latestReport === null => 'No report yet',
            $record->isStale() => 'Overdue',
            default => 'Up to date',
        };
    }
}
