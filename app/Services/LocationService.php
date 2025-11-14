<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class LocationService
{
    /**
     * Add random offset to location for privacy (Anti-Stalking)
     * App Store Compliance: Prevent exact location tracking!
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusMeters Random offset radius (default 20m)
     * @return array ['latitude' => float, 'longitude' => float]
     */
    public function fuzzLocation(float $latitude, float $longitude, int $radiusMeters = 20): array
    {
        // Convert meters to degrees (approximate)
        // 1 degree latitude ≈ 111,320 meters
        $latOffset = ($radiusMeters / 111320) * (rand(-100, 100) / 100);

        // 1 degree longitude varies by latitude
        $lonOffset = ($radiusMeters / (111320 * cos(deg2rad($latitude)))) * (rand(-100, 100) / 100);

        return [
            'latitude' => $latitude + $latOffset,
            'longitude' => $longitude + $lonOffset,
        ];
    }

    /**
     * Format distance for display (hide exact distance)
     * App Store Compliance: Don't show exact meters!
     *
     * @param float $meters
     * @return string
     */
    public function formatDistanceForDisplay(float $meters): string
    {
        if ($meters < 100) {
            return 'Nearby'; // Don't show exact distance under 100m
        } elseif ($meters < 500) {
            return '< 500m';
        } elseif ($meters < 1000) {
            return '< 1km';
        } else {
            return round($meters / 1000, 1) . 'km';
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find users within a specific radius of given coordinates.
     * Uses PostGIS for 10x faster performance with 100k+ users!
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusInMeters
     * @param int|null $excludeUserId User ID to exclude from results
     * @return Collection
     */
    public function findNearbyUsers(
        float $latitude,
        float $longitude,
        int $radiusInMeters = 50,
        ?int $excludeUserId = null
    ): Collection {
        // Check if PostGIS is available
        if ($this->hasPostGIS()) {
            return $this->findNearbyUsersPostGIS($latitude, $longitude, $radiusInMeters, $excludeUserId);
        }

        // Fallback to Haversine formula (slower but works without PostGIS)
        return $this->findNearbyUsersHaversine($latitude, $longitude, $radiusInMeters, $excludeUserId);
    }

    /**
     * Find nearby users using PostGIS (SUPER FAST - 10x faster than Haversine!)
     */
    protected function findNearbyUsersPostGIS(
        float $latitude,
        float $longitude,
        int $radiusInMeters,
        ?int $excludeUserId
    ): Collection {
        $point = "ST_MakePoint($longitude, $latitude)::geography";

        $query = User::query()
            ->visible()
            ->publicAccount()
            ->notBanned()
            ->whereNotNull('location')
            ->selectRaw("*, ST_Distance(location, $point) as distance")
            ->whereRaw("ST_DWithin(location, $point, ?)", [$radiusInMeters])
            ->orderBy('distance');

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->get()->map(function ($user) {
            $user->distance = round($user->distance, 2);
            return $user;
        });
    }

    /**
     * Find nearby users using Haversine formula (Fallback method)
     */
    protected function findNearbyUsersHaversine(
        float $latitude,
        float $longitude,
        int $radiusInMeters,
        ?int $excludeUserId
    ): Collection {
        // Calculate bounding box for initial filter (optimization)
        $latDelta = $radiusInMeters / 111320; // 1 degree latitude ≈ 111.32 km
        $lonDelta = $radiusInMeters / (111320 * cos(deg2rad($latitude)));

        $query = User::query()
            ->visible()
            ->publicAccount()
            ->notBanned()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lonDelta, $longitude + $lonDelta]);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $users = $query->get();

        // Filter by exact distance using Haversine formula
        return $users->filter(function ($user) use ($latitude, $longitude, $radiusInMeters) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                (float) $user->latitude,
                (float) $user->longitude
            );

            $user->distance = round($distance, 2);

            return $distance <= $radiusInMeters;
        })->sortBy('distance')->values();
    }

    /**
     * Check if PostGIS extension is available
     * PostGIS is only available on PostgreSQL, not MySQL
     */
    protected function hasPostGIS(): bool
    {
        try {
            // Check if we're using PostgreSQL
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver");

            if ($connection !== 'pgsql') {
                return false; // PostGIS only works with PostgreSQL
            }

            $result = \DB::select("SELECT EXISTS(SELECT 1 FROM pg_extension WHERE extname = 'postgis') as has_postgis");
            return $result[0]->has_postgis ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update user's location.
     * Updates both latitude/longitude AND PostGIS location column
     *
     * @param int $userId
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function updateUserLocation(int $userId, float $latitude, float $longitude): bool
    {
        $user = User::find($userId);

        if (!$user || $user->is_banned) {
            return false;
        }

        // Don't update location if user is in ghost mode
        if (!$user->is_visible) {
            return false;
        }

        $user->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_active_at' => now(),
        ]);

        // Also update PostGIS location column if available
        if ($this->hasPostGIS()) {
            \DB::update(
                "UPDATE users SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?",
                [$longitude, $latitude, $userId]
            );
        }

        return true;
    }

    /**
     * Get all online users with their locations for admin map.
     *
     * @param int $activeWithinMinutes
     * @return Collection
     */
    public function getOnlineUsersWithLocation(int $activeWithinMinutes = 30): Collection
    {
        return User::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->active($activeWithinMinutes)
            ->notBanned()
            ->select('id', 'name', 'profile_image', 'latitude', 'longitude', 'last_active_at', 'gender')
            ->get();
    }
}
