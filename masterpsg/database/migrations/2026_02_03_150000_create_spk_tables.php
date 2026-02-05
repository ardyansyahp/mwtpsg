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
        Schema::create('t_spk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spk')->unique()->nullable();
            
            // Self-referencing for Split SPK logic
            $table->unsignedBigInteger('parent_spk_id')->nullable(); 
            // Constraint handled manually or add if table exists, but self-ref is safe if nullable
            
            $table->integer('cycle_number')->nullable();
            
            $table->string('manpower_pembuat')->nullable(); // Name or ID string
            
            $table->foreignId('customer_id')->constrained('m_perusahaan');
            $table->foreignId('plantgate_id')->nullable()->constrained('m_plantgate');
            
            $table->dateTime('tanggal');
            $table->time('jam_berangkat_plan')->nullable();
            $table->time('jam_datang_plan')->nullable();
            
            $table->string('cycle', 10)->nullable(); // C1, C2, etc
            $table->string('no_surat_jalan')->nullable()->index();
            $table->string('nomor_plat')->nullable();
            
            $table->foreignId('driver_id')->nullable()->constrained('m_manpower');
            
            $table->string('model_part')->nullable();
            $table->text('catatan')->nullable();
            
            $table->timestamps();
            
            // Foreign key for self-reference (must be after column definition)
            $table->foreign('parent_spk_id')->references('id')->on('t_spk')->nullOnDelete();
        });

        Schema::create('t_spk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('t_spk')->cascadeOnDelete();
            
            $table->foreignId('part_id')->constrained('sm_part'); // Assuming sm_part exists
            
            $table->integer('qty_packing_box')->default(0);
            $table->integer('jadwal_delivery_pcs')->default(0);
            $table->integer('original_jadwal_delivery_pcs')->default(0);
            $table->integer('jumlah_pulling_box')->default(0);
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_spk_detail');
        Schema::dropIfExists('t_spk');
    }
};
