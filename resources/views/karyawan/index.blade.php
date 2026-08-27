@extends('layouts.app')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Data Karyawan</h3>
    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary">+ Tambah Akun</a>
    @endif
</div>

<!-- Section Pimpinan -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <h5 class="mb-0">Pimpinan</h5>
        <span class="badge bg-primary">{{ $pimpinans->total() }}</span>
    </div>
    <hr class="mt-0 mb-2">
    @include('karyawan._partials.table', ['items' => $pimpinans, 'emptyMessage' => 'Belum ada pimpinan.', 'paginationName' => 'pimpinan_page'])
</div>

<!-- Section Admin -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <h5 class="mb-0">Admin</h5>
        <span class="badge bg-warning text-dark">{{ $admins->total() }}</span>
    </div>
    <hr class="mt-0 mb-2">
    @include('karyawan._partials.table', ['items' => $admins, 'emptyMessage' => 'Belum ada admin.', 'paginationName' => 'admin_page'])
</div>

<!-- Section Karyawan -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <h5 class="mb-0">Karyawan</h5>
        <span class="badge bg-success">{{ $karyawans->total() }}</span>
    </div>
    <hr class="mt-0 mb-2">
    @include('karyawan._partials.table', ['items' => $karyawans, 'emptyMessage' => 'Belum ada karyawan biasa.', 'paginationName' => 'karyawan_page'])
</div>
@endsection