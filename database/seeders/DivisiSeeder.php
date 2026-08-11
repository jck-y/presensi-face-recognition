<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiSeeder extends Seeder
{
    public function run()
    {
        DB::table('divisis')->insert([
            ['kode_divisi' => 'PRD', 'nama_divisi' => 'Produksi', 'created_at' => now(), 'updated_at' => now()],
            ['kode_divisi' => 'ADM', 'nama_divisi' => 'Administrasi', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}