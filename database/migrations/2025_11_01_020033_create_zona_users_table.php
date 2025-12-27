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
        Schema::create('zona_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Ubah ke nullable sementara proses pendaftaran
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable(); // Tambahkan untuk registrasi HP
            $table->string('password')->nullable(); // Nullable karena user Google tidak pakai password manual
            $table->string('google_id')->nullable(); 
            $table->foreignId('role_id')->default(1)->constrained('roles');
            $table->decimal('total_transaksi', 15, 2)->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zona_users');
    }
};
