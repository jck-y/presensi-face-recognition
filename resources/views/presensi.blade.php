@extends('layouts.app')
@section('content')

@if(auth()->user()->role === 'admin' && $todayAttendance)
    {{-- Admin sudah absen hari ini, tampilkan data yang sudah ada --}}
    <div class="card p-4 mx-auto" style="max-width: 400px;">
        <h3 class="mb-3">Absensi Hari Ini</h3>

        <div class="alert alert-success mb-3">
            Anda sudah melakukan absensi masuk hari ini.
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Waktu Masuk</label>
            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($todayAttendance->waktu)->format('d M Y H:i:s') }}</p>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Foto Presensi</label>
            <img src="{{ Storage::disk('supabase')->url($todayAttendance->foto_path) }}"
                 alt="Foto presensi" class="img-fluid rounded border">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Status</label>
            <p class="form-control-plaintext">
                @if($todayAttendance->status_absensi === 'hadir')
                    <span class="badge bg-success">Hadir</span>
                @elseif($todayAttendance->status_absensi === 'tidak_hadir')
                    <span class="badge bg-danger">Tidak Hadir</span>
                @elseif($todayAttendance->status_absensi === 'TW')
                    <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                @else
                    <span class="badge bg-secondary">{{ $todayAttendance->status_absensi }}</span>
                @endif
            </p>
        </div>

        <a href="{{ route('redirect-home') }}" class="btn btn-primary w-100">Kembali ke Dashboard</a>
    </div>
@else
    {{-- Form presensi (untuk karyawan atau admin yang belum absen) --}}
    <div class="card p-4 mx-auto" style="max-width: 400px;">
        <h3 class="mb-3">Presensi Harian</h3>

        <p id="status-lokasi" class="text-warning fw-bold mb-3">Mencari lokasi GPS Anda...</p>

        <video id="video" autoplay playsinline width="100%" class="border rounded d-block mb-3" style="background: #000;"></video>
        <canvas id="canvas" width="320" height="240" style="display:none"></canvas>
        <img id="preview" style="display:none; width: 100%;" class="border rounded d-block mb-3">

        <form id="formPresensi">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <label>Jenis Presensi</label>
            @if(auth()->user()->role === 'admin')
                {{-- Admin hanya bisa absen masuk --}}
                <input type="hidden" name="jenis_absensi" value="masuk">
                <div class="form-control form-control-lg mb-3 bg-light">Masuk</div>
            @else
                <select name="jenis_absensi" class="form-select form-select-lg mb-3">
                    <option value="masuk">Masuk</option>
                    <option value="pulang">Pulang</option>
                </select>
            @endif

            <div class="d-flex gap-2">
                <button type="button" id="btnAmbil" class="btn btn-lg btn-secondary flex-fill">Ambil Foto</button>
                <button type="button" id="btnUlang" class="btn btn-lg btn-warning flex-fill" style="display:none;">Ulangi</button>
            </div>

            <button type="submit" id="btnKirim" class="btn btn-lg btn-primary w-100 mt-2" disabled>Kirim Presensi</button>
        </form>
    </div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const btnAmbil = document.getElementById('btnAmbil');
const btnUlang = document.getElementById('btnUlang');
const btnKirim = document.getElementById('btnKirim');
const statusLokasi = document.getElementById('status-lokasi');
let fotoBlob = null;
let currentStream = null;
let lokasiOK = false;

@php
    $officePoint = $office ? [
        'latitude' => (float) $office->latitude,
        'longitude' => (float) $office->longitude,
        'radius' => (int) $office->radius_meters,
    ] : null;
@endphp

const office = @json($officePoint);

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateSubmitState() {
    btnKirim.disabled = !(fotoBlob && lokasiOK);
}

function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(stream => {
            currentStream = stream;
            video.srcObject = stream;
            video.style.display = 'block';
            preview.style.display = 'none';
            btnAmbil.style.display = 'block';
            btnUlang.style.display = 'none';
            updateSubmitState();
        })
        .catch(() => alert('Gagal mengakses kamera. Pastikan browser Anda mengizinkan akses kamera.'));
}
startCamera();

