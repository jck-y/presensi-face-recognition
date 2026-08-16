@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 600px;">
    <h3>Pengaturan Lokasi Kantor</h3>
    <form method="POST" action="{{ route('office-setting.update') }}" data-loading-message="Menyimpan pengaturan...">
        @csrf @method('PUT')
        <div class="mb-3"><label>Latitude</label>
            <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $office->latitude) }}"></div>
        <div class="mb-3"><label>Longitude</label>
            <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $office->longitude) }}"></div>
        <div class="mb-3"><label>Radius Toleransi (meter)</label>
            <input type="number" name="radius_meters" class="form-control" value="{{ old('radius_meters', $office->radius_meters) }}"></div>
        <button class="btn btn-primary w-100">Simpan Pengaturan</button>
    </form>
    <!-- <p class="text-muted mt-3 mb-0" style="font-size: 0.9em;">
        Tip: Buka Google Maps, klik kanan pada lokasi kantor Anda. Angka yang muncul adalah kordinat. Angka kiri adalah Latitude, angka kanan adalah Longitude.
    </p> -->
</div>
@endsection