<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Presensi - PT Gloria Sumber Damai</title>
    <!-- Kita gunakan Bootstrap agar tampilannya rapi otomatis -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-md navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('redirect-home') }}">Presensi</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Buka menu navigasi">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <div class="navbar-nav me-auto">
            @auth
                @if(auth()->user()->role === 'karyawan')
                    <a class="nav-link" href="{{ route('presensi.form') }}">Presensi</a>
                @endif
                @if(auth()->user()->role === 'admin')
                    <a class="nav-link" href="{{ route('presensi.form') }}">Presensi Admin</a>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'pimpinan', 'super_admin']))
                    <a class="nav-link" href="{{ route('rekap.index') }}">Rekap</a>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <a class="nav-link" href="{{ route('karyawan.index') }}">Data Karyawan</a>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <a class="nav-link" href="{{ route('office-setting.edit') }}">Pengaturan Lokasi</a>
                @endif
                @if(auth()->user()->role === 'super_admin')
                    <a class="nav-link" href="{{ route('super-admin.edit-password') }}">Edit Password</a>
                @endif
            @endauth
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 mt-2 mt-md-0">
            @auth
                <span class="text-white me-md-3 small">{{ auth()->user()->nama_karyawan }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0" data-loading-message="Sedang keluar...">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<div class="container mt-4 pb-5">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<!-- Loading Overlay: muncul saat pindah halaman, simpan data, logout, dll -->
<div id="loading-overlay" class="d-none" style="position:fixed; inset:0; z-index:9999; background:rgba(255,255,255,0.92);">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center;">
        <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status"></div>
        <div id="loading-message" class="mt-3 text-muted fw-bold">Memuat...</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingMessage = document.getElementById('loading-message');

    function showLoading(message = 'Memuat...') {
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('d-none');
    }

    function hideLoading() {
        loadingOverlay.classList.add('d-none');
    }

    // 1. Indikator loading saat pindah halaman lewat link
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href') || '';
        if (link.target === '_blank' || link.hasAttribute('download')) return;
        if (href.startsWith('#') || href.startsWith('javascript:')) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        showLoading('Memuat halaman...');
    });

    // 2. Indikator loading saat mengirim form (simpan data, logout, hapus, dll)
    //    Form presensi (#formPresensi) memakai fetch & punya penanganan sendiri.
    document.addEventListener('submit', function (e) {
        if (e.target.id === 'formPresensi') return;
        showLoading(e.target.getAttribute('data-loading-message') || 'Menyimpan data...');
    });

    // 3. Cadangan: tampilkan overlay saat halaman ditutup (tombol back, refresh, dll)
    window.addEventListener('beforeunload', function () {
        showLoading();
    });
</script>
</body>
</html>