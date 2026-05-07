<p style="font-size:14px; color:#374151; line-height:1.6; margin:0 0 16px;">
    {{ $notification['body'] }}
</p>

<div style="background:#f9fafb; border-radius:8px; padding:12px; font-size:12px; color:#6b7280;">
    <div style="display:flex; justify-content:space-between;">
        <span>Time</span>
        <span style="color:#374151; font-weight:500;">{{ $notification['created_at'] }}</span>
    </div>
</div>
