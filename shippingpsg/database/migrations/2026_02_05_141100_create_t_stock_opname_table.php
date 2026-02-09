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
        Schema::create('t_stock_opname', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id');
            $table->integer('qty_system');
            $table->integer('qty_actual');
            $table->integer('diff');
            $table->unsignedBigInteger('manpower_id')->nullable(); // Who performed opname
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('sm_part')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_stock_opname');
    }
};
