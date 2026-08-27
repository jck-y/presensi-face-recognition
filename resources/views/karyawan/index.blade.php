@extends('layouts.app')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Data Karyawan</h3>
    @if(auth()->user()->role !== 'super_admin')
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
    @endif
</div>

<!-- Tabs Navigasi -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-karyawan" type="button" role="tab">
            Karyawan <span class="badge bg-secondary">{{ $karyawans->total() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin" type="button" role="tab">
            Admin <span class="badge bg-secondary">{{ $admins->total() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pimpinan" type="button" role="tab">
            Pimpinan <span class="badge bg-secondary">{{ $pimpinans->total() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab Karyawan -->
    <div class="tab-pane fade show active" id="tab-karyawan" role="tabpanel">
        @include('karyawan._partials.table', ['items' => $karyawans, 'emptyMessage' => 'Belum ada karyawan biasa.', 'paginationName' => 'karyawan_page'])
    </div>

    <!-- Tab Admin -->
    <div class="tab-pane fade" id="tab-admin" role="tabpanel">
        @include('karyawan._partials.table', ['items' => $admins, 'emptyMessage' => 'Belum ada admin.', 'paginationName' => 'admin_page'])
    </div>

    <!-- Tab Pimpinan -->
    <div class="tab-pane fade" id="tab-pimpinan" role="tabpanel">
        @include('karyawan._partials.table', ['items' => $pimpinans, 'emptyMessage' => 'Belum ada pimpinan.', 'paginationName' => 'pimpinan_page'])
    </div>
</div>
@endsection