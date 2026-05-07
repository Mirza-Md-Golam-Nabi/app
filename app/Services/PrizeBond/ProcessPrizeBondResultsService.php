<?php

namespace App\Services\PrizeBond;

use App\Http\Controllers\NotificationController;
use App\Jobs\ProcessBondResultsJob;
use App\Models\PrizeBondDraw;

class ProcessPrizeBondResultsService
{
    public function handle(): void
    {
        PrizeBondDraw::processed()
            ->each(fn ($draw) => ProcessBondResultsJob::dispatch($draw));

        app(NotificationController::class)->taskCompletedNotification(
            title: '✅ প্রাইজ বন্ড রেজাল্ট সম্পন্ন',
            body: '🎯 সকল বন্ড নম্বর চেক করা হয়েছে।',
        );
    }
}
