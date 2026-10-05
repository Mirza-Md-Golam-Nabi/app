<?php

namespace App\Filament\Resources\Schools\Schemas;

use App\Models\School;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('School')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('school_code')
                            ->label('School ID')
                            ->helperText('স্কুলের .env-এর CENTRAL_SCHOOL_ID। খালি রাখলে নিজে থেকে তৈরি হবে। স্কুলে বসানোর পর বদলালে স্কুলের .env-ও বদলাতে হবে।')
                            ->maxLength(40)
                            ->regex('/^[A-Za-z0-9_-]+$/')
                            ->unique(ignoreRecord: true),

                        TextInput::make('base_url')
                            ->label('School Site URL')
                            ->helperText('স্কুলের সফটওয়্যারের ঠিকানা, যেমন https://school.example.com — "Fetch Now" দিয়ে সংখ্যা আনতে এটা লাগে।')
                            ->url()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('বন্ধ করলে এই স্কুলের পাঠানো রিপোর্ট আর গ্রহণ করা হবে না।')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('স্কুলের .env ফাইলে যা বসবে')
                    ->description('নিচের তিনটা মান এই স্কুলের সার্ভারের .env ফাইলে বসান। Secret কাউকে দেবেন না — এটা দিয়েই স্কুলের রিপোর্ট যাচাই হয়।')
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->schema(array_map(
                        fn (string $key): TextEntry => TextEntry::make($key)
                            ->label($key)
                            ->state(fn (?School $record): ?string => $record?->envValues()[$key])
                            ->fontFamily('mono')
                            ->copyable()
                            ->copyableState(fn (?School $record): string => $key.'='.($record?->envValues()[$key] ?? ''))
                            ->copyMessage("{$key} লাইনটা কপি হয়েছে"),
                        ['CENTRAL_URL', 'CENTRAL_SCHOOL_ID', 'CENTRAL_SECRET'],
                    )),
            ]);
    }
}
