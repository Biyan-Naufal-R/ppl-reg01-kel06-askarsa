<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('domisili', 'Baturaden')
            ->update(['domisili' => 'Baturraden']);
    }

    public function down(): void
    {
        // Keep canonical values when rolling back; new records may also use Baturraden.
    }
};
