<?php

namespace App\Services\School;

use App\Enums\SchoolReportSource;
use App\Models\School;
use App\Models\SchoolStudentReport;
use Carbon\CarbonInterface;

class StoreSchoolStudentReportService
{
    /**
     * স্কুলের student সংখ্যার একটা রিপোর্ট ইতিহাসে সেভ করে। স্কুল একই রিপোর্ট আবার
     * পাঠালে (সংযোগ না পেলে সে retry করে) নতুন রেকর্ড হয় না — আগেরটাই ফেরত আসে।
     */
    public function handle(School $school, int $totalStudents, CarbonInterface $reportedAt, SchoolReportSource $source): SchoolStudentReport
    {
        return SchoolStudentReport::firstOrCreate(
            [
                'school_id' => $school->id,
                'reported_at' => $reportedAt->copy()->setTimezone(config('app.timezone'))->startOfSecond(),
            ],
            [
                'total_students' => $totalStudents,
                'source' => $source,
            ],
        );
    }
}
