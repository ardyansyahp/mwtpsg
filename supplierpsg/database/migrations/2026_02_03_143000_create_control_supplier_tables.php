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
        Schema::create('t_schedule_header', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 10); // Format Y-m
            $table->foreignId('supplier_id')->constrained('m_perusahaan')->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('m_bahanbaku')->cascadeOnDelete();
            $table->string('po_number')->nullable();
            
            $table->decimal('total_plan_auto', 10, 2)->default(0);
            $table->decimal('total_plan_manual', 10, 2)->default(0);
            $table->decimal('total_plan', 10, 2)->default(0);
            $table->decimal('total_act', 10, 2)->default(0);
            $table->decimal('total_blc', 10, 2)->default(0);
            $table->string('total_status')->default('OPEN');
            $table->decimal('total_ar', 10, 2)->default(0);
            $table->decimal('total_sr', 10, 2)->default(0);
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            // Indexes for faster querying
            $table->index(['periode', 'supplier_id', 'bahan_baku_id']);
            $table->index('po_number');
        });

        Schema::create('t_schedule_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_header_id')->constrained('t_schedule_header')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('po_number')->nullable(); // Override or specific PO for this day
            
            $table->decimal('pc_plan', 10, 2)->default(0);
            $table->decimal('pc_act', 10, 2)->default(0);
            $table->decimal('pc_blc', 10, 2)->default(0);
            $table->string('pc_status')->default('PENDING');
            $table->decimal('pc_ar', 10, 2)->default(0);
            $table->decimal('pc_sr', 10, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['schedule_header_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_schedule_detail');
        Schema::dropIfExists('t_schedule_header');
    }
};
