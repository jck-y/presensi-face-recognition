<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        DB::table('karyawans')->insert([
            'nip' => 'ADM001',
            'divisi_id' => 2, // Mengarah ke ID 2 (Administrasi) dari tabel divisis
            'nama_karyawan' => 'Administrator',
            'email' => 'admin@gloriasumberdamai.com',
            'password' => Hash::make('password123'), // Password default untuk login
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}