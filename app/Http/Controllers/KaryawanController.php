<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with('divisi')
            ->where('role', 'karyawan')
            ->paginate(15, ['*'], 'karyawan_page');

        $admins = Karyawan::with('divisi')
            ->where('role', 'admin')
            ->paginate(15, ['*'], 'admin_page');

        $pimpinans = Karyawan::with('divisi')
            ->where('role', 'pimpinan')
            ->paginate(15, ['*'], 'pimpinan_page');

        return view('karyawan.index', compact('karyawans', 'admins', 'pimpinans'));
    }

    public function create()
    {
        $divisis = Divisi::all();

        return view('karyawan.create', compact('divisis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip' => 'required|unique:karyawans',
            'nama_karyawan' => 'required',
            'email' => 'required|email|unique:karyawans',
            'password' => 'required|min:6',
            'divisi_id' => 'required|exists:divisis,id',
            'role' => 'required|in:admin,pimpinan,karyawan',
            'alamat' => 'nullable',
            'no_telp' => 'nullable',
        ]);

        $data['password'] = Hash::make($data['password']);
        Karyawan::create($data);

        return redirect()->route('karyawan.index')->with('status', 'Karyawan berhasil ditambahkan. Jangan lupa daftarkan foto wajahnya.');
    }

    public function edit(Karyawan $karyawan)
    {
        // Super admin hanya bisa edit password sendiri
        if ($karyawan->role === 'super_admin') {
            return redirect()->route('super-admin.edit-password');
        }

        $divisis = Divisi::all();

        return view('karyawan.edit', compact('karyawan', 'divisis'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        // Super admin tidak bisa diedit dari sini
        if ($karyawan->role === 'super_admin') {
            return redirect()->route('karyawan.index')->with('status', 'Super admin tidak dapat diedit dari sini.');
        }

        $data = $request->validate([
            'nip' => 'required|unique:karyawans,nip,'.$karyawan->id,
            'nama_karyawan' => 'required',
            'email' => 'required|email|unique:karyawans,email,'.$karyawan->id,
            'divisi_id' => 'required|exists:divisis,id',
            'role' => 'required|in:admin,pimpinan,karyawan',
            'alamat' => 'nullable',
            'no_telp' => 'nullable',
            'password' => 'nullable|min:6',
        ]);

        $data['password'] = filled($data['password'] ?? null)
            ? Hash::make($data['password'])
            : $karyawan->password;

        $karyawan->update($data);

        return redirect()->route('karyawan.index')->with('status', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        // Super admin tidak bisa dihapus
        if ($karyawan->role === 'super_admin') {
            return redirect()->route('karyawan.index')->with('status', 'Super admin tidak dapat dihapus.');
        }

        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('status', 'Karyawan berhasil dihapus.');
    }

    /**
     * Super admin: form edit password sendiri.
     */
    public function editPassword()
    {
        $karyawan = Auth::user();

        return view('karyawan.edit-password', compact('karyawan'));
    }

    /**
     * Super admin: update password sendiri.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $karyawan = Auth::user();
        $karyawan->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('karyawan.index')->with('status', 'Password berhasil diperbarui.');
    }
}
