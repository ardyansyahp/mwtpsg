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
        // 1. SM_PART
        Schema::create('sm_part', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_part')->unique();
            $table->string('nama_part')->nullable();
            
            // FK to M_PERUSAHAAN (Customer)
            $table->foreignId('customer_id')->nullable()->constrained('m_perusahaan')->onDelete('set null');
            
            $table->integer('tipe_id')->nullable(); 
            $table->string('model_part')->nullable();
            $table->string('proses')->nullable(); // INJECT / ASSY
            
            // Hierarchy
            $table->foreignId('parent_part_id')->nullable()->constrained('sm_part')->onDelete('set null');
            $table->string('relation_type')->nullable(); 
            
            // Cycle Time & Specs
            $table->decimal('CT_Inject', 8, 2)->nullable();
            $table->decimal('CT_Assy', 8, 2)->nullable();
            $table->string('Warna_Label_Packing')->nullable();
            $table->integer('QTY_Packing_Box')->nullable();
            $table->integer('R_Karton_Box_id')->nullable(); // Reference to box ID?
            
            // Weights
            $table->decimal('N_Cav1', 12, 4)->nullable(); // Netto
            $table->decimal('Runner', 12, 4)->nullable();
            $table->decimal('Avg_Brutto', 12, 4)->nullable(); // Brutto

            // Status & Keterangan (Like Bahan Baku)
            $table->boolean('status')->default(true);
            $table->text('keterangan')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. SM_PART_MATERIAL
        Schema::create('sm_part_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            $table->string('material_type')->nullable(); // MAIN / ALTERNATIVE
            $table->string('tipe')->nullable(); 
            $table->decimal('std_using', 12, 4)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 3. SM_PART_SUBPART
        Schema::create('sm_part_subpart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('subpart_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            $table->decimal('std_using', 12, 4)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 4. SM_PART_BOX
        Schema::create('sm_part_box', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('box_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            // Snapshot dimensions/specs
            $table->string('tipe')->nullable();
            $table->string('jenis_box')->nullable();
            $table->string('kode_box')->nullable();
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 5. SM_PART_LAYER
        Schema::create('sm_part_layer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('layer_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            $table->decimal('qty', 12, 4)->nullable(); // Often 1 or specific usage
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 6. SM_PART_POLYBAG
        Schema::create('sm_part_polybag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('polybag_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            $table->decimal('qty', 12, 4)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 7. SM_PART_REMPART
        Schema::create('sm_part_rempart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('sm_part')->onDelete('cascade');
            $table->foreignId('rempart_id')->nullable()->constrained('m_bahanbaku')->onDelete('cascade');
            $table->decimal('qty', 12, 4)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_part_rempart');
        Schema::dropIfExists('sm_part_polybag');
        Schema::dropIfExists('sm_part_layer');
        Schema::dropIfExists('sm_part_box');
        Schema::dropIfExists('sm_part_subpart');
        Schema::dropIfExists('sm_part_material');
        Schema::dropIfExists('sm_part');
    }
};
