<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->foreign('karyawan_id')->references('id')->on('karyawans')->cascadeOnDelete();
        });
    }
};