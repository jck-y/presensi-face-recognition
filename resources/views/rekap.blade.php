@extends('layouts.app')
@section('content')
<div class="card p-4">
    <h3 class="mb-4">Rekapitulasi Presensi</h3>

    <!-- Form Filter Data -->
    <div class="bg-light p-3 rounded mb-4 border">
        <form method="GET" action="{{ route('rekap.index') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            
            @if(auth()->user()->role !== 'karyawan')
            <div class="col-auto">
                <select name="divisi_id" class="form-select">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisis as $div)
                        <option value="{{ $div->id }}" {{ request('divisi_id') == $div->id ? 'selected' : '' }}>
                            {{ $div->nama_divisi }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="col-auto">
                <button type="submit" class="btn btn-success">Filter</button>
                <a href="{{ route('rekap.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $row)
                    <tr>
                        <td>{{ $row->karyawan->nama_karyawan }}</td>
                        <td>{{ $row->karyawan->divisi->nama_divisi }}</td>
                        <td>{{ $row->tanggal }}</td>
                        <td>{{ $row->waktu }}</td>
                        <td>{{ ucfirst($row->jenis_absensi) }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $row->status_absensi }}</span>
                        </td>
                        <td>
                            <a href="{{ asset('storage/' . $row->foto_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Link Pagination (jika datanya banyak) -->
    <div class="mt-3">
        {{ $rekap->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection