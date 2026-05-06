<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Notifications\OcrCompletedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function ocrCompleted(
        mixed $draw,
        int $count,
        string $status = 'success',
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
}
