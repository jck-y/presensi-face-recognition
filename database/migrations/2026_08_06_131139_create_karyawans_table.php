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
    Schema::create('karyawans', function (Blueprint $table) {
        $table->id();
        $table->string('nip', 20)->unique();
        $table->foreignId('divisi_id')->constrained('divisis');
        $table->string('nama_karyawan');
        $table->string('email')->unique();
        $table->string('password');
        $table->enum('role', ['admin', 'pimpinan', 'karyawan'])->default('karyawan');
        $table->text('alamat')->nullable();
        $table->string('no_telp', 20)->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
