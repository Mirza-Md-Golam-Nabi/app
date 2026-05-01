<?php
namespace App\Services\PrizeBond;

use App\Jobs\ProcessBondResultsJob;
use App\Models\PrizeBondDraw;

class ProcessPrizeBondResultsService
{
    public function handle(): void
    {
        PrizeBondDraw::processed()
            ->each(fn($draw) => ProcessBondResultsJob::dispatch($draw));
    }
}
