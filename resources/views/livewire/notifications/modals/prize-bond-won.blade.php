<p style="font-size:14px; color:#374151; line-height:1.6; margin:0 0 16px;">
    {{ $notification['body'] }}
</p>

<div style="background:#f9fafb; border-radius:8px; padding:12px; font-size:12px; color:#6b7280;">

    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>বন্ড নম্বর</span>
        <span style="color:#374151; font-weight:500;">{{ $notification['bond_number'] }}</span>
    </div>

    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>পুরস্কার</span>
        <span style="color:#10B981; font-weight:600;">🏆 {{ $notification['prize_rank'] }}</span>
    </div>

    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>ড্র নম্বর</span>
        <span style="color:#374151; font-weight:500;"># {{ $notification['draw_number'] }}</span>
    </div>

    <div style="display:flex; justify-content:space-between;">
        <span>সময়</span>
        <span style="color:#374151; font-weight:500;">{{ $notification['created_at'] }}</span>
    </div>

</div>