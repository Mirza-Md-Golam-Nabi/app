<?php

namespace App\Notifications;

use App\Enums\NotificationRetention;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private string $title,
        private string $body = ''
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => NotificationType::TaskCompleted,
            'retention' => NotificationRetention::OneDay,
            'title' => $this->title,
            'body' => $this->body,
            'status' => 'success',
        ];
    }
}
