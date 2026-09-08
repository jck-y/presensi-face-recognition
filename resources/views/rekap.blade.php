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
                @if(in_array(auth()->user()->role, ['admin', 'pimpinan', 'super_admin']))
                <a href="{{ route('rekap.export-pdf', request()->query()) }}" class="btn btn-danger flex-fill" target="_blank">
                    📄 Export PDF
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tampilan Mobile: Kartu -->
```php
<div class="d-md-none">
    @forelse($rekap as $row)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="fw-bold">
                            {{ $row->karyawan->nama_karyawan }}
                        </div>

                        <div class="text-muted small">
                            {{ $row->karyawan->divisi->nama_divisi }}
                        </div>
                    </div>

                    <span class="badge bg-primary">
                        {{ $row->hasil_hari }}
                    </span>
                </div>

                <div class="small mb-2">
                    <strong>Tanggal:</strong>
                    {{ $row->tanggal }}
                </div>

                <div class="small mb-2">
                    <strong>Masuk:</strong>

                    @if($row->waktu_masuk)
                        {{ \Carbon\Carbon::parse($row->waktu_masuk)->format('H:i:s') }}
                    @else
                        -
                    @endif
                </div>

                <div class="small mb-2">
                    <strong>Pulang:</strong>

                    @if($row->waktu_pulang)
                        {{ \Carbon\Carbon::parse($row->waktu_pulang)->format('H:i:s') }}
                    @else
                        -
                    @endif
                </div>

                <div class="small mb-3">
                    <strong>Status:</strong>

                    @if($row->status_absensi === 'hadir')
                        <span class="badge bg-success">Hadir</span>
                    @elseif($row->status_absensi === 'TR')
                        <span class="badge bg-warning text-dark">Terlambat</span>
                    @elseif($row->status_absensi === 'PC')
                        <span class="badge bg-warning text-dark">Pulang Cepat</span>
                    @elseif($row->status_absensi === 'pulang')
                        <span class="badge bg-success">Pulang Tepat Waktu</span>
                    @elseif($row->status_absensi === 'tidak_hadir')
                        <span class="badge bg-danger">Tidak Hadir</span>
                    @else
                        <span class="badge bg-secondary">
                            {{ $row->status_absensi }}
                        </span>
                    @endif
                </div>

                <a
                    href="{{ Storage::disk('supabase')->url($row->foto_path) }}"
                    target="_blank"
                >
                    <img
                        src="{{ Storage::disk('supabase')->url($row->foto_path) }}"
                        width="80"
                        height="80"
                        class="rounded border object-fit-cover"
                        alt="Foto presensi"
                    >
                </a>

            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            Belum ada data presensi.
        </div>
    @endforelse
</div>
```


    <!-- Tampilan Desktop: Tabel -->
    <div class="table-responsive d-none d-md-block">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Hasil Hari</th>
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

            <td>
                @if($row->waktu_masuk)
                    {{ \Carbon\Carbon::parse($row->waktu_masuk)->format('H:i:s') }}
                @else
                    -
                @endif
            </td>

            <td>
                @if($row->waktu_pulang)
                    {{ \Carbon\Carbon::parse($row->waktu_pulang)->format('H:i:s') }}
                @else
                    -
                @endif
            </td>

            <td>
                @if($row->hasil_hari === 'Hadir')
                    <span class="badge bg-success">Hadir</span>
                @elseif($row->hasil_hari === 'Terlambat')
                    <span class="badge bg-warning text-dark">Terlambat</span>
                @elseif($row->hasil_hari === 'Pulang Cepat')
                    <span class="badge bg-warning text-dark">Pulang Cepat</span>
                @elseif($row->hasil_hari === 'Terlambat & Pulang Cepat')
                    <span class="badge bg-danger">Terlambat & Pulang Cepat</span>
                @elseif($row->hasil_hari === 'Terlambat & Belum Pulang')
                    <span class="badge bg-warning text-dark">Terlambat & Belum Pulang</span>
                @elseif($row->hasil_hari === 'Belum Pulang')
                    <span class="badge bg-info text-dark">Belum Pulang</span>
                @else
                    <span class="badge bg-secondary">{{ $row->hasil_hari }}</span>
                @endif
            </td>

            <td>
                @if($row->status_absensi === 'hadir')
                    <span class="badge bg-success">Hadir</span>
                @elseif($row->status_absensi === 'TR')
                    <span class="badge bg-warning text-dark">Terlambat</span>
                @elseif($row->status_absensi === 'PC')
                    <span class="badge bg-warning text-dark">Pulang Cepat</span>
                @elseif($row->status_absensi === 'pulang')
                    <span class="badge bg-success">Pulang Tepat Waktu</span>
                @elseif($row->status_absensi === 'tidak_hadir')
                    <span class="badge bg-danger">Tidak Hadir</span>
                @else
                    <span class="badge bg-secondary">{{ $row->status_absensi }}</span>
                @endif
            </td>

            <td>
                <a
                    href="{{ Storage::disk('supabase')->url($row->foto_path) }}"
                    target="_blank"
                >
                    <img
                        src="{{ Storage::disk('supabase')->url($row->foto_path) }}"
                        width="60"
                        class="rounded border"
                        alt="Foto presensi"
                    >
                </a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center text-muted py-4">
                Belum ada data presensi.
            </td>
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