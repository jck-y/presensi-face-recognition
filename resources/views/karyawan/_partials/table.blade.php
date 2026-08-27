{{-- Karyawan Table Partial - used in index tabs --}}
@php
    $isSuperAdmin = auth()->user()->role === 'super_admin';
@endphp

<!-- Tampilan Mobile: Kartu -->
<div class="d-md-none">
    @forelse($items as $k)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="me-2">
                        <div class="fw-bold">{{ $k->nama_karyawan }}</div>
                        <div class="small text-muted">NIP: {{ $k->nip }}</div>
                        <div class="small text-muted">{{ $k->divisi->nama_divisi }} · {{ ucfirst($k->role) }}</div>
                    </div>
                    <div class="d-flex flex-column gap-1 flex-shrink-0">
                        @if($k->role === 'super_admin')
                            <a href="{{ route('super-admin.edit-password') }}" class="btn btn-sm btn-warning">Edit Password</a>
                        @else
                            <a href="{{ route('karyawan.edit', $k) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('karyawan.enroll-wajah', $k) }}" class="btn btn-sm btn-info">Wajah</a>
                            <form action="{{ route('karyawan.destroy', $k) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus karyawan ini? Data presensi & wajahnya ikut terhapus.')"
                                  data-loading-message="Menghapus karyawan...">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger w-100">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">{{ $emptyMessage }}</div>
    @endforelse
</div>

<!-- Tampilan Desktop: Tabel -->
<div class="table-responsive d-none d-md-block">
    <table class="table table-bordered bg-white">
        <thead>
            <tr><th>NIP</th><th>Nama</th><th>Divisi</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @foreach($items as $k)
            <tr>
                <td>{{ $k->nip }}</td>
                <td>{{ $k->nama_karyawan }}</td>
                <td>{{ $k->divisi->nama_divisi }}</td>
                <td>
                    @if($k->role === 'super_admin')
                        <a href="{{ route('super-admin.edit-password') }}" class="btn btn-sm btn-warning">Edit Password</a>
                    @else
                        <a href="{{ route('karyawan.edit', $k) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('karyawan.enroll-wajah', $k) }}" class="btn btn-sm btn-info">Kelola Wajah</a>
                        <form action="{{ route('karyawan.destroy', $k) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus karyawan ini? Data presensi & wajahnya ikut terhapus.')"
                              data-loading-message="Menghapus karyawan...">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{ $items->links('pagination::bootstrap-5') }}
