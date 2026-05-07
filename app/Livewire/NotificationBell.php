<?php

namespace App\Livewire;

use App\Enums\NotificationType;
use App\Enums\UserType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public bool $showDropdown = false;

    public bool $showModal = false;

    public array $notifications = [];

    public ?array $selectedNotification = null;

    public function mount(): void
    {
        $this->loadUnreadCount();
    }

    public function loadUnreadCount(): void
    {
        $user = Auth::user();

        if (
            ! $user ||
            ! in_array($user->user_type, [UserType::SuperAdmin, UserType::Admin])
        ) {
            $this->unreadCount = 0;

            return;
        }

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;

        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    protected function findNotification(string $id)
    {
        return Auth::user()
            ->notifications()
            ->find($id);
    }

    public function loadNotifications(): void
    {
        $user = Auth::user();

        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($note) => $this->mapNotification($note))
            ->filter()
            ->values()
            ->toArray();
    }

    protected function mapNotification($note): ?array
    {
        return match ($note->data['type'] ?? null) {
            NotificationType::OcrCompleted->value => $this->ocrCompletedNotification($note),
            NotificationType::PrizeBondWon->value => $this->prizeBondWonNotification($note),
            NotificationType::TaskCompleted->value => $this->taskCompletedNotification($note),
            default => null,
        };
    }

    protected function ocrCompletedNotification($note): array
    {
        return [
            'id' => $note->id,
            'type' => $note->data['type'] ?? null,
            'title' => $note->data['title'],
            'body' => $note->data['body'],
            'status' => $note->data['status'],
            'draw_number' => $note->data['draw_number'] ?? null,
            'read_at' => $note->read_at,
            'human_read_at' => $note->read_at?->diffForHumans(),
            'created_at' => $note->created_at->format('d M Y, h:i A'),
            'human_created_at' => $note->created_at->diffForHumans(),
            'is_read' => $note->read_at !== null,
        ];
    }

    protected function prizeBondWonNotification($note): array
    {
        return [
            'id' => $note->id,
            'type' => $note->data['type'] ?? null,
            'title' => $note->data['title'],
            'body' => $note->data['body'],
            'status' => $note->data['status'],
            'draw_number' => $note->data['draw_number'] ?? null,
            'bond_number' => $note->data['bond_number'] ?? null,
            'prize_rank' => $note->data['prize_rank'] ?? null,
            'result_id' => $note->data['result_id'] ?? null,
            'is_read' => $note->read_at !== null,
            'read_at' => $note->read_at?->diffForHumans(),
            'created_at' => $note->created_at->format('d M Y, h:i A'),
            'human_created_at' => $note->created_at->diffForHumans(),
        ];
    }

    protected function taskCompletedNotification($note): array
    {
        return [
            'id' => $note->id,
            'type' => $note->data['type'] ?? null,
            'title' => $note->data['title'],
            'body' => $note->data['body'],
            'status' => $note->data['status'] ?? null,
            'is_read' => $note->read_at !== null,
            'read_at' => $note->read_at?->diffForHumans(),
            'created_at' => $note->created_at->format('d M Y, h:i A'),
            'human_created_at' => $note->created_at->diffForHumans(),
        ];
    }

    public function openModal(string $id): void
    {
        $notification = $this->findNotification($id);

        if (! $notification) {
            return;
        }

        // Mark as read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
            $this->loadUnreadCount();
        }

        $this->selectedNotification = $this->mapNotification($notification);

        $this->showModal = true;
        $this->showDropdown = false;

        // Dropdown এর list ও update করো
        $this->loadNotifications();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedNotification = null;
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $user->unreadNotifications()
            ->update(['read_at' => now()]);

        $this->refresh();
    }

    public function toggleReadStatus(string $id): void
    {
        $notification = $this->findNotification($id);

        if (! $notification) {
            return;
        }

        $notification->read_at
            ? $notification->update(['read_at' => null])
            : $notification->markAsRead();

        $this->refresh();
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function deleteNotification(string $id): void
    {
        $notification = $this->findNotification($id);

        if (! $notification) {
            return;
        }

        $notification->delete();

        // Modal খোলা থাকলে বন্ধ করো
        if (
            $this->showModal &&
            $this->selectedNotification &&
            $this->selectedNotification['id'] === $id
        ) {
            $this->closeModal();
        }

        $this->refresh();
    }

    protected function refresh(): void
    {
        $this->loadUnreadCount();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
