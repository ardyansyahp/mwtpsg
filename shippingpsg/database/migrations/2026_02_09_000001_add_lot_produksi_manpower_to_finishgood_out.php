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
        Schema::table('t_finishgood_out', function (Blueprint $table) {
            $table->string('lot_produksi')->nullable()->after('lot_number');
            $table->unsignedBigInteger('manpower_id')->nullable()->after('spk_id'); // Adding manpower_id too as requested (Umar)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_finishgood_out', function (Blueprint $table) {
            $table->dropColumn(['lot_produksi', 'manpower_id']);
        });
    }
};
