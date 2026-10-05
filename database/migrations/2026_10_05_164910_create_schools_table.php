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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('school_code', 40)->unique(); // স্কুলের .env-এর CENTRAL_SCHOOL_ID
            $table->string('base_url')->nullable(); // pull করার জন্য স্কুলের সাইটের ঠিকানা
            $table->text('secret'); // encrypted — স্কুলের .env-এর CENTRAL_SECRET
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
