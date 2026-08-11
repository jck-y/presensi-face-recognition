<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AbsensiController extends Controller
{
    public function form()
    {
        return view('presensi');
    }

    public function store(Request $request)
    {
        // 1. Validasi inputan
        $request->validate([
            'foto' => 'required|image|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'jenis_absensi' => 'required|in:masuk,pulang',
        ]);

        $karyawan = Auth::user();

        // 2. Cek lokasi (Apakah masuk di dalam radius kantor?)
        if (!LocationService::isWithinOffice($request->latitude, $request->longitude)) {
            return back()->withErrors(['lokasi' => 'Anda berada di luar radius kantor.']);
        }

        // 3. Ambil data wajah (embedding) karyawan dari database
        $stored = DB::selectOne(
            'SELECT embedding::text AS embedding_text FROM wajah_karyawan WHERE karyawan_id = ?',
            [$karyawan->id]
        );

        if (!$stored) {
            return back()->withErrors(['foto' => 'Wajah Anda belum didaftarkan. Hubungi admin.']);
        }

        // 4. Verifikasi kecocokan wajah ke FastAPI
        $response = Http::attach(
            'file',
            file_get_contents($request->file('foto')->getRealPath()),
            'presensi.jpg'
        )->post('http://127.0.0.1:8001/verify', [
            'stored_embedding' => $stored->embedding_text,
        ]);

        $hasil = $response->json();

        if (!($hasil['match'] ?? false)) {
            return back()->withErrors(['foto' => 'Wajah tidak cocok. Presensi ditolak!']);
        }

        // 5. Jika cocok, simpan foto ke folder storage dan catat di database
        $path = $request->file('foto')->store('presensi', 'public');

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => now()->toDateString(),
            'jenis_absensi' => $request->jenis_absensi,
            'waktu' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'foto_path' => $path,
            'status_absensi' => 'TW', // Tepat Waktu (sementara hardcode dulu)
        ]);

        return back()->with('status', 'Presensi ' . $request->jenis_absensi . ' berhasil dicatat!');
    }
}