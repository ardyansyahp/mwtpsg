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
            $table->string('lot_produksi')->nullable()->after('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_finishgood_in', function (Blueprint $table) {
            $table->dropColumn('lot_produksi');
        });
    }
};
