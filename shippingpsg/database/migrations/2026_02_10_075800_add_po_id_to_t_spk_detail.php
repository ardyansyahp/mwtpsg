<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_spk_detail', function (Blueprint $table) {
            $table->foreignId('po_customer_id')->nullable()->constrained('t_purchase_order_customer')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('t_spk_detail', function (Blueprint $table) {
            $table->dropConstrainedForeignId('po_customer_id');
        });
    }
};
