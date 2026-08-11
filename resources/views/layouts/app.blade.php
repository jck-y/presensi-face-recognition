<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Presensi - PT Gloria Sumber Damai</title>
    <!-- Kita gunakan Bootstrap agar tampilannya rapi otomatis -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('redirect-home') }}">Presensi</a>
    <div class="navbar-nav">
        @auth
            @if(auth()->user()->role === 'karyawan')
                <a class="nav-link" href="{{ route('presensi.form') }}">Presensi</a>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'pimpinan']))
                <a class="nav-link" href="{{ route('rekap.index') }}">Rekap</a>
            @endif
            @if(auth()->user()->role === 'admin')
                <a class="nav-link" href="{{ route('karyawan.index') }}">Kelola Karyawan</a>
            @endif
        @endauth
    </div>
    <div class="ms-auto d-flex align-items-center">
        @auth
            <span class="text-white me-3">{{ auth()->user()->nama_karyawan }} ({{ auth()->user()->role }})</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        @endauth
    </div>
</nav>

<div class="container mt-4">
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

</body>
</html>