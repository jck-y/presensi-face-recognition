<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WajahKaryawan extends Model
{
    // Kita beritahu Laravel nama tabel aslinya
    protected $table = 'wajah_karyawan';
    
    // Kolom yang boleh diisi
    protected $fillable = ['karyawan_id'];
}