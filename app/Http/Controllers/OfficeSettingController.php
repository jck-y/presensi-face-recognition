<?php

namespace App\Http\Controllers;

use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
    public function edit()
    {
        // Ambil data pertama. Jika tabel kosong, buatkan data default 0,0
        $office = OfficeSetting::firstOrCreate([], ['latitude' => 0, 'longitude' => 0, 'radius_meters' => 100]);

        return view('office-setting.edit', compact('office'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10',
        ]);

        $office = OfficeSetting::firstOrCreate([]);
        $office->update($data);

        return back()->with('status', 'Lokasi kantor berhasil diperbarui.');
    }
}
