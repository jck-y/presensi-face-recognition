<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('absensis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('karyawan_id')->constrained('karyawans');
        $table->date('tanggal');
        $table->enum('jenis_absensi', ['masuk', 'pulang']);
        $table->dateTime('waktu');
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->string('foto_path');
        $table->enum('status_absensi', ['TW', 'TR', 'TA', 'PA', 'LR'])->default('TW');
        $table->timestamps();
    }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
