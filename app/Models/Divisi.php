<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    // Mengamankan nama tabel yang benar
    protected $table = 'divisis'; 
    protected $fillable = ['kode_divisi', 'nama_divisi'];
}