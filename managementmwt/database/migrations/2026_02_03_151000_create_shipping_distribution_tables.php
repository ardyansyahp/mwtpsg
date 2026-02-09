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
        Schema::create('t_shipping_delivery_header', function (Blueprint $table) {
            $table->id();
            
            $table->string('periode', 10); // Y-m
            $table->foreignId('kendaraan_id')->constrained('m_kendaraan');
            $table->foreignId('driver_id')->nullable()->constrained('m_manpower');
            
            $table->string('destination')->default('-'); // Customer name or route
            $table->string('no_surat_jalan')->nullable()->index(); // Link to SPK/FG Out
            
            $table->date('tanggal_berangkat')->index();
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            
            $table->string('status')->default('OPEN'); // OPEN, IN_TRANSIT, DELIVERED, COMPLETED
            
            $table->integer('total_trip')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });

        Schema::create('t_shipping_delivery_detail', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('delivery_header_id')->constrained('t_shipping_delivery_header')->cascadeOnDelete();
            
            $table->date('tanggal');
            $table->integer('jam'); // 0-23
            
            $table->string('status')->default('OPEN'); 
            $table->string('lokasi_saat_ini')->nullable();
            
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->dateTime('waktu_update')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('foto_bukti')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate logs for same hour if desired, 
            // but ControlTruck allows overwrite so index is enough.
            $table->index(['delivery_header_id', 'tanggal', 'jam']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_shipping_delivery_detail');
        Schema::dropIfExists('t_shipping_delivery_header');
    }
};
