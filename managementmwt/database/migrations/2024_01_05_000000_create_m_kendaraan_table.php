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
        Schema::create('m_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nopol_kendaraan')->unique();
            $table->string('jenis_kendaraan'); // e.g. Truck, Pick Up, Blind Van
            $table->string('merk_kendaraan'); // e.g. Isuzu, Hino
            $table->integer('tahun_kendaraan');
            $table->boolean('status')->default(true); 
            $table->string('qrcode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kendaraan');
    }
};
