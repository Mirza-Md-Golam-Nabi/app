<?php

namespace App\Services\School;

use App\Enums\SchoolReportSource;
use App\Models\School;
use App\Models\SchoolStudentReport;
use Carbon\CarbonInterface;

class StoreSchoolStudentReportService
{
    /**
     * স্কুলের student সংখ্যা সেভ করে — প্রতিটা স্কুলের জন্য প্রতি মাসে একটাই রেকর্ড থাকে।
     * একই মাসে আবার রিপোর্ট এলে (push হোক বা Fetch Now) নতুন রেকর্ড না হয়ে সেই মাসের
     * রেকর্ডটাই সর্বশেষ সংখ্যা দিয়ে আপডেট হয়। মাস বা বছর বদলালে নতুন রেকর্ড তৈরি হয়,
     * তাই অক্টোবর ২০২৬ আর অক্টোবর ২০২৭ আলাদা থাকে।
     */
    public function handle(School $school, int $totalStudents, CarbonInterface $reportedAt, SchoolReportSource $source): SchoolStudentReport
    {
        // মাস ঠিক হয় সেন্ট্রালের নিজের timezone-এ, যাতে সব স্কুলের হিসাব এক নিয়মে হয়।
        $reportedAt = $reportedAt->copy()->setTimezone(config('app.timezone'))->startOfSecond();

        return SchoolStudentReport::updateOrCreate(
            [
                'school_id' => $school->id,
                'report_year' => $reportedAt->year,
                'report_month' => $reportedAt->month,
            ],
            [
                'total_students' => $totalStudents,
                'reported_at' => $reportedAt,
                'source' => $source,
            ],
        );
    }
}
