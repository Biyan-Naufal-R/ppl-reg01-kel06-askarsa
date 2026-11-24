<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->text('alasan_penolakan')->nullable()->after('catatan_penyelesaian');
            $table->timestamp('direspon_pada')->nullable()->after('alasan_penolakan');
        });

        // Data lama yang dulu disimpan sebagai "dibatalkan" + teks "Ditolak ..."
        // dipindahkan ke status dan kolom yang eksplisit.
        DB::table('pesanan')
            ->where('status', 'dibatalkan')
            ->where('catatan_penyelesaian', 'like', 'Ditolak%')
            ->update([
                'status' => 'ditolak',
                'alasan_penolakan' => DB::raw('catatan_penyelesaian'),
                'catatan_penyelesaian' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('pesanan')
            ->where('status', 'ditolak')
            ->update([
                'status' => 'dibatalkan',
                'catatan_penyelesaian' => DB::raw("COALESCE(alasan_penolakan, 'Ditolak oleh penyedia.')"),
            ]);

        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['alasan_penolakan', 'direspon_pada']);
        });
    }
};
