@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Karyawan</h3>
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
</div>
<table class="table table-bordered bg-white">
    <thead>
        <tr><th>NIP</th><th>Nama</th><th>Divisi</th><th>Role</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    @foreach($karyawans as $k)
        <tr>
            <td>{{ $k->nip }}</td>
            <td>{{ $k->nama_karyawan }}</td>
            <td>{{ $k->divisi->nama_divisi }}</td>
            <td>{{ $k->role }}</td>
            <td>
                <a href="{{ route('karyawan.edit', $k) }}" class="btn btn-sm btn-warning">Edit</a>
                <a href="{{ route('karyawan.enroll-wajah', $k) }}" class="btn btn-sm btn-info">Kelola Wajah</a>
                <form action="{{ route('karyawan.destroy', $k) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Yakin hapus karyawan ini? Data presensi & wajahnya ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $karyawans->links('pagination::bootstrap-5') }}
@endsection