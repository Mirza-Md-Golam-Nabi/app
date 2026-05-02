<div>
    <button
        style="position:relative; display:inline-flex; align-items:center; justify-content:center; width:35px; height:35px; border-radius:50%; background:#FAEEDA; border:0.5px solid #FAC775; cursor:pointer;"
    >
        <x-filament::icon
            icon="heroicon-o-bell"
            style="width:20px; height:20px; color:#BA7517;"
        />

        {{-- Unread Badge --}}
        <span style="position:absolute; top:-4px; right:-4px; width:18px; height:18px; background:#E24B4A; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:500; color:#fff; border:2px solid white;">
            3
        </span>
    </button>
</div>