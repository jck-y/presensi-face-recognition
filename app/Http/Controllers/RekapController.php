<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Divisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        // Tarik data absensi beserta data karyawan dan divisinya
        $query = Absensi::with('karyawan.divisi');

        // Jika yang login adalah karyawan biasa, batasi data HANYA miliknya sendiri
        if (Auth::user()->role === 'karyawan') {
            $query->where('karyawan_id', Auth::id());
        }

        // Fitur Filter (Pencarian berdasarkan tanggal, ID Karyawan, atau Divisi)
        $query->when($request->tanggal, fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->karyawan_id, fn ($q) => $q->where('karyawan_id', $request->karyawan_id))
            ->when($request->divisi_id, fn ($q) => $q->whereHas('karyawan', fn ($q2) => $q2->where('divisi_id', $request->divisi_id)));

        // Urutkan dari yang terbaru dan batasi 20 baris per halaman
        $rekap = $query->latest('waktu')->paginate(20);

        // Ambil daftar divisi untuk dropdown filter di tampilan
        $divisis = Divisi::all();

        return view('rekap', compact('rekap', 'divisis'));
    }

    /**
     * Export rekap presensi ke PDF (untuk admin & pimpinan).
     */
    public function exportPdf(Request $request)
    {
        $query = Absensi::with('karyawan.divisi');

        // Filter sesuai request
        $query->when($request->tanggal, fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->karyawan_id, fn ($q) => $q->where('karyawan_id', $request->karyawan_id))
            ->when($request->divisi_id, fn ($q) => $q->whereHas('karyawan', fn ($q2) => $q2->where('divisi_id', $request->divisi_id)));

        $rekap = $query->latest('waktu')->get();

        $pdf = Pdf::loadView('rekap-pdf', compact('rekap'))
            ->setPaper('a4', 'landscape');

        $filename = 'rekap-presensi-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }
}
