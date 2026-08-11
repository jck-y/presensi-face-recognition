<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // 1. Memastikan extension dibuat dan dipaksa masuk ke schema 'extensions'
    DB::statement('CREATE EXTENSION IF NOT EXISTS vector SCHEMA extensions');

    Schema::create('wajah_karyawan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('karyawan_id')->unique()->constrained('karyawans')->cascadeOnDelete();
        $table->timestamps();
    });

    // 2. Menambahkan awalan 'extensions.' sebelum vector(512)
    DB::statement('ALTER TABLE wajah_karyawan ADD COLUMN embedding extensions.vector(512)');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wajah_karyawan');
    }
};
