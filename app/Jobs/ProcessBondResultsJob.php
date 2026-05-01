<?php

namespace App\Jobs;

use App\Models\PrizeBondDraw;
use App\Services\PrizeBond\PrizeBondResultService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBondResultsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PrizeBondDraw $draw
    ) {
        // Constructor code, if needed
    }

    /**
     * Execute the job.
     */
    public function handle(PrizeBondResultService $service): void
    {
        $service->process($this->draw);
    }
}
