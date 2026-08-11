<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with('divisi')->paginate(15);
        return view('karyawan.index', compact('karyawans'));
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
        $divisis = Divisi::all();
        return view('karyawan.edit', compact('karyawan', 'divisis'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $request->validate([
            'nip' => 'required|unique:karyawans,nip,' . $karyawan->id,
            'nama_karyawan' => 'required',
            'email' => 'required|email|unique:karyawans,email,' . $karyawan->id,
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
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('status', 'Karyawan berhasil dihapus.');
    }
}