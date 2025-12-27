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
    Schema::table('zona_users', function (Blueprint $table) {
        // Menyimpan status apakah 2FA aktif atau tidak
        $table->boolean('two_factor_enabled')->default(false)->after('password');
        
        // Menyimpan tipe 2FA (misal: 'email' atau 'authenticator')
        $table->string('two_factor_type')->nullable()->after('two_factor_enabled');
        
        // (Opsional) Jika nanti ingin pakai Google Authenticator
        $table->text('two_factor_secret')->nullable()->after('two_factor_type');
    });
}

public function down(): void
{
    Schema::table('zona_users', function (Blueprint $table) {
        $table->dropColumn(['two_factor_enabled', 'two_factor_type', 'two_factor_secret']);
    });
}
};
