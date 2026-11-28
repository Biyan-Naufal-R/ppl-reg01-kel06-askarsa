<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelapor_id')->constrained('users')->cascadeOnDelete();
            $table->string('objek_type');
            $table->unsignedBigInteger('objek_id');
            $table->string('kategori');
            $table->text('penjelasan');
            $table->string('bukti_path')->nullable();
            $table->string('bukti_nama')->nullable();
            $table->string('status')->default('menunggu')->index();
            $table->timestamps();

            $table->index(['objek_type', 'objek_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
