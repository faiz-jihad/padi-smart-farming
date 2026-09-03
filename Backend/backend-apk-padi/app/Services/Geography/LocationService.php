<?php

namespace App\Services\Geography;

use App\Models\District;
use App\Models\DistrictBoundary;
use App\Models\Village;
use App\Models\VillageBoundary;
use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Resolve GPS coordinates to Administrative Hierarchy (Village, District, Regency, Province)
     *
     * Algorithm:
     * 1. Check point-in-polygon for Village boundaries.
     * 2. If not found, check point-in-polygon for District boundaries.
     * 3. Fallback: Find nearest District by coordinate Euclidean/Haversine distance.
     *
     * TODO: When migrating to PostgreSQL/PostGIS or MySQL 8 Spatial, replace with ST_Contains / ST_Within.
     */
    public function resolveCoordinates(float $latitude, float $longitude): ?array
    {
        // 1. Try resolving Village by polygon
        $villageBoundary = $this->findBoundaryContainingPoint(VillageBoundary::class, $latitude, $longitude);
        if ($villageBoundary) {
            $village = Village::with('district.regency.province')->find($villageBoundary->village_id);
            if ($village) {
                return $this->formatResult(
                    province: $village->district?->regency?->province,
                    regency: $village->district?->regency,
                    district: $village->district,
                    village: $village,
                    method: 'polygon_exact'
                );
            }
        }

        // 2. Try resolving District by polygon
        $districtBoundary = $this->findBoundaryContainingPoint(DistrictBoundary::class, $latitude, $longitude);
        if ($districtBoundary) {
            $district = District::with(['regency.province', 'villages'])->find($districtBoundary->district_id);
            if ($district) {
                // Find nearest village inside this district if possible
                $nearestVillage = $this->findNearestVillageInDistrict($district->id, $latitude, $longitude);

                return $this->formatResult(
                    province: $district->regency?->province,
                    regency: $district->regency,
                    district: $district,
                    village: $nearestVillage,
                    method: 'district_polygon'
                );
            }
        }

        // 3. Fallback: Nearest district by centroid distance
        $nearestDistrict = $this->findNearestDistrict($latitude, $longitude);

        if ($nearestDistrict) {
            $distance = $this->haversineDistance(
                $latitude,
                $longitude,
                (float) $nearestDistrict->latitude,
                (float) $nearestDistrict->longitude
            );

            if ($distance > 30) {
                return null;
            }

            $nearestVillage = $this->findNearestVillageInDistrict(
                $nearestDistrict->id,
                $latitude,
                $longitude
            );

            return $this->formatResult(
                province: $nearestDistrict->regency?->province,
                regency: $nearestDistrict->regency,
                district: $nearestDistrict,
                village: $nearestVillage,
                method: 'nearest_centroid'
            );
        }

        return null;
    }

    /**
     * Find a boundary record whose geometry contains the point (lat, lng).
     */
    private function findBoundaryContainingPoint(
        string $modelClass,
        float $latitude,
        float $longitude
    ): ?object {
        $foreignKey = $modelClass === VillageBoundary::class
            ? 'village_id'
            : 'district_id';

        $candidates = $modelClass::query()
            ->select(['id', 'bbox'])
            ->whereNotNull('bbox')
            ->get()
            ->filter(function ($boundary) use ($latitude, $longitude) {
                if (!is_array($boundary->bbox) || count($boundary->bbox) < 4) {
                    return false;
                }

                [$minLng, $minLat, $maxLng, $maxLat] = $boundary->bbox;

                return $longitude >= (float) $minLng
                    && $longitude <= (float) $maxLng
                    && $latitude >= (float) $minLat
                    && $latitude <= (float) $maxLat;
            });

        foreach ($candidates as $candidate) {
            $boundary = $modelClass::query()
                ->select([
                    'id',
                    $foreignKey,
                    'geometry',
                    'bbox',
                ])
                ->find($candidate->id);

            if (!$boundary) {
                continue;
            }

            $geometry = $boundary->geometry_array;

            if (!$geometry) {
                continue;
            }

            if ($this->isPointInGeometry(
                $longitude,
                $latitude,
                $geometry
            )) {
                return $boundary;
            }
        }

        return null;
    }

    /**
     * Ray-Casting Point-in-Polygon Algorithm for GeoJSON Polygon and MultiPolygon
     */
    public function isPointInGeometry(float $x, float $y, array $geometry): bool
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            return $this->isPointInPolygonRings($x, $y, $coords);
        }

        if ($type === 'MultiPolygon') {
            foreach ($coords as $polygonCoords) {
                if ($this->isPointInPolygonRings($x, $y, $polygonCoords)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if point is inside outer ring and not in any inner holes
     */
    private function isPointInPolygonRings(float $x, float $y, array $rings): bool
    {
        if (empty($rings)) {
            return false;
        }

        // Outer ring
        if (!$this->pointInPolygon($x, $y, $rings[0])) {
            return false;
        }

        // Holes (inner rings)
        $ringCount = count($rings);
        for ($i = 1; $i < $ringCount; $i++) {
            if ($this->pointInPolygon($x, $y, $rings[$i])) {
                return false; // Inside hole -> not in polygon
            }
        }

        return true;
    }

    /**
     * Ray-Casting algorithm on a single ring of vertices
     */
    private function pointInPolygon(float $x, float $y, array $polygon): bool
    {
        $inside = false;
        $numPoints = count($polygon);

        for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
            $xi = (float) $polygon[$i][0];
            $yi = (float) $polygon[$i][1];
            $xj = (float) $polygon[$j][0];
            $yj = (float) $polygon[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 0.00000001) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Find nearest District by Haversine formula
     */
    private function findNearestDistrict(float $lat, float $lng): ?District
    {
        $districts = District::with('regency.province')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $nearest = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($districts as $district) {
            $dist = $this->haversineDistance($lat, $lng, (float) $district->latitude, (float) $district->longitude);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $district;
            }
        }

        return $nearest;
    }

    /**
     * Find nearest Village within a given District
     */
    private function findNearestVillageInDistrict(int $districtId, float $lat, float $lng): ?Village
    {
        $villages = Village::where('district_id', $districtId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $nearest = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($villages as $village) {
            $dist = $this->haversineDistance($lat, $lng, (float) $village->latitude, (float) $village->longitude);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $village;
            }
        }

        return $nearest;
    }

    /**
     * Calculate distance between 2 coordinates in kilometers (Haversine)
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function formatResult($province, $regency, $district, $village, string $method): array
    {
        return [
            'province' => $province ? [
                'id'        => $province->id,
                'code'      => $province->code,
                'name'      => $province->name,
                'latitude'  => $province->latitude !== null ? (float) $province->latitude : null,
                'longitude' => $province->longitude !== null ? (float) $province->longitude : null,
            ] : null,
            'regency' => $regency ? [
                'id'         => $regency->id,
                'code'       => $regency->code,
                'name'       => $regency->name,
                'type'       => $regency->type?->value,
                'latitude'   => $regency->latitude !== null ? (float) $regency->latitude : null,
                'longitude'  => $regency->longitude !== null ? (float) $regency->longitude : null,
            ] : null,
            'district' => $district ? [
                'id'        => $district->id,
                'code'      => $district->code,
                'name'      => $district->name,
                'latitude'  => $district->latitude !== null ? (float) $district->latitude : null,
                'longitude' => $district->longitude !== null ? (float) $district->longitude : null,
            ] : null,
            'village' => $village ? [
                'id'        => $village->id,
                'code'      => $village->code,
                'name'      => $village->name,
                'type'      => $village->type?->value,
                'latitude'  => $village->latitude !== null ? (float) $village->latitude : null,
                'longitude' => $village->longitude !== null ? (float) $village->longitude : null,
            ] : null,
            'formatted_address' => implode(', ', array_filter([
                $village ? ($village->type?->value === 'urban_village' ? 'Kel. ' : 'Desa ') . $village->name : null,
                $district ? 'Kec. ' . $district->name : null,
                $regency ? ($regency->type?->value === 'city' ? 'Kota ' : 'Kab. ') . $regency->name : null,
                $province ? 'Prov. ' . $province->name : null,
            ])),
            'resolution_method' => $method,
        ];
    }
}
