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
        Schema::create('t_stock_fg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->cascadeOnDelete();
            $table->integer('qty')->default(0);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('part_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_stock_fg');
    }
};
