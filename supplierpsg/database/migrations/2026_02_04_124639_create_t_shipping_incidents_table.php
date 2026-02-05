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
        Schema::create('t_shipping_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_header_id')->constrained('t_shipping_delivery_header')->cascadeOnDelete();
            
            $table->text('keterangan');
            $table->string('foto')->nullable();
            
            // Location info
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Status for administrative tracking
            $table->string('status')->default('OPEN'); // OPEN, ACKNOWLEDGED, RESOLVED
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_shipping_incidents');
    }
};
