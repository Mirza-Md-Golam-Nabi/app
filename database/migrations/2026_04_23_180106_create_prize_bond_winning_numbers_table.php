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
        Schema::create('prize_bond_winning_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->constrained('prize_bond_draws')->onDelete('cascade');
            $table->string('prize_rank');
            $table->string('winning_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_bond_winning_numbers');
    }
};
