<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_student_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_students');
            $table->dateTime('reported_at'); // স্কুল যে সময়ে সংখ্যাটা গুনেছে
            $table->string('source', 10); // push, pull
            $table->timestamps();

            // স্কুল একই রিপোর্ট আবার পাঠালে (retry) যেন দ্বিতীয় রেকর্ড না হয়
            $table->unique(['school_id', 'reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_student_reports');
    }
};
