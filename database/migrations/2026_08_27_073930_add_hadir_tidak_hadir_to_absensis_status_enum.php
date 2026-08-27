<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop CHECK constraint lama
        DB::statement('ALTER TABLE absensis DROP CONSTRAINT IF EXISTS absensis_status_absensi_check');

        // Buat CHECK constraint baru dengan tambahan 'hadir' dan 'tidak_hadir'
        DB::statement(
            "ALTER TABLE absensis ADD CONSTRAINT absensis_status_absensi_check CHECK (((status_absensi)::text = ANY ((ARRAY['TW'::character varying, 'TR'::character varying, 'TA'::character varying, 'PA'::character varying, 'LR'::character varying, 'hadir'::character varying, 'tidak_hadir'::character varying])::text[])))"
        );
    }

    public function down(): void
    {
        // Drop CHECK constraint baru
        DB::statement('ALTER TABLE absensis DROP CONSTRAINT IF EXISTS absensis_status_absensi_check');

        // Kembalikan CHECK constraint lama (tanpa 'hadir' dan 'tidak_hadir')
        DB::statement(
            "ALTER TABLE absensis ADD CONSTRAINT absensis_status_absensi_check CHECK (((status_absensi)::text = ANY ((ARRAY['TW'::character varying, 'TR'::character varying, 'TA'::character varying, 'PA'::character varying, 'LR'::character varying])::text[])))"
        );
    }
};
