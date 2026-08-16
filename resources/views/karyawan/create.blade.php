@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 600px;">
    <h3>Tambah Karyawan</h3>
    <form method="POST" action="{{ route('karyawan.store') }}" data-loading-message="Menyimpan karyawan...">
        @csrf
        <div class="mb-3"><label>NIP</label>
            <input type="text" name="nip" class="form-control" value="{{ old('nip') }}"></div>
        <div class="mb-3"><label>Nama</label>
            <input type="text" name="nama_karyawan" class="form-control" value="{{ old('nama_karyawan') }}"></div>
        <div class="mb-3"><label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
        <div class="mb-3"><label>Password</label>
            <input type="password" name="password" class="form-control"></div>
        <div class="mb-3"><label>Divisi</label>
            <select name="divisi_id" class="form-select">
                @foreach($divisis as $d)
                    <option value="{{ $d->id }}">{{ $d->nama_divisi }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Role</label>
            <select name="role" class="form-select">
                <option value="karyawan">Karyawan</option>
                <option value="pimpinan">Pimpinan</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-3"><label>Alamat</label>
            <textarea name="alamat" class="form-control">{{ old('alamat') }}</textarea></div>
        <div class="mb-3"><label>No. Telp</label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}"></div>
        <button class="btn btn-primary w-100">Simpan</button>
    </form>
</div>
@endsection