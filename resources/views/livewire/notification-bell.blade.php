@php
    use Illuminate\Support\Str;
    use App\Support\NotificationStatusConfig;
@endphp

<div wire:poll.5s="loadUnreadCount" style="position:relative;">

    {{-- Bell Button --}}
    <button wire:click="toggleDropdown"
        style="position:relative; display:inline-flex; align-items:center; justify-content:center; width:35px; height:35px; border-radius:50%; background:#FAEEDA; border:0.5px solid #FAC775; cursor:pointer;">
        <x-filament::icon icon="heroicon-o-bell" style="width:20px; height:20px; color:#BA7517;" />

        @if ($unreadCount > 0)
            <span
                style="position:absolute; top:-4px; right:-4px; width:18px; height:18px; background:#E24B4A; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:500; color:#fff; border:2px solid white;">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    @if ($showDropdown)
        {{-- Backdrop --}}
        <div wire:click="closeDropdown" style="position:fixed; inset:0; z-index:40;"></div>

        <div
            style="position:absolute; right:0; top:calc(100% + 8px); width:320px; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.12); border:1px solid #f0f0f0; z-index:50; overflow:hidden;">

            {{-- Header --}}
            <div
                style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #f5f5f5;">
                <span style="font-size:14px; font-weight:600; color:#1a1a1a;">Notification</span>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                        style="font-size:11px; color:#BA7517; background:none; border:none; cursor:pointer; font-weight:500;">
                        Mark all as read
                    </button>
                @endif
            </div>

            {{-- List --}}
            <div style="max-height:360px; overflow-y:auto;">
                @forelse($notifications as $notification)
                @php
                    $status = NotificationStatusConfig::get($notification['status']);
                @endphp
                    <div wire:click="openModal('{{ $notification['id'] }}')"
                        style="
                            display:flex; align-items:flex-start; gap:10px;
                            padding:12px 16px; cursor:pointer;
                            background:{{ $notification['is_read'] ? '#fff' : '#FFFBF3' }};
                            border-bottom:1px solid #f9f9f9;
                            transition: background 0.15s;
                        "
                        onmouseover="this.style.background='#fdf6e8'"
                        onmouseout="this.style.background='{{ $notification['is_read'] ? '#fff' : '#FFFBF3' }}'">
                        {{-- Icon --}}
                        <div
                            style="
                            width:32px; height:32px; border-radius:50%; flex-shrink:0;
                            display:flex; align-items:center; justify-content:center;
                            background:{{ $status['bg'] }};
                        ">
                            <x-filament::icon
                                icon="{{ $status['icon'] }}"
                                style="width:16px; height:16px; color:{{ $status['color'] }};" />
                        </div>

                        {{-- Content --}}
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                                <span
                                    style="font-size:13px; font-weight:{{ $notification['is_read'] ? '400' : '600' }}; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $notification['title'] }}
                                </span>
                                <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                    <button wire:click.stop="toggleReadStatus('{{ $notification['id'] }}')"
                                        title="{{ $notification['is_read'] ? 'Unread' : 'Read' }}"
                                        style="background:none; border:none; cursor:pointer; padding:2px; color:#9ca3af;">
                                        <x-filament::icon
                                            icon="{{ $notification['is_read'] ? 'heroicon-o-envelope' : 'heroicon-o-envelope-open' }}"
                                            style="width:13px; height:13px;" />
                                    </button>

                                    <button wire:click.stop="deleteNotification('{{ $notification['id'] }}')"
                                        title="Delete"
                                        style="background:none; border:none; cursor:pointer; padding:2px; color:#9ca3af;"
                                        onmouseover="this.style.color='#EF4444'"
                                        onmouseout="this.style.color='#9ca3af'">
                                        <x-filament::icon icon="heroicon-o-trash" style="width:13px; height:13px;" />
                                    </button>

                                    @if (!$notification['is_read'])
                                        <span
                                            style="width:7px; height:7px; border-radius:50%; background:#E24B4A;"></span>
                                    @endif
                                </div>
                            </div>
                            <p
                                style="font-size:11px; color:#6b7280; margin:2px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $notification['body'] }}
                            </p>
                            <span style="font-size:10px; color:#9ca3af;">{{ $notification['human_created_at'] }}</span>
                        </div>
                    </div>
                @empty
                    <div style="padding:32px 16px; text-align:center; color:#9ca3af; font-size:13px;">
                        No Notifications
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal && $selectedNotification)
        {{-- Backdrop --}}
        <div wire:click="closeModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:60;"></div>

        <div
            style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:420px; background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); z-index:70; overflow:hidden;">

            @php
                // 'ocr_completed' → 'ocr-completed'
                $typeSlug    = Str::kebab($selectedNotification['type'] ?? '');
                $bodyView    = 'livewire.notifications.modals.' . $typeSlug;
                $defaultBody = 'livewire.notifications.modals.default';

                // body partial — type-specific না পেলে default
                $resolvedBodyView = view()->exists($bodyView) ? $bodyView : $defaultBody;

                // footer actions — null পাঠাই, layout নিজেই default ব্যবহার করবে
                $footerActions = null;
            @endphp

            @include('livewire.notifications.modals.layout', [
                'notification'  => $selectedNotification,
                'bodyView'      => $resolvedBodyView,
                'footerActions' => $footerActions,
            ])

        </div>
    @endif

</div>
