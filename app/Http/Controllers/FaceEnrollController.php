<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FaceEnrollController extends Controller
{
    // Fungsi untuk menampilkan halaman form (View)
    public function form(Karyawan $karyawan)
    {
        return view('enroll-wajah', compact('karyawan'));
    }

    // Fungsi untuk memproses foto yang dikirim
    public function store(Request $request, Karyawan $karyawan)
    {
        // 1. Pastikan yang diunggah benar-benar file foto
        $request->validate(['foto' => 'required|image|max:5120']);

        // 2. Kirim foto ke FastAPI pengenalan wajah
        $response = Http::attach(
            'file',
            file_get_contents($request->file('foto')->getRealPath()),
            'wajah.jpg'
        )->post('http://127.0.0.1:8001/enroll');

        $hasil = $response->json();

        // 3. Jika wajah tidak terdeteksi oleh AI
        if (!($hasil['success'] ?? false)) {
            return back()->withErrors(['foto' => $hasil['message'] ?? 'Wajah tidak terdeteksi, coba foto lain.']);
        }

        // 4. Ubah format array menjadi string untuk pgvector
        $vectorString = '[' . implode(',', $hasil['embedding']) . ']';

        // 5. Simpan ke database (Jika sudah ada, timpa/update dengan yang baru)
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