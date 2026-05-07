<?php

namespace App\Notifications;

use App\Enums\NotificationRetention;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OcrCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly mixed $draw,
        public readonly int $count,
        public readonly string $status = NotificationStatus::Success->value,
        public readonly string $errorMessage = '',
    ) {}

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
            'type' => NotificationType::OcrCompleted,
            'retention' => NotificationRetention::OneDay,
            'title' => $this->status === NotificationStatus::Success->value ? 'OCR সম্পন্ন' : 'OCR ব্যর্থ',
            'body' => $this->status === NotificationStatus::Success->value
                ? "{$this->count}টি নম্বর extract হয়েছে।"
                : $this->errorMessage,
            'draw_id' => $this->draw->id,
            'draw_number' => $this->draw->draw_number,
            'status' => $this->status,
        ];
    }
}
