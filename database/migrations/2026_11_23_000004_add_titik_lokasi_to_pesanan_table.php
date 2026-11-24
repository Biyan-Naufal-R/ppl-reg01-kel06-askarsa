<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->text('titik_ambil')->nullable()->after('alamat_lengkap');
            $table->text('titik_antar')->nullable()->after('titik_ambil');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['titik_ambil', 'titik_antar']);
        });
    }
};
