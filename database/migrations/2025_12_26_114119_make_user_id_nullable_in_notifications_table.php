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
    Schema::table('notifications', function (Blueprint $table) {
        // 1. Hapus constraint foreign key sementara (agar bisa diubah)
        $table->dropForeign(['user_id']); 
        
        // 2. Ubah kolom menjadi nullable
        $table->foreignId('user_id')->nullable()->change();
        
        // 3. Pasang kembali constraint-nya
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable(false)->change();
    });
}
};
