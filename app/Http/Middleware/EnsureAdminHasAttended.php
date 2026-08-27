<?php

namespace App\Http\Middleware;

use App\Models\Absensi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasAttended
{
    /**
     * Handle an incoming request.
     *
     * If the logged-in user is an admin (not super_admin) and has NOT
     * performed a "masuk" (check-in) today, redirect them to the
     * attendance page so they must check in first.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Only applies to admin role (not super_admin, not pimpinan, not karyawan)
        if ($user && $user->role === 'admin') {
            $today = now()->toDateString();

            $hasAttended = Absensi::where('karyawan_id', $user->id)
                ->where('tanggal', $today)
                ->where('jenis_absensi', 'masuk')
                ->exists();

            if (! $hasAttended) {
                return redirect()->route('presensi.form')
                    ->with('info', 'Anda belum melakukan absensi masuk hari ini. Silakan absen terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
