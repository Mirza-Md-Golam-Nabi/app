<?php

use App\Enums\OcrStatus;
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
        Schema::create('prize_bond_draws', function (Blueprint $table) {
            $table->id();
            $table->date('draw_date');
            $table->unsignedSmallInteger('draw_number'); // যেমন: 45তম
            $table->string('result_image', 50)->nullable(); // OCR ইমেজ পাথ
            $table->string('status', 15)->default(OcrStatus::PENDING);
            $table->timestamps();

            $table->index('draw_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_bond_draws');
    }
};
