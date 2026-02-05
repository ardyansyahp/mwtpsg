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
        // Main Table
        Schema::create('m_bahanbaku', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50); // material, subpart, box, layer, polybag, rempart, masterbatch
            $table->string('nama_bahan_baku');
            $table->string('nomor_bahan_baku', 100)->nullable()->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('m_perusahaan')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->string('qrcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Child Table: Material / Masterbatch
        Schema::create('m_bahanbaku_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('nama_bahan_baku');
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });

        // Child Table: Subpart
        Schema::create('m_bahanbaku_subpart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('nama_bahan_baku');
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });

        // Child Table: Box
        Schema::create('m_bahanbaku_box', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('jenis', 50); // polybox, impraboard
            $table->string('kode_box', 50)->nullable();
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });

        // Child Table: Layer
        Schema::create('m_bahanbaku_layer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('jenis', 50); // ldpe, foam_sheet, etc
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });

        // Child Table: Polybag
        Schema::create('m_bahanbaku_polybag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('jenis', 50); // ldpe
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });

        // Child Table: Rempart
        Schema::create('m_bahanbaku_rempart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('jenis', 50);
            $table->decimal('std_packing', 10, 2)->nullable();
            $table->string('uom', 50)->nullable();
            $table->string('jenis_packing', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_bahanbaku_rempart');
        Schema::dropIfExists('m_bahanbaku_polybag');
        Schema::dropIfExists('m_bahanbaku_layer');
        Schema::dropIfExists('m_bahanbaku_box');
        Schema::dropIfExists('m_bahanbaku_subpart');
        Schema::dropIfExists('m_bahanbaku_material');
        Schema::dropIfExists('m_bahanbaku');
    }
};
