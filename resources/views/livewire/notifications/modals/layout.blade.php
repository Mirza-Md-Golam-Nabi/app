@php
    $defaultFooterActions = [
        [
            'key' => 'unread',
            'label' => 'Unread',
            'icon' => 'heroicon-o-envelope',
            'action' => "toggleReadStatus('{$notification['id']}')",
            'style' => 'secondary',
            'show' => $notification['is_read'],
        ],
        [
            'key' => 'delete',
            'label' => 'Delete',
            'icon' => 'heroicon-o-trash',
            'action' => "deleteNotification('{$notification['id']}')",
            'style' => 'danger',
            'show' => true,
        ],
        [
            'key' => 'close',
            'label' => 'Close',
            'icon' => 'heroicon-o-x-mark',
            'action' => 'closeModal',
            'style' => 'primary',
            'show' => true,
        ],
    ];

    $resolvedActions = $footerActions ?? $defaultFooterActions;
@endphp

{{-- Modal Header --}}
<div
    style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f0f0f0;">
    <div style="display:flex; align-items:center; gap:10px;">
        <div
            style="
            width:36px; height:36px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            background:{{ $notification['status'] === 'success' ? '#ECFDF5' : '#FEF2F2' }};
        ">
            <x-filament::icon
                icon="{{ $notification['status'] === 'success' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle' }}"
                style="width:20px; height:20px; color:{{ $notification['status'] === 'success' ? '#10B981' : '#EF4444' }};" />
        </div>
        <span style="font-size:15px; font-weight:600; color:#1a1a1a;">
            {{ $notification['title'] }}
        </span>
    </div>
    <button wire:click="closeModal" style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:4px;">
        <x-filament::icon icon="heroicon-o-x-mark" style="width:18px; height:18px;" />
    </button>
</div>

{{-- Modal Body (type-specific partial) --}}
<div style="padding:20px;">
    @include($bodyView, ['notification' => $notification])
</div>

{{-- Modal Footer --}}
<div
    style="padding:12px 20px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">

    @foreach ($resolvedActions as $action)
        @if ($action['style'] === 'secondary' && ($action['show'] ?? true))
            <div style="display:flex; gap:8px;">
                <button wire:click="{{ $action['action'] }}"
                    style="padding:8px 16px; background:#fff; border:1px solid #e5e7eb; border-radius:8px; color:#6b7280; font-size:13px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    @if (!empty($action['icon']))
                        <x-filament::icon icon="{{ $action['icon'] }}" style="width:14px; height:14px;" />
                    @endif
                    {{ $action['label'] }}
                </button>
            </div>
        @endif
    @endforeach

    @foreach ($resolvedActions as $action)
        @if ($action['style'] === 'danger' && ($action['show'] ?? true))
            <div style="display:flex; gap:8px;">
                <button wire:click="{{ $action['action'] }}"
                    style="padding:8px 16px; background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; color:#EF4444; font-size:13px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    @if (!empty($action['icon']))
                        <x-filament::icon icon="{{ $action['icon'] }}" style="width:14px; height:14px;" />
                    @endif
                    {{ $action['label'] }}
                </button>
            </div>
        @endif
    @endforeach

    @foreach ($resolvedActions as $action)
        @if ($action['style'] === 'primary' && ($action['show'] ?? true))
            <div style="display:flex; gap:8px;">
                <button wire:click="{{ $action['action'] }}"
                    style="padding:8px 16px; background:#FAEEDA; border:1px solid #FAC775; border-radius:8px; color:#BA7517; font-size:13px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    @if (!empty($action['icon']))
                        <x-filament::icon icon="{{ $action['icon'] }}" style="width:14px; height:14px;" />
                    @endif
                    {{ $action['label'] }}
                </button>
            </div>
        @endif
    @endforeach
</div>
