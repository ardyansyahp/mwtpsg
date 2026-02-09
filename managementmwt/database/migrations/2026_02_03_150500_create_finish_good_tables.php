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
        Schema::create('t_finishgood_in', function (Blueprint $table) {
            $table->id();
            
            // External reference to Assy Out (not strictly constrained to avoid dependency issues if Assy specific migration is missing)
            $table->unsignedBigInteger('assy_out_id')->nullable()->index();
            
            $table->string('lot_number')->index();
            $table->string('no_planning')->nullable();
            
            // Resources
            $table->foreignId('mesin_id')->nullable()->constrained('m_mesin');
            $table->dateTime('tanggal_produksi');
            $table->string('shift', 20)->nullable();
            
            $table->foreignId('part_id')->constrained('sm_part');
            $table->integer('qty')->default(0);
            
            $table->string('customer')->nullable(); // Snapshot name or ref
            
            $table->foreignId('manpower_id')->nullable()->constrained('m_manpower');
            
            $table->dateTime('waktu_scan');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });

        Schema::create('t_finishgood_out', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('finish_good_in_id')->constrained('t_finishgood_in');
            $table->string('lot_number'); // Snapshot or ref
            
            $table->foreignId('spk_id')->nullable()->constrained('t_spk')->nullOnDelete();
            $table->foreignId('part_id')->constrained('sm_part');
            
            $table->dateTime('waktu_scan_out');
            $table->text('catatan')->nullable();
            $table->integer('cycle')->default(1);
            $table->integer('qty')->default(0);
            $table->string('no_surat_jalan')->nullable()->index();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_finishgood_out');
        Schema::dropIfExists('t_finishgood_in');
    }
};
