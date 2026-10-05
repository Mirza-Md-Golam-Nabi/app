<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\School\PullSchoolStudentReportService;
use Illuminate\Console\Command;
use Throwable;

class PullSchoolStudentReports extends Command
{
    /**
     * @var string
     */
    protected $signature = 'schools:pull-student-reports';

    /**
     * @var string
     */
    protected $description = 'Ask every active school for its current student count and save it.';

    public function handle(PullSchoolStudentReportService $pullReport): int
    {
        $schools = School::query()->active()->whereNotNull('base_url')->orderBy('name')->get();

        if ($schools->isEmpty()) {
            $this->warn('No active school with a base URL to pull from.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($schools as $school) {
            try {
                $report = $pullReport->handle($school);

                $this->info("{$school->name}: {$report->total_students} student(s)");
            } catch (Throwable $exception) {
                $failed++;
                report($exception);

                $this->error("{$school->name}: ".PullSchoolStudentReportService::describeFailure($exception));
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