function updateLokasi(pos) {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    const akurasi = pos.coords.accuracy;

    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    if (!office) {
        statusLokasi.textContent = 'Lokasi GPS didapat, tetapi admin belum mengatur lokasi kantor.';
        statusLokasi.className = 'text-danger fw-bold mb-3';
        lokasiOK = false;
        updateSubmitState();
        return;
    }

    const jarak = haversine(lat, lng, office.latitude, office.longitude);
    lokasiOK = jarak <= office.radius;

    const akurasiTeks = (akurasi != null) ? `±${Math.round(akurasi)} m` : 'akurasi tidak diketahui';
    statusLokasi.textContent =
        `GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)} · akurasi ${akurasiTeks} · jarak ke kantor ${Math.round(jarak)} m (radius ${office.radius} m)`;
    statusLokasi.className = lokasiOK ? 'text-success fw-bold mb-3' : 'text-danger fw-bold mb-3';
    updateSubmitState();
}

if (navigator.geolocation) {
    navigator.geolocation.watchPosition(updateLokasi, (error) => {
        lokasiOK = false;
        statusLokasi.textContent = 'Gagal mengambil lokasi GPS. Pastikan browser mengizinkan akses lokasi dan fitur lokasi Windows menyala.';
        statusLokasi.className = 'text-danger fw-bold mb-3';
        updateSubmitState();
    }, { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 });
} else {
    statusLokasi.textContent = 'Browser Anda tidak mendukung GPS.';
    statusLokasi.className = 'text-danger fw-bold mb-3';
}

btnAmbil.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(blob => {
        fotoBlob = blob;
        preview.src = URL.createObjectURL(blob);
        preview.style.display = 'block';
        video.style.display = 'none';

        btnAmbil.style.display = 'none';
        btnUlang.style.display = 'block';
        updateSubmitState();

        currentStream.getTracks().forEach(track => track.stop());
    }, 'image/jpeg', 0.9);
});

btnUlang.addEventListener('click', () => {
    fotoBlob = null;
    updateSubmitState();
    startCamera();
});

document.getElementById('formPresensi').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('foto', fotoBlob, 'presensi.jpg');

    const originalText = btnKirim.innerText;
    btnKirim.innerText = "Memproses Wajah...";
    btnKirim.disabled = true;
    showLoading('Memproses wajah, mohon tunggu...');

    // Timeout 30 detik agar cukup untuk verifikasi ke Colab via Vercel
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);

    fetch("{{ route('presensi.store') }}", {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' },
        signal: controller.signal,
    })
    .then(res => {
        clearTimeout(timeoutId);
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            // Server return HTML (error page, redirect, dll)
            throw new Error('Server mengembalikan respons yang tidak valid (HTTP ' + res.status + ').');
        }
        return res.json();
    })
    .then(data => {
        hideLoading();
        if (data.status) {
            alert(data.status);
            window.location.replace("{{ route('presensi.form') }}");
        } else if (data.errors) {
            alert(Object.values(data.errors).flat().join('\n'));
            btnKirim.innerText = originalText;
            btnKirim.disabled = false;
            updateSubmitState();
        } else if (data.message) {
            alert('Error server: ' + data.message);
            btnKirim.innerText = originalText;
            btnKirim.disabled = false;
            updateSubmitState();
        } else {
            alert('Terjadi kesalahan tidak diketahui dari server.');
            btnKirim.innerText = originalText;
            btnKirim.disabled = false;
            updateSubmitState();
        }
    })
    .catch(err => {
        clearTimeout(timeoutId);
        hideLoading();
        if (err.name === 'AbortError') {
            alert('Permintaan habis waktu (timeout). Pastikan layanan verifikasi wajah aktif dan coba lagi.');
        } else {
            alert('Terjadi kesalahan: ' + err.message);
        }
        btnKirim.innerText = originalText;
        btnKirim.disabled = false;
        updateSubmitState();
    });
});
</script>
@endif
@endsection
