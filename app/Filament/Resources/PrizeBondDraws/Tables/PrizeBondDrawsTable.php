<?php

namespace App\Filament\Resources\PrizeBondDraws\Tables;

use App\Enums\OcrStatus;
use App\Services\PrizeBond\PrizeBondOcrService;
use App\Services\PrizeBond\ProcessPrizeBondResultsService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrizeBondDrawsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('draw_number', 'desc')
            ->columns([
                TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('draw_number')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('result_image')
                    ->disk('public')
                    ->imageHeight(50),

                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),

                Action::make('run_ocr')
                    ->label('OCR')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('OCR শুরু করবেন?')
                    ->modalDescription('এই ড্র এর রেজাল্ট ইমেজ থেকে নম্বর extract করা হবে।')
                    ->modalSubmitActionLabel('হ্যাঁ, শুরু করুন')
                    ->visible(
                        fn ($record) => in_array($record->status, [
                            OcrStatus::PENDING,
                            OcrStatus::FAILED,
                        ])
                    )
                    ->action(function ($record) {
                        try {
                            $results = (new PrizeBondOcrService)->extractFromImage($record->result_image);

                            if (empty($results)) {
                                throw new Exception('কোনো নম্বর extract হয়নি।');
                            }

                            if (count($results) !== 46) {
                                throw new Exception('মোট '.count($results).'টি নম্বর পাওয়া গেছে, ৪৬টি হওয়ার কথা।');
                            }

                            // পুরনো data মুছে নতুন insert
                            $record->winningNumbers()->delete();
                            $record->winningNumbers()->createMany($results);

                            $record->update(['status' => OcrStatus::PROCESSED]);

                            // Fetch processed draws and dispatch background jobs to generate prize bond results
                            app(ProcessPrizeBondResultsService::class)->handle();

                            Notification::make()
                                ->title('সফল!')
                                ->body(count($results).'টি নম্বর extract হয়েছে।')
                                ->success()
                                ->send();

                        } catch (Exception $e) {
                            $record->update(['status' => OcrStatus::FAILED]);

                            Notification::make()
                                ->title('OCR ব্যর্থ হয়েছে')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
