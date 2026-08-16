<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OfficeSetting;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class AbsensiController extends Controller
{
    public function form()
    {
        $office = OfficeSetting::first();

        return view('presensi', compact('office'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'jenis_absensi' => 'required|in:masuk,pulang',
        ]);

        $karyawan = Auth::user();

        // 1. Cek Lokasi Database Baru
        if (! LocationService::isWithinOffice($request->latitude, $request->longitude)) {
            return response()->json(['errors' => ['lokasi' => ['Anda berada di luar radius kantor.']]], 422);
        }

        // 2. Ambil wajah terdaftar
        $stored = DB::selectOne(
            'SELECT embedding::text AS embedding_text FROM wajah_karyawan WHERE karyawan_id = ?',
            [$karyawan->id]
        );

        if (! $stored) {
            return response()->json(['errors' => ['foto' => ['Wajah Anda belum didaftarkan, hubungi admin.']]], 422);
        }

        // 3. Verifikasi AI ke FastAPI
// 3. Verifikasi AI ke FastAPI
        try {
            $response = Http::timeout(7)->attach(
                'file', file_get_contents($request->file('foto')->getRealPath()), 'presensi.jpg'
            )->post(env('FASTAPI_URL', 'http://127.0.0.1:8001') . '/verify', [
                'stored_embedding' => $stored->embedding_text,
            ]);
        } catch (ConnectionException $e) {
            return response()->json([
                'errors' => ['foto' => ['Servis pengenalan wajah belum aktif. Pastikan program di komputer admin sudah dijalankan.']]
            ], 503);
        }

        $hasil = $response->json();

        if (! ($hasil['match'] ?? false)) {
            return response()->json(['errors' => ['foto' => ['Wajah tidak cocok, presensi ditolak.']]], 422);
        }

        // 4. Catat jika sukses
        $path = $request->file('foto')->store('presensi', 'supabase');

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => now()->toDateString(),
            'jenis_absensi' => $request->jenis_absensi,
            'waktu' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'foto_path' => $path,
            'status_absensi' => 'TW',
        ]);

        return response()->json(['status' => 'Presensi berhasil dicatat.']);
    }
}
