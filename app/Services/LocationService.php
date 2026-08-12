<?php

namespace App\Services;

use App\Models\OfficeSetting;

class LocationService
{
    public static function distanceInMeters($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public static function isWithinOffice($lat, $lon)
    {
        $office = OfficeSetting::first();

        // Jika Admin belum mensetting lokasi, tolak semua absensi
        if (! $office) {
            return false;
        }

        $distance = self::distanceInMeters($lat, $lon, $office->latitude, $office->longitude);

        return $distance <= $office->radius_meters;
    }
}
