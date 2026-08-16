@extends('layouts.app')
@section('content')
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
        <select name="jenis_absensi" class="form-select mb-3">
            <option value="masuk">Masuk</option>
            <option value="pulang">Pulang</option>
        </select>

        <div class="d-flex gap-2">
            <button type="button" id="btnAmbil" class="btn btn-secondary flex-fill">Ambil Foto</button>
            <button type="button" id="btnUlang" class="btn btn-warning flex-fill" style="display:none;">Ulangi</button>
        </div>

        <button type="submit" id="btnKirim" class="btn btn-primary w-100 mt-2" disabled>Kirim Presensi</button>
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
    // Titik kantor dari database (dikirim dari server)
    $officePoint = $office ? [
        'latitude' => (float) $office->latitude,
        'longitude' => (float) $office->longitude,
        'radius' => (int) $office->radius_meters,
    ] : null;
@endphp

const office = @json($officePoint);

// Rumus jarak haversine (sama dengan App\Services\LocationService)
function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// Tombol kirim hanya aktif jika foto sudah diambil DAN lokasi masih dalam radius
function updateSubmitState() {
    btnKirim.disabled = !(fotoBlob && lokasiOK);
}

// Aktifkan Kamera Depan
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

// Ambil & pantau lokasi GPS dengan akurasi tinggi, tanpa memakai posisi basi (cache)
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

// Jepret Foto
btnAmbil.addEventListener('click', () => {
    // Sesuaikan ukuran canvas dengan rasio asli video
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

        // Matikan kamera sementara agar hemat baterai/memori
        currentStream.getTracks().forEach(track => track.stop());
    }, 'image/jpeg', 0.9);
});

// Ulangi Foto
btnUlang.addEventListener('click', () => {
    fotoBlob = null;
    updateSubmitState();
    startCamera();
});

// Kirim Data Presensi
document.getElementById('formPresensi').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('foto', fotoBlob, 'presensi.jpg');

    // Ubah teks tombol jadi loading
    const originalText = btnKirim.innerText;
    btnKirim.innerText = "Memproses Wajah...";
    btnKirim.disabled = true;

    fetch("{{ route('presensi.store') }}", {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            alert(data.status); // Sukses
            location.reload();
        } else if (data.errors) {
            // Tampilkan error jika lokasi/wajah salah
            alert(Object.values(data.errors).flat().join('\n'));
            btnKirim.innerText = originalText;
            updateSubmitState();
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan koneksi server. Coba lagi.');
        btnKirim.innerText = originalText;
        updateSubmitState();
    });
});
</script>
@endsection
