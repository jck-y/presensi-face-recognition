
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE absensis DROP CONSTRAINT IF EXISTS absensis_status_absensi_check'
        );

        DB::statement(
            "ALTER TABLE absensis ADD CONSTRAINT absensis_status_absensi_check CHECK (((status_absensi)::text = ANY ((ARRAY['TW'::character varying, 'TR'::character varying, 'TA'::character varying, 'PA'::character varying, 'LR'::character varying, 'hadir'::character varying, 'tidak_hadir'::character varying, 'PC'::character varying, 'pulang'::character varying])::text[])))"
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE absensis DROP CONSTRAINT IF EXISTS absensis_status_absensi_check'
        );

        DB::statement(
            "ALTER TABLE absensis ADD CONSTRAINT absensis_status_absensi_check CHECK (((status_absensi)::text = ANY ((ARRAY['TW'::character varying, 'TR'::character varying, 'TA'::character varying, 'PA'::character varying, 'LR'::character varying, 'hadir'::character varying, 'tidak_hadir'::character varying])::text[])))"
        );
    }
};

