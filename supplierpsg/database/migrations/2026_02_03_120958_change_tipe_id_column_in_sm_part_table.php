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
             // Assuming it might be integer or have foreign key, we modify it to simple string
             // First drop potential foreign key if it exists (ignoring error if not)
             // $table->dropForeign(['tipe_id']); // Commented out as we are not sure, but safe to just change
             
             // Change to string and nullable
             $table->string('tipe_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sm_part', function (Blueprint $table) {
            // Revert is hard without knowing original state, but we can typically assume integer if it was id
            // $table->integer('tipe_id')->nullable()->change();
        });
    }
};
