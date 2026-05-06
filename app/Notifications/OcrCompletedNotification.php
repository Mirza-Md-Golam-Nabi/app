<?php

namespace App\Notifications;

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
        public readonly string $status = 'success',
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
            'notification_type' => 'temporary',
            'title' => $this->status === 'success' ? 'OCR সম্পন্ন' : 'OCR ব্যর্থ',
            'body' => $this->status === 'success'
                ? "{$this->count}টি নম্বর extract হয়েছে।"
                : $this->errorMessage,
            'draw_id' => $this->draw->id,
            'draw_number' => $this->draw->draw_number,
            'status' => $this->status,
        ];
    }
}
