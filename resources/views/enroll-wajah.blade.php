@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 500px;">
    <h3 class="mb-3">Daftar Wajah: {{ $karyawan->nama_karyawan }}</h3>
    <p class="text-muted">Silakan unggah atau ambil foto wajah karyawan dengan jelas.</p>

    <form method="POST" action="{{ route('karyawan.enroll-wajah.store', $karyawan->id) }}" enctype="multipart/form-data" data-loading-message="Memproses wajah...">
        @csrf
        
        <div class="mb-3">
            <label class="form-label">Pilih Foto Wajah</label>
            <input type="file" name="foto" class="form-control" accept="image/*" capture="user" required>
        </div>
        
        <button type="submit" class="btn btn-success w-100 mb-2">Daftarkan Wajah</button>
    </form>
    
    <!-- Tombol Hapus Wajah (opsional, sudah diatur di controller) -->
    <form method="POST" action="{{ route('karyawan.hapus-wajah', $karyawan->id) }}" onsubmit="return confirm('Yakin ingin menghapus data wajah ini?')" data-loading-message="Menghapus data wajah...">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger w-100">Hapus Data Wajah</button>
    </form>
</div>
@endsection