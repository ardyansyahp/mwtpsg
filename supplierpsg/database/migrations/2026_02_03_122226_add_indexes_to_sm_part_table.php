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
        Schema::table('sm_part', function (Blueprint $table) {
            $table->index('nama_part');
            $table->index('model_part');
            $table->index('proses');
            $table->index('status');
            $table->index('tipe_id');
            // customer_id and nomor_part usually already indexed by Foreign Key and Unique constraints respectively
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sm_part', function (Blueprint $table) {
            $table->dropIndex(['nama_part']);
            $table->dropIndex(['model_part']);
            $table->dropIndex(['proses']);
            $table->dropIndex(['status']);
            $table->dropIndex(['tipe_id']);
        });
    }
};
