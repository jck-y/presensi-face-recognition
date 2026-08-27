<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE karyawans DROP CONSTRAINT IF EXISTS karyawans_role_check');
        DB::statement("ALTER TABLE karyawans ADD CONSTRAINT karyawans_role_check CHECK (role IN ('admin', 'pimpinan', 'karyawan', 'super_admin'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE karyawans DROP CONSTRAINT IF EXISTS karyawans_role_check');
        DB::statement("ALTER TABLE karyawans ADD CONSTRAINT karyawans_role_check CHECK (role IN ('admin', 'pimpinan', 'karyawan'))");
    }
};
