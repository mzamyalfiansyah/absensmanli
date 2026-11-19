<?php

namespace Tests\Unit;

use App\Services\GeoLocationService;
use PHPUnit\Framework\TestCase;

class GeoLocationServiceTest extends TestCase
{
    public function test_haversine_calculates_distance_accurately(): void
    {
        // Coordinate 1: SMAN 5 Karawang (-6.3053800, 107.3012500)
        // Coordinate 2: Very close point (~10 meters away)
        $distance = GeoLocationService::calculateDistance(-6.3053800, 107.3012500, -6.3053800, 107.3012500);
        $this->assertEquals(0, $distance);

        // Point ~111 meters North (approx 0.001 deg latitude)
        $distanceFar = GeoLocationService::calculateDistance(-6.3053800, 107.3012500, -6.3043800, 107.3012500);
        $this->assertGreaterThan(100, $distanceFar);
        $this->assertLessThan(120, $distanceFar);
    }
}
