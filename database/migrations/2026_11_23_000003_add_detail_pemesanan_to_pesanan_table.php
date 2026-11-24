<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('nama_pemesan')->nullable()->after('penyedia_id');
            $table->string('email_pemesan')->nullable()->after('nama_pemesan');
            $table->string('nomor_telepon', 30)->nullable()->after('email_pemesan');
            $table->string('domisili_pemesan')->nullable()->after('nomor_telepon');
            $table->text('alamat_lengkap')->nullable()->after('domisili_pemesan');
            $table->date('tanggal_mulai')->nullable()->after('alamat_lengkap');
            $table->string('perkiraan_durasi')->nullable()->after('tanggal_mulai');
            $table->json('jenis_bantuan')->nullable()->after('perkiraan_durasi');
            $table->text('catatan_tambahan')->nullable()->after('jenis_bantuan');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn([
                'nama_pemesan',
                'email_pemesan',
                'nomor_telepon',
                'domisili_pemesan',
                'alamat_lengkap',
                'tanggal_mulai',
                'perkiraan_durasi',
                'jenis_bantuan',
                'catatan_tambahan',
            ]);
        });
    }
};
