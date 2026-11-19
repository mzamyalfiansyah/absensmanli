<?php

namespace App\Services;

use App\Models\SystemSetting;

class GeoLocationService
{
    /**
     * Calculate distance between two coordinates in meters using the Haversine formula.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Validate user location against school coordinate and configured radius.
     *
     * @return array{
     *     valid: bool,
     *     status: string,
     *     distance_meters: float|null,
     *     message: string,
     *     need_verification: bool
     * }
     */
    public static function validate(
        ?float $userLat,
        ?float $userLon,
        ?float $userAccuracy,
        ?SystemSetting $settings = null
    ): array {
        $settings = $settings ?: SystemSetting::current();

        if (! $settings->require_location) {
            return [
                'valid' => true,
                'status' => 'bypassed',
                'distance_meters' => null,
                'message' => 'Validasi lokasi dinonaktifkan oleh sekolah.',
                'need_verification' => false,
            ];
        }

        if ($userLat === null || $userLon === null) {
            $isReject = $settings->fallback_on_location_error === 'reject';

            return [
                'valid' => ! $isReject,
                'status' => $isReject ? 'outside_radius' : 'poor_accuracy',
                'distance_meters' => null,
                'message' => $isReject
                    ? 'Izin lokasi tidak terdeteksi. Absensi ditolak sesuai kebijakan sekolah.'
                    : 'Izin lokasi tidak terdeteksi. Absensi dicatat dan memerlukan verifikasi manual.',
                'need_verification' => ! $isReject,
            ];
        }

        // Validate coordinate bounds
        if ($userLat < -90 || $userLat > 90 || $userLon < -180 || $userLon > 180) {
            return [
                'valid' => false,
                'status' => 'outside_radius',
                'distance_meters' => null,
                'message' => 'Koordinat GPS yang diterima tidak valid.',
                'need_verification' => false,
            ];
        }

        // Check GPS accuracy threshold if provided
        if ($userAccuracy !== null && $userAccuracy > $settings->max_gps_accuracy_meters) {
            $isReject = $settings->fallback_on_location_error === 'reject';

            return [
                'valid' => ! $isReject,
                'status' => 'poor_accuracy',
                'distance_meters' => null,
                'message' => "Akurasi sinyal GPS Anda lemah (±{$userAccuracy} meter > batas {$settings->max_gps_accuracy_meters}m).",
                'need_verification' => ! $isReject,
            ];
        }

        $distance = static::calculateDistance(
            (float) $userLat,
            (float) $userLon,
            (float) $settings->school_latitude,
            (float) $settings->school_longitude
        );

        $allowedRadius = (float) $settings->allowed_radius_meters;

        if ($distance <= $allowedRadius) {
            return [
                'valid' => true,
                'status' => 'valid',
                'distance_meters' => $distance,
                'message' => "Lokasi valid ({$distance} meter dari titik sekolah, radius {$allowedRadius}m).",
                'need_verification' => false,
            ];
        }

        // Outside radius
        $isReject = $settings->fallback_on_location_error === 'reject';

        return [
            'valid' => ! $isReject,
            'status' => 'outside_radius',
            'distance_meters' => $distance,
            'message' => "Anda berada di luar radius sekolah ({$distance} meter > batas {$allowedRadius}m).",
            'need_verification' => ! $isReject,
        ];
    }
}
