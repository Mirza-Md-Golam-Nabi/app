<?php

namespace App\Filament\Resources\Schools\Actions;

use App\Models\School;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RegenerateSecretAction
{
    /**
     * Replaces a school's secret with a fresh one — for when the old one may
     * have been seen by someone it should not have been. The old secret stops
     * working at once, so the school's .env has to be updated straight after.
     */
    public static function make(): Action
    {
        return Action::make('regenerateSecret')
            ->label('Regenerate Secret')
            ->icon(Heroicon::OutlinedKey)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('নতুন secret তৈরি করবেন?')
            ->modalDescription('পুরনো secret সাথে সাথে বাতিল হয়ে যাবে। স্কুলের সার্ভারের .env ফাইলে নতুন CENTRAL_SECRET না বসানো পর্যন্ত ওই স্কুলের রিপোর্ট পাঠানো (push) এবং এখান থেকে আনা (Fetch Now) দুটোই ব্যর্থ হবে।')
            ->modalSubmitActionLabel('হ্যাঁ, নতুন secret দিন')
            ->action(function (School $record): void {
                $record->regenerateSecret();

                Notification::make()
                    ->title('নতুন secret তৈরি হয়েছে')
                    ->body('নিচের CENTRAL_SECRET লাইনটা কপি করে স্কুলের .env-এ বসান, তারপর স্কুলের সার্ভারে php artisan optimize:clear চালান।')
                    ->warning()
                    ->persistent()
                    ->send();
            });
    }
}
