<?php

namespace App\Http\Controllers;

use App\Enums\NotificationStatus;
use App\Enums\UserType;
use App\Models\PrizeBondResult;
use App\Models\User;
use App\Notifications\OcrCompletedNotification;
use App\Notifications\PrizeBondWonNotification;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function ocrCompleted(
        mixed $draw,
        int $count,
        string $status = NotificationStatus::Success->value,
        string $errorMessage = '',
    ) {
        $admins = User::whereIn('user_type', [
            UserType::SuperAdmin,
            UserType::Admin,
        ])->get();

        Notification::send($admins, new OcrCompletedNotification(
            draw: $draw,
            count: $count,
            status: $status,
            errorMessage: $errorMessage,
        ));
    }

    public function notifyWinners()
    {
        PrizeBondResult::query()
            ->where('is_notified', false)
            ->with(['user', 'draw'])
            ->each(function (PrizeBondResult $result) {

                // যে user জিতেছে তাকে notify করো
                $result->user->notify(new PrizeBondWonNotification($result));

                // সব Admin + SuperAdmin কেও notify করো
                $admins = User::whereIn('user_type', [
                    UserType::SuperAdmin,
                    UserType::Admin,
                ])
                    ->where('id', '!=', $result->user_id) // ← duplicate বাঁচাও
                    ->get();

                Notification::send($admins, new PrizeBondWonNotification($result));

                // is_notified = true করো যাতে duplicate না হয়
                $result->update(['is_notified' => true]);
            });
    }

    public function taskCompletedNotification(string $title, string $body)
    {
        Auth::user()->notify(new TaskCompletedNotification(
            title: $title,
            body: $body,
        ));
    }
}
