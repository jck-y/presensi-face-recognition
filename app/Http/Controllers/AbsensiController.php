<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OfficeSetting;
use App\Services\LocationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AbsensiController extends Controller
{
    public function form()
    {
        $office = OfficeSetting::first();
        $user = Auth::user();

        // Jika admin (bukan super_admin), cek apakah sudah absen masuk hari ini
        $todayAttendance = null;
        if ($user->role === 'admin') {
            $todayAttendance = Absensi::where('karyawan_id', $user->id)
                ->where('tanggal', now()->toDateString())
                ->where('jenis_absensi', 'masuk')
                ->first();
        }

        return view('presensi', compact('office', 'todayAttendance'));
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

        // Admin (bukan super_admin) hanya boleh absen masuk sekali sehari
        if ($karyawan->role === 'admin' && $request->jenis_absensi === 'masuk') {
            $alreadyAttended = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', now()->toDateString())
                ->where('jenis_absensi', 'masuk')
                ->exists();

            if ($alreadyAttended) {
                return response()->json([
                    'errors' => ['foto' => ['Anda sudah melakukan absensi masuk hari ini.']],
                ], 422);
            }
        }

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
        try {
            $response = Http::timeout(9)
                ->withHeaders(['ngrok-skip-browser-warning' => 'true'])
                ->attach(
                    'file', file_get_contents($request->file('foto')->getRealPath()), 'presensi.jpg'
                )->post(env('FASTAPI_URL', 'http://127.0.0.1:8001').'/verify', [
                    'stored_embedding' => $stored->embedding_text,
                ]);
        } catch (ConnectionException $e) {
            \Log::error('Gagal koneksi ke FastAPI verify: '.$e->getMessage());

            return response()->json([
                'errors' => ['foto' => ['Servis pengenalan wajah belum aktif. Pastikan program di komputer admin sudah dijalankan.']],
            ], 503);
        } catch (\Exception $e) {
            \Log::error('Error saat verifikasi wajah: '.$e->getMessage());

            return response()->json([
                'errors' => ['foto' => ['Terjadi kesalahan saat verifikasi wajah.']],
            ], 500);
        }
        $hasil = $response->json();

        if (! ($hasil['match'] ?? false)) {
            return response()->json(['errors' => ['foto' => ['Wajah tidak cocok, presensi ditolak.']]], 422);
        }

        // 4. Catat jika sukses
        $path = $request->file('foto')->store('uploads', 'supabase');
        if (! $path) {
            \Log::error('Upload foto ke Supabase Storage gagal. Cek kredensial SUPABASE_STORAGE_*.');

            return response()->json(['errors' => ['foto' => ['Gagal menyimpan foto, coba lagi.']]], 500);
        }
        \Log::info('Foto berhasil diupload ke Supabase: '.$path);

        // Admin langsung 'hadir' (tidak perlu verifikasi), karyawan tetap 'TW'
        $status = $karyawan->role === 'admin' ? 'hadir' : 'TW';

        $absensi = Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => now()->toDateString(),
            'jenis_absensi' => $request->jenis_absensi,
            'waktu' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'foto_path' => $path,
            'status_absensi' => $status,
        ]);
        \Log::info('Absensi berhasil dicatat', [
            'id' => $absensi->id,
            'karyawan_id' => $karyawan->id,
            'status' => $status,
        ]);

        return response()->json(['status' => 'Presensi berhasil dicatat.']);
    }
}
