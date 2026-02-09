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
        Schema::create('t_purchase_order_customer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id');
            $table->string('po_number');
            $table->integer('qty');
            $table->string('delivery_frequency')->nullable(); // e.g. "4x/month" or integer
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('sm_part')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_purchase_order_customer');
    }
};
