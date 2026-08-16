@extends('layouts.app')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Data Karyawan</h3>
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
</div>

<!-- Tampilan Mobile: Kartu -->
<div class="d-md-none">
    @forelse($karyawans as $k)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="me-2">
                        <div class="fw-bold">{{ $k->nama_karyawan }}</div>
                        <div class="small text-muted">NIP: {{ $k->nip }}</div>
                        <div class="small text-muted">{{ $k->divisi->nama_divisi }} · {{ $k->role }}</div>
                    </div>
                    <div class="d-flex flex-column gap-1 flex-shrink-0">
                        <a href="{{ route('karyawan.edit', $k) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('karyawan.enroll-wajah', $k) }}" class="btn btn-sm btn-info">Wajah</a>
                        <form action="{{ route('karyawan.destroy', $k) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus karyawan ini? Data presensi & wajahnya ikut terhapus.')"
                              data-loading-message="Menghapus karyawan...">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger w-100">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">Belum ada karyawan.</div>
    @endforelse
</div>

<!-- Tampilan Desktop: Tabel -->
<div class="table-responsive d-none d-md-block">
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
                          onsubmit="return confirm('Yakin hapus karyawan ini? Data presensi & wajahnya ikut terhapus.')"
                          data-loading-message="Menghapus karyawan...">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{ $karyawans->links('pagination::bootstrap-5') }}
@endsection