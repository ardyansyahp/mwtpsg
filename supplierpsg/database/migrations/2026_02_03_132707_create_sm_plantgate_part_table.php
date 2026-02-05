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
        Schema::create('sm_plantgate_part', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plantgate_id');
            $table->unsignedBigInteger('part_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('plantgate_id')->references('id')->on('m_plantgate')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('sm_part')->onDelete('cascade');

            // Indexes
            $table->index('plantgate_id');
            $table->index('part_id');
            $table->index('status');
            
            // Unique constraint
            $table->unique(['plantgate_id', 'part_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_plantgate_part');
    }
};
