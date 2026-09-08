<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Divisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with('karyawan.divisi');

        if (Auth::user()->role === 'karyawan') {
            $query->where('karyawan_id', Auth::id());
        }

        $query->when(
            $request->tanggal,
            fn ($q) => $q->whereDate('tanggal', $request->tanggal)
        )
        ->when(
            $request->karyawan_id,
            fn ($q) => $q->where('karyawan_id', $request->karyawan_id)
        )
        ->when(
            $request->divisi_id,
            fn ($q) => $q->whereHas(
                'karyawan',
                fn ($q2) => $q2->where(
                    'divisi_id',
                    $request->divisi_id
                )
            )
        );

        $rekap = $query
            ->latest('waktu')
            ->paginate(20)
            ->withQueryString();

        $rekap->getCollection()->transform(function ($row) {
            $tanggal = $row->tanggal instanceof Carbon
                ? $row->tanggal->toDateString()
                : $row->tanggal;

            $masuk = Absensi::where('karyawan_id', $row->karyawan_id)
                ->where('tanggal', $tanggal)
                ->where('jenis_absensi', 'masuk')
                ->first();

            $pulang = Absensi::where('karyawan_id', $row->karyawan_id)
                ->where('tanggal', $tanggal)
                ->where('jenis_absensi', 'pulang')
                ->first();

            $row->waktu_masuk = $masuk?->waktu;
            $row->waktu_pulang = $pulang?->waktu;

            if (! $masuk) {
                $row->hasil_hari = 'Belum Absen';
            } elseif (! $pulang) {
                if ($masuk->status_absensi === 'TR') {
                    $row->hasil_hari = 'Terlambat & Belum Pulang';
                } else {
                    $row->hasil_hari = 'Belum Pulang';
                }
            } else {
                $masukTerlambat = $masuk->status_absensi === 'TR';
                $pulangCepat = $pulang->status_absensi === 'PC';

                if ($masukTerlambat && $pulangCepat) {
                    $row->hasil_hari = 'Terlambat & Pulang Cepat';
                } elseif ($masukTerlambat) {
                    $row->hasil_hari = 'Terlambat';
                } elseif ($pulangCepat) {
                    $row->hasil_hari = 'Pulang Cepat';
                } else {
                    $row->hasil_hari = 'Hadir';
                }
            }

            return $row;
        });

        $divisis = Divisi::all();

        return view(
            'rekap',
            compact('rekap', 'divisis')
        );
    }

    public function updateStatus(
        Request $request,
        Absensi $absensi
    ) {
        $request->validate([
            'status_absensi' => 'required|in:hadir,tidak_hadir',
        ]);

        $user = Auth::user();

        if (
            $user->role === 'admin' &&
            $absensi->karyawan_id === $user->id
        ) {
            return back()->withErrors(
                'Anda tidak dapat mengubah status absensi sendiri.'
            );
        }

        $absensi->update([
            'status_absensi' => $request->status_absensi,
        ]);

        return back()->with(
            'status',
            'Status absensi berhasil diperbarui.'
        );
    }

    public function exportPdf(Request $request)
    {
        $query = Absensi::with('karyawan.divisi');

        $query->when(
            $request->tanggal,
            fn ($q) => $q->whereDate(
                'tanggal',
                $request->tanggal
            )
        )
        ->when(
            $request->karyawan_id,
            fn ($q) => $q->where(
                'karyawan_id',
                $request->karyawan_id
            )
        )
        ->when(
            $request->divisi_id,
            fn ($q) => $q->whereHas(
                'karyawan',
                fn ($q2) => $q2->where(
                    'divisi_id',
                    $request->divisi_id
                )
            )
        );

        $rekap = $query
            ->latest('waktu')
            ->get();

        $rekap->transform(function ($row) {
            $tanggal = $row->tanggal instanceof Carbon
                ? $row->tanggal->toDateString()
                : $row->tanggal;

            $masuk = Absensi::where('karyawan_id', $row->karyawan_id)
                ->where('tanggal', $tanggal)
                ->where('jenis_absensi', 'masuk')
                ->first();

            $pulang = Absensi::where('karyawan_id', $row->karyawan_id)
                ->where('tanggal', $tanggal)
                ->where('jenis_absensi', 'pulang')
                ->first();

            $row->waktu_masuk = $masuk?->waktu;
            $row->waktu_pulang = $pulang?->waktu;

            if (! $masuk) {
                $row->hasil_hari = 'Belum Absen';
            } elseif (! $pulang) {
                $row->hasil_hari = $masuk->status_absensi === 'TR'
                    ? 'Terlambat & Belum Pulang'
                    : 'Belum Pulang';
            } else {
                $masukTerlambat = $masuk->status_absensi === 'TR';
                $pulangCepat = $pulang->status_absensi === 'PC';

                if ($masukTerlambat && $pulangCepat) {
                    $row->hasil_hari = 'Terlambat & Pulang Cepat';
                } elseif ($masukTerlambat) {
                    $row->hasil_hari = 'Terlambat';
                } elseif ($pulangCepat) {
                    $row->hasil_hari = 'Pulang Cepat';
                } else {
                    $row->hasil_hari = 'Hadir';
                }
            }

            return $row;
        });

        $pdf = Pdf::loadView(
            'rekap-pdf',
            compact('rekap')
        )->setPaper('a4', 'landscape');

        $filename = 'rekap-presensi-' .
            Carbon::now('Asia/Jakarta')->format('Y-m-d') .
            '.pdf';

        return $pdf->download($filename);
    }
}