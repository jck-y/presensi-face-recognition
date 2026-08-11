@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 500px;">
    <h3 class="mb-2">Presensi Harian</h3>
    <p id="status-lokasi" class="text-warning fw-bold mb-4">Mencari lokasi GPS Anda...</p>

    <form method="POST" action="{{ route('presensi.store') }}" enctype="multipart/form-data">
        @csrf
        <!-- Kolom tersembunyi untuk menyimpan titik kordinat -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label class="form-label">Jenis Presensi</label>
            <select name="jenis_absensi" class="form-select">
                <option value="masuk">Masuk</option>
                <option value="pulang">Pulang</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Ambil Foto Selfie</label>
            <input type="file" name="foto" class="form-control" accept="image/*" capture="user" required>
        </div>
        
        <button type="submit" id="btn-submit" class="btn btn-primary w-100" disabled>Kirim Presensi</button>
    </form>
</div>

<script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('latitude').value = pos.coords.latitude;
            document.getElementById('longitude').value = pos.coords.longitude;
            document.getElementById('status-lokasi').innerText = 'Lokasi GPS berhasil didapatkan.';
            document.getElementById('status-lokasi').className = 'text-success fw-bold mb-4';
            document.getElementById('btn-submit').disabled = false;
        }, function(error) {
            document.getElementById('status-lokasi').innerText = 'Gagal mendapat lokasi. Tolong izinkan akses lokasi (GPS).';
            document.getElementById('status-lokasi').className = 'text-danger fw-bold mb-4';
        });
    } else {
        document.getElementById('status-lokasi').innerText = 'Browser Anda tidak mendukung GPS.';
    }
</script>
@endsection