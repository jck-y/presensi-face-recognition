@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 600px;">
    <h3>Edit Karyawan</h3>
    <form method="POST" action="{{ route('karyawan.update', $karyawan) }}">
        @csrf
        @method('PUT')
        <div class="mb-3"><label>NIP</label>
            <input type="text" name="nip" class="form-control" value="{{ old('nip', $karyawan->nip) }}"></div>
        <div class="mb-3"><label>Nama</label>
            <input type="text" name="nama_karyawan" class="form-control" value="{{ old('nama_karyawan', $karyawan->nama_karyawan) }}"></div>
        <div class="mb-3"><label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $karyawan->email) }}"></div>
        <div class="mb-3"><label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti"></div>
        <div class="mb-3"><label>Divisi</label>
            <select name="divisi_id" class="form-control">
                @foreach($divisis as $d)
                    <option value="{{ $d->id }}" {{ $karyawan->divisi_id == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Role</label>
            <select name="role" class="form-control">
                <option value="karyawan" {{ $karyawan->role == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                <option value="pimpinan" {{ $karyawan->role == 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                <option value="admin" {{ $karyawan->role == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="mb-3"><label>Alamat</label>
            <textarea name="alamat" class="form-control">{{ old('alamat', $karyawan->alamat) }}</textarea></div>
        <div class="mb-3"><label>No. Telp</label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $karyawan->no_telp) }}"></div>
        <button class="btn btn-primary w-100">Simpan Perubahan</button>
    </form>
</div>
@endsection