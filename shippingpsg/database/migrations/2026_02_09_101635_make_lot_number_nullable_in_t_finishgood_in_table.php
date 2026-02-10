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
        Schema::table('t_finishgood_in', function (Blueprint $table) {
            $table->string('lot_number')->nullable()->change();
            // Assuming lot_produksi exists based on context, change if exists
            if (Schema::hasColumn('t_finishgood_in', 'lot_produksi')) {
                 $table->string('lot_produksi')->nullable()->change();
            }
            $table->dateTime('tanggal_produksi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_finishgood_in', function (Blueprint $table) {
            // Revert is risky if data contains nulls, but for strictness:
            // $table->string('lot_number')->nullable(false)->change();
        });
    }
};
