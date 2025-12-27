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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel zona_users
            $table->foreignId('user_id')
                  ->constrained('zona_users')
                  ->onDelete('cascade');

            $table->string('device_name')->nullable(); // Contoh: iPhone 13 / Samsung
            $table->string('ip_address')->nullable();  // Alamat IP User
            $table->string('platform')->nullable();    // Contoh: Android / iOS
            $table->timestamp('login_at')->useCurrent(); // Mencatat waktu login otomatis
            
            // Sesuai model Anda: Hanya butuh created_at (UPDATED_AT = null)
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
