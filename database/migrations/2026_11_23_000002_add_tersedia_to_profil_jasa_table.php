<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_jasa', function (Blueprint $table) {
            $table->boolean('tersedia')->default(true)->after('aktif')->index();
        });
    }

    public function down(): void
    {
        Schema::table('profil_jasa', function (Blueprint $table) {
            $table->dropColumn('tersedia');
        });
    }
};
