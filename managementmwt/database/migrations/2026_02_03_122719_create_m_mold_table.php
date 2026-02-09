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
        Schema::create('m_mold', function (Blueprint $table) {
            $table->id();
            // $table->string('mold_id')->unique()->nullable(); // Usually id is enough or use custom id
            
            // Relationships
            $table->foreignId('perusahaan_id')->nullable()->constrained('m_perusahaan')->onDelete('set null');
            $table->foreignId('part_id')->nullable()->constrained('sm_part')->onDelete('set null');
            
            // Identifiers / Specs
            $table->string('kode_mold')->unique()->nullable();
            $table->string('nomor_mold')->nullable();
            $table->integer('cavity')->default(0);
            $table->decimal('cycle_time', 8, 2)->nullable();
            $table->integer('capacity')->nullable();
            
            // Additional Info
            $table->string('lokasi_mold')->nullable();
            $table->string('tipe_mold')->nullable();
            $table->string('material_resin')->nullable();
            $table->string('warna_produk')->nullable();
            
            // Status
            $table->boolean('status')->default(true); // Active/Inactive (Running/Discontinue)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_mold');
    }
};
