@extends('layouts.app')
@section('content')
<div class="card p-4 mx-auto" style="max-width: 500px;">
    <h3 class="mb-3">Edit Password</h3>
    <p class="text-muted mb-3">Super Admin - {{ $karyawan->email }}</p>
    <form method="POST" action="{{ route('super-admin.update-password') }}" data-loading-message="Menyimpan password...">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
        </div>
        <button class="btn btn-primary w-100">Simpan Password</button>
    </form>
</div>
@endsection
