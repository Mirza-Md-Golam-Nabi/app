<?php

namespace App\Livewire;

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

    public function loadNotifications(): void
    {
        $user = Auth::user();

        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'],
                'body' => $n->data['body'],
                'status' => $n->data['status'],
                'draw_number' => $n->data['draw_number'] ?? null,
                'read_at' => $n->read_at?->diffForHumans(),
                'created_at' => $n->created_at->diffForHumans(),
                'is_read' => ! is_null($n->read_at),
            ])
            ->toArray();
    }

    public function openModal(string $id): void
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return;
        }

        // Mark as read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
            $this->loadUnreadCount();
        }

        $this->selectedNotification = [
            'id' => $notification->id,
            'type' => $notification->data['type'] ?? null,
            'title' => $notification->data['title'],
            'body' => $notification->data['body'],
            'status' => $notification->data['status'],
            'draw_number' => $notification->data['draw_number'] ?? null,
            'created_at' => $notification->created_at->format('d M Y, h:i A'),
            'is_read' => true,
        ];

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
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadUnreadCount();
        $this->loadNotifications();
    }

    public function toggleReadStatus(string $id): void
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return;
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        } else {
            $notification->update(['read_at' => null]); // unread
        }

        $this->loadUnreadCount();
        $this->loadNotifications();
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function deleteNotification(string $id): void
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return;
        }

        $notification->delete();

        // Modal খোলা থাকলে বন্ধ করো
        if ($this->showModal && $this->selectedNotification['id'] === $id) {
            $this->closeModal();
        }

        $this->loadUnreadCount();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
