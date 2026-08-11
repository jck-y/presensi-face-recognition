<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensis';
    protected $fillable = ['karyawan_id', 'tanggal', 'jenis_absensi', 'waktu', 'latitude', 'longitude', 'foto_path', 'status_absensi'];
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}