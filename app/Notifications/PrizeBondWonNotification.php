<?php

namespace App\Notifications;

use App\Enums\NotificationRetention;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Enums\UserType;
use App\Models\PrizeBondResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrizeBondWonNotification extends Notification
{
    use Queueable;

    protected $draw;

    /**
     * Create a new notification instance.
     */
    public function __construct(public PrizeBondResult $result)
    {
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

    public function toDatabase(object $notifiable): array
    {
        $this->draw = $this->result->draw;

        $isAdmin = in_array($notifiable->user_type, [
            UserType::SuperAdmin,
            UserType::Admin,
        ]);

        $base = [
            'type' => NotificationType::PrizeBondWon,
            'retention' => NotificationRetention::TwoYears,
            'bond_number' => $this->result->bond_number,
            'prize_rank' => $this->result->prize_rank,
            'draw_id' => $this->result->draw_id,
            'draw_number' => $this->draw->draw_number,
            'result_id' => $this->result->id,
        ];

        $specific = $isAdmin ? $this->admin() : $this->user();

        return array_merge($base, $specific);
    }

    private function admin(): array
    {
        return [
            'title' => '🏆 নতুন প্রাইজ বন্ড বিজয়ী!',
            'body' => "ব্যবহারকারী #{$this->result->user->name} এর বন্ড নম্বর "
            ."{$this->result->bond_number}, {$this->draw->draw_date->format('Y-m-d')} তারিখের "
            ."{$this->draw->draw_number}তম ড্রতে {$this->result->prize_rank} পুরস্কার জিতেছে।",
            'user_id' => $this->result->user_id,
            'status' => NotificationStatus::Info->value,
        ];
    }

    private function user(): array
    {
        return [
            'title' => '🎉 অভিনন্দন! আপনি প্রাইজ বন্ড জিতেছেন!',
            'body' => "আপনার বন্ড নম্বর {$this->result->bond_number}, "
            ."{$this->draw->draw_date->format('Y-m-d')} তারিখের {$this->draw->draw_number}তম ড্রতে "
            ."{$this->result->prize_rank} পুরস্কার জিতেছে।",
            'status' => NotificationStatus::Success->value,
        ];
    }
}
