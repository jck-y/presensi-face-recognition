<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OfficeSetting;
use App\Services\LocationService;
use Carbon\Carbon;
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
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $todayAttendance = null;

        if ($user->role === 'admin') {
            $todayAttendance = Absensi::where('karyawan_id', $user->id)
                ->where('tanggal', $today)
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
        $nowWib = Carbon::now('Asia/Jakarta');
        $today = $nowWib->toDateString();

        $jenisAbsensi = $request->jenis_absensi;

        $existingAttendance = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $today)
            ->where('jenis_absensi', $jenisAbsensi)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'errors' => [
                    'jenis_absensi' => [
                        'Anda sudah melakukan absensi ' . $jenisAbsensi . ' hari ini.'
                    ]
                ]
            ], 422);
        }

        if ($jenisAbsensi === 'pulang') {
            $attendanceMasuk = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->first();

            if (! $attendanceMasuk) {
                return response()->json([
                    'errors' => [
                        'jenis_absensi' => [
                            'Anda belum melakukan absensi masuk hari ini.'
                        ]
                    ]
                ], 422);
            }
        }

        if (! LocationService::isWithinOffice(
            $request->latitude,
            $request->longitude
        )) {
            return response()->json([
                'errors' => [
                    'lokasi' => [
                        'Anda berada di luar radius kantor.'
                    ]
                ]
            ], 422);
        }

        $stored = DB::selectOne(
            'SELECT embedding::text AS embedding_text FROM wajah_karyawan WHERE karyawan_id = ?',
            [$karyawan->id]
        );

        if (! $stored) {
            return response()->json([
                'errors' => [
                    'foto' => [
                        'Wajah Anda belum didaftarkan, hubungi admin.'
                    ]
                ]
            ], 422);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true'
                ])
                ->attach(
                    'file',
                    file_get_contents(
                        $request->file('foto')->getRealPath()
                    ),
                    'presensi.jpg'
                )
                ->post(
                    env(
                        'FASTAPI_URL',
                        'http://127.0.0.1:8001'
                    ) . '/verify',
                    [
                        'stored_embedding' => $stored->embedding_text,
                    ]
                );
        } catch (ConnectionException $e) {
            \Log::error(
                'Gagal koneksi ke FastAPI verify: ' . $e->getMessage()
            );

            return response()->json([
                'errors' => [
                    'foto' => [
                        'Servis pengenalan wajah belum aktif. Pastikan program di komputer admin sudah dijalankan.'
                    ]
                ]
            ], 503);
        } catch (\Exception $e) {
            \Log::error(
                'Error saat verifikasi wajah: ' . $e->getMessage()
            );

            return response()->json([
                'errors' => [
                    'foto' => [
                        'Terjadi kesalahan saat verifikasi wajah.'
                    ]
                ]
            ], 500);
        }

        $hasil = $response->json();

        if (! ($hasil['match'] ?? false)) {
            return response()->json([
                'errors' => [
                    'foto' => [
                        'Wajah tidak cocok, presensi ditolak.'
                    ]
                ]
            ], 422);
        }

        $path = $request->file('foto')->store(
            'uploads',
            'supabase'
        );

        if (! $path) {
            \Log::error(
                'Upload foto ke Supabase Storage gagal. Cek kredensial SUPABASE_STORAGE_*.'
            );

            return response()->json([
                'errors' => [
                    'foto' => [
                        'Gagal menyimpan foto, coba lagi.'
                    ]
                ]
            ], 500);
        }

        $statusAbsensi = 'hadir';

        if ($jenisAbsensi === 'masuk') {
            $batasMasuk = $nowWib->copy()->setTime(
                8,
                0,
                0
            );

            if ($nowWib->greaterThan($batasMasuk)) {
                $statusAbsensi = 'TR';
            }
        }

        if ($jenisAbsensi === 'pulang') {
            $batasPulang = $nowWib->copy()->setTime(
                17,
                0,
                0
            );

            if ($nowWib->lessThan($batasPulang)) {
                $statusAbsensi = 'PC';
            } else {
                $statusAbsensi = 'pulang';
            }
        }

        $absensi = Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => $today,
            'jenis_absensi' => $jenisAbsensi,
            'waktu' => $nowWib,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'foto_path' => $path,
            'status_absensi' => $statusAbsensi,
        ]);

        \Log::info('Absensi berhasil dicatat', [
            'id' => $absensi->id,
            'karyawan_id' => $karyawan->id,
            'jenis_absensi' => $jenisAbsensi,
            'status' => $statusAbsensi,
            'waktu_wib' => $nowWib->format('Y-m-d H:i:s'),
        ]);

        if ($jenisAbsensi === 'masuk') {
            if ($statusAbsensi === 'TR') {
                $message = 'Presensi masuk berhasil. Status: Terlambat.';
            } else {
                $message = 'Presensi masuk berhasil. Status: Hadir.';
            }
        } else {
            if ($statusAbsensi === 'PC') {
                $message = 'Presensi pulang berhasil. Anda pulang sebelum pukul 17:00 WIB.';
            } else {
                $message = 'Presensi pulang berhasil.';
            }
        }

        return response()->json([
            'status' => $message
        ]);
    }
}
