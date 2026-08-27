<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status_absensi ENUM('TW', 'TR', 'TA', 'PA', 'LR', 'hadir', 'tidak_hadir') DEFAULT 'TW'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status_absensi ENUM('TW', 'TR', 'TA', 'PA', 'LR') DEFAULT 'TW'");
    }
};
