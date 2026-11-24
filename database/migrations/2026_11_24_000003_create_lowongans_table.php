<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->string('perusahaan');
            $table->string('slug')->unique();
            $table->string('kategori')->index();
            $table->string('jenis')->index();
            $table->string('metode')->index();
            $table->string('kecamatan')->index();
            $table->text('deskripsi');
            $table->text('persyaratan');
            $table->string('durasi');
            $table->date('batas_lamaran');
            $table->unsignedBigInteger('gaji_min');
            $table->unsignedBigInteger('gaji_max');
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongans');
    }
};
