<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Presensi</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; }
        input { width: 100%; padding: .5rem; box-sizing: border-box; }
        button { width: 100%; padding: .75rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; font-size: 0.875rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login Presensi</h2>

    <!-- Tampilkan pesan error jika login gagal -->
    @if($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/login">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="Masukkan email">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Masukkan password">
        </div>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>