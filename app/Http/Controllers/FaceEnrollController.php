<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FaceEnrollController extends Controller
{
    public function form(Karyawan $karyawan)
    {
        return view('enroll-wajah', compact('karyawan'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate(['foto' => 'required|image|max:5120']);

        try {
            $response = Http::timeout(10)
                ->withHeaders(['ngrok-skip-browser-warning' => 'true'])
                ->attach('file', file_get_contents($request->file('foto')->getRealPath()), 'wajah.jpg')
                ->post(config('services.fastapi.url') . '/enroll');
        } catch (\Throwable $e) {
            \Log::error('Gagal hubungi FastAPI saat enroll: ' . $e->getMessage());
            return back()->withErrors(['foto' => 'Servis pengenalan wajah tidak bisa dihubungi. Pastikan program di komputer admin sudah dijalankan, dan cek URL ngrok masih aktif.']);
        }

        $hasil = $response->json();

        if (!($hasil['success'] ?? false)) {
            return back()->withErrors(['foto' => $hasil['message'] ?? 'Wajah tidak terdeteksi, coba foto lain.']);
        }

        $vectorString = '[' . implode(',', $hasil['embedding']) . ']';

        DB::statement(
            'INSERT INTO wajah_karyawan (karyawan_id, embedding, created_at, updated_at)
             VALUES (?, ?::vector, NOW(), NOW())
             ON CONFLICT (karyawan_id) DO UPDATE SET embedding = EXCLUDED.embedding, updated_at = NOW()',
            [$karyawan->id, $vectorString]
        );

        return back()->with('status', 'Wajah berhasil didaftarkan untuk ' . $karyawan->nama_karyawan);
    }

    public function destroy(Karyawan $karyawan)
    {
        DB::table('wajah_karyawan')->where('karyawan_id', $karyawan->id)->delete();
        return back()->with('status', 'Data wajah berhasil dihapus, silakan daftarkan ulang.');
    }
}