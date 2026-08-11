<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Authenticatable
{
    use HasFactory;

    // Perhatikan: devisi_id diubah menjadi divisi_id
    protected $fillable = ['nip', 'divisi_id', 'nama_karyawan', 'email', 'password', 'role', 'alamat', 'no_telp'];
    protected $hidden = ['password', 'remember_token'];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }
}