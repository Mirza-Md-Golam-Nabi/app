<?php

namespace App\Services\School;

use App\Enums\SchoolReportSource;
use App\Models\School;
use App\Models\SchoolStudentReport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class PullSchoolStudentReportService
{
    public function __construct(
        private SchoolRequestSignature $signature,
        private StoreSchoolStudentReportService $storeReport,
    ) {}

    /**
     * স্কুলের সার্ভারকে এখনই জিজ্ঞেস করে তার student সংখ্যা এনে ইতিহাসে সেভ করে।
     * Request-টা স্কুলের secret দিয়ে সই করা থাকে, নইলে স্কুল উত্তর দেয় না।
     *
     * @throws RuntimeException when the school has no base URL or answers with something unusable
     * @throws ConnectionException|RequestException when the school cannot be reached or refuses the request
     */
    public function handle(School $school): SchoolStudentReport
    {
        if (blank($school->base_url)) {
            throw new RuntimeException('এই স্কুলের Base URL দেওয়া নেই।');
        }

        $timestamp = (string) now()->timestamp;

        $response = Http::withHeaders([
            'X-Timestamp' => $timestamp,
            'X-Signature' => $this->signature->sign($school->secret, $timestamp, $school->school_code),
        ])
            ->acceptJson()
            ->connectTimeout(config('schools.pull_connect_timeout_seconds'))
            ->timeout(config('schools.pull_timeout_seconds'))
            ->get(rtrim($school->base_url, '/').config('schools.pull_path'))
            ->throw();

        $report = (array) $response->json();

        $isUsable = Validator::make($report, [
            'school_id' => ['required', 'string'],
            'total_students' => ['required', 'integer', 'min:0'],
            'reported_at' => ['required', 'date'],
        ])->passes();

        if (! $isUsable || $report['school_id'] !== $school->school_code) {
            throw new RuntimeException('স্কুলের উত্তরটা প্রত্যাশিত রিপোর্টের মতো নয়।');
        }

        return $this->storeReport->handle(
            $school,
            (int) $report['total_students'],
            Carbon::parse($report['reported_at']),
            SchoolReportSource::Pull,
        );
    }

    /**
     * A message fit to show an admin for whatever went wrong while pulling.
     */
    public static function describeFailure(\Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ConnectionException => 'স্কুলের সার্ভারে সংযোগ করা যায়নি (বন্ধ বা ধীর)।',
            $exception instanceof RequestException && $exception->response->status() === 401 => 'স্কুল request গ্রহণ করেনি (401) — স্কুলের .env-এর CENTRAL_SCHOOL_ID ও CENTRAL_SECRET মিলিয়ে দেখুন।',
            $exception instanceof RequestException => "স্কুলের সার্ভার {$exception->response->status()} উত্তর দিয়েছে।",
            default => $exception->getMessage(),
        };
    }
}
