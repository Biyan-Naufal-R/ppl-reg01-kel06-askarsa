<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lowongan_id')->constrained('lowongans')->cascadeOnDelete();
            $table->string('nama_pelamar');
            $table->string('email_pelamar');
            $table->string('cv_path');
            $table->string('cv_nama');
            $table->text('pesan')->nullable();
            $table->string('status')->default('menunggu')->index();
            $table->timestamp('direspon_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lowongan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lamarans');
    }
};
