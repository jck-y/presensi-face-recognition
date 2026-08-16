@extends('layouts.app')
@section('content')
<div class="card p-4">
    <h3 class="mb-4">Rekapitulasi Presensi</h3>

    <!-- Form Filter Data -->
    <div class="bg-light p-3 rounded mb-4 border">
        <form method="GET" action="{{ route('rekap.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-sm-auto">
                <label class="form-label small text-muted mb-1">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            
            @if(auth()->user()->role !== 'karyawan')
            <div class="col-12 col-sm-auto">
                <label class="form-label small text-muted mb-1">Divisi</label>
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
            
            <div class="col-12 col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill">Filter</button>
                <a href="{{ route('rekap.index') }}" class="btn btn-secondary flex-fill">Reset</a>
            </div>
        </form>
    </div>

    <!-- Tampilan Mobile: Kartu -->
    <div class="d-md-none">
        @forelse($rekap as $row)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-bold">{{ $row->karyawan->nama_karyawan }}</div>
                            <div class="text-muted small">{{ $row->karyawan->divisi->nama_divisi }}</div>
                        </div>
                        <span class="badge bg-info text-dark flex-shrink-0">{{ $row->status_absensi }}</span>
                    </div>
                    <div class="small text-muted mb-3">
                        {{ $row->tanggal }} · {{ $row->waktu }} · {{ ucfirst($row->jenis_absensi) }}
                    </div>
                    <a href="{{ Storage::disk('supabase')->url($row->foto_path) }}" target="_blank">
                        <img src="{{ Storage::disk('supabase')->url($row->foto_path) }}" width="80" height="80"
                             class="rounded border object-fit-cover" alt="Foto presensi">
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">Belum ada data presensi.</div>
        @endforelse
    </div>

    <!-- Tampilan Desktop: Tabel -->
    <div class="table-responsive d-none d-md-block">
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
                            <a href="{{ Storage::disk('supabase')->url($row->foto_path) }}" target="_blank">
                                <img src="{{ Storage::disk('supabase')->url($row->foto_path) }}" width="60" class="rounded border">
                            </a>
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