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
        Schema::create('bb_receiving', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_receiving');
            $table->foreignId('supplier_id')->constrained('m_perusahaan');
            $table->string('no_surat_jalan')->nullable();
            $table->string('no_purchase_order')->nullable();
            $table->string('manpower')->nullable();
            $table->string('shift')->nullable();
            $table->timestamps();
            
            $table->index(['tanggal_receiving', 'supplier_id']);
        });

        Schema::create('bb_receiving_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('bb_receiving')->cascadeOnDelete();
            
            // Optional relation to schedule detail if needed for backward trace
            $table->foreignId('schedule_detail_id')->nullable()->constrained('t_schedule_detail')->nullOnDelete();
            
            // Relation to Bahan Baku via nomor_bahan_baku (string FK pattern)
            $table->string('nomor_bahan_baku');
            $table->foreign('nomor_bahan_baku')->references('nomor_bahan_baku')->on('m_bahanbaku');
            
            $table->string('lot_number')->nullable();
            $table->string('internal_lot_number')->nullable();
            $table->decimal('qty', 12, 3)->default(0);
            $table->string('uom', 50)->nullable();
            $table->string('qrcode')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bb_receiving_detail');
        Schema::dropIfExists('bb_receiving');
    }
};
