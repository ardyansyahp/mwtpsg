<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. M_Manpower
        Schema::create('m_manpower', function (Blueprint $table) {
            $table->id();
            $table->string('mp_id')->unique(); // Linked to User
            $table->string('nik')->unique();
            $table->string('nama');
            $table->string('departemen');
            $table->string('bagian');
            $table->string('qrcode')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // Maps to m_manpower.mp_id
            $table->string('password');
            $table->boolean('is_superadmin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. User Permissions Pivot
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['user_id', 'permission_id']);
        });

        // 5. Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // --- SEED DATA (Embedded) ---
        // Create Superadmin Manpower
        DB::table('m_manpower')->insert([
            'mp_id' => 'admin',
            'nik' => '000000',
            'nama' => 'superadmin',
            'departemen' => 'IT',
            'bagian' => 'System',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Superadmin User
        DB::table('users')->insert([
            'user_id' => 'admin',
            'password' => Hash::make('2025'),
            'is_superadmin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('m_manpower');
    }
};
