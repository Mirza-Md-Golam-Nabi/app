<?php

use App\Models\SchoolStudentReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * প্রতিটা স্কুলের জন্য প্রতি মাসে একটাই রিপোর্ট থাকবে — তাই রিপোর্টটা কোন বছরের কোন
     * মাসের সেটা আলাদা কলামে রাখা হচ্ছে, আর unique এখন (স্কুল, বছর, মাস)-এর ওপর।
     */
    public function up(): void
    {
        Schema::table('school_student_reports', function (Blueprint $table) {
            $table->unsignedSmallInteger('report_year')->nullable()->after('total_students');
            $table->unsignedTinyInteger('report_month')->nullable()->after('report_year'); // 1–12
        });

        // আগে থেকে থাকা রো: reported_at থেকে বছর-মাস বসাও; একই মাসে একাধিক থাকলে
        // সবচেয়ে নতুনটা রেখে বাকিগুলো মুছে দাও, নইলে নতুন unique বসবে না।
        $keptMonths = [];

        SchoolStudentReport::query()
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->get()
            ->each(function (SchoolStudentReport $report) use (&$keptMonths): void {
                $monthKey = $report->school_id.'-'.$report->reported_at->format('Y-n');

                if (isset($keptMonths[$monthKey])) {
                    $report->delete();

                    return;
                }

                $keptMonths[$monthKey] = true;

                $report->forceFill([
                    'report_year' => $report->reported_at->year,
                    'report_month' => $report->reported_at->month,
                ])->saveQuietly();
            });

        Schema::table('school_student_reports', function (Blueprint $table) {
            $table->unsignedSmallInteger('report_year')->nullable(false)->change();
            $table->unsignedTinyInteger('report_month')->nullable(false)->change();

            // নতুন unique আগে, পুরনোটা পরে — school_id-র foreign key-র সবসময় একটা index লাগে
            $table->unique(['school_id', 'report_year', 'report_month']);
            $table->dropUnique(['school_id', 'reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_student_reports', function (Blueprint $table) {
            $table->unique(['school_id', 'reported_at']);
            $table->dropUnique(['school_id', 'report_year', 'report_month']);
            $table->dropColumn(['report_year', 'report_month']);
        });
    }
};
