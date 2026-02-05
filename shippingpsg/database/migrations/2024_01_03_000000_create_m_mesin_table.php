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
        Schema::create('m_mesin', function (Blueprint $table) {
            $table->id();
            $table->string('mesin_id')->nullable(); // Logical ID
            $table->string('no_mesin');
            $table->string('merk_mesin');
            $table->integer('tonase')->default(0);
            $table->string('qrcode')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_mesin');
    }
};
