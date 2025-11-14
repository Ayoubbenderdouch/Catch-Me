<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class LocationCacheService
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Update user location in Redis (SUPER FAST!)
     * Syncs to PostgreSQL async via database queue
     *
     * @param int $userId
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function updateLocation(int $userId, float $latitude, float $longitude): bool
    {
        $user = User::find($userId);

        if (!$user || $user->is_banned) {
            return false;
        }

        // Don't update location if user is in ghost mode
        if (!$user->is_visible) {
            return false;
        }

        // Store in Redis with 5-minute TTL (300 seconds)
        $locationData = json_encode([
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'updated_at' => time(),
        ]);

        Redis::setex("user:location:{$userId}", 300, $locationData);

        // Also update user's last_active_at in Redis
        Redis::setex("user:active:{$userId}", 1800, now()->toDateTimeString()); // 30 min

        // Queue job to sync to PostgreSQL database (async, not blocking!)
        \App\Jobs\SyncLocationToDatabase::dispatch($userId, $latitude, $longitude)
            ->delay(now()->addSeconds(30)); // Batch updates every 30 seconds

        return true;
    }

    /**
     * Get user location from Redis (or fallback to database)
     *
     * @param int $userId
     * @return array|null
     */
    public function getLocation(int $userId): ?array
    {
        $cached = Redis::get("user:location:{$userId}");

        if ($cached) {
            return json_decode($cached, true);
        }

        // Fallback to database
        $user = User::find($userId);
        if ($user && $user->hasLocation()) {
            return [
                'user_id' => $user->id,
                'latitude' => (float) $user->latitude,
                'longitude' => (float) $user->longitude,
                'updated_at' => $user->updated_at->timestamp,
            ];
        }

        return null;
    }

    /**
     * Get nearby users using Redis cache + PostGIS
     * MUCH faster than querying database every time!
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusInMeters
     * @param int|null $excludeUserId
     * @return Collection
     */
    public function getNearbyUsers(
        float $latitude,
        float $longitude,
        int $radiusInMeters = 50,
        ?int $excludeUserId = null
    ): Collection {
        // Create unique cache key based on location + radius
        $cacheKey = "nearby:" . round($latitude, 4) . ":" . round($longitude, 4) . ":{$radiusInMeters}";

        // Cache nearby users for 30 seconds (balance between freshness and performance)
        return Cache::remember($cacheKey, 30, function () use ($latitude, $longitude, $radiusInMeters, $excludeUserId) {
            // Use LocationService (PostGIS or Haversine)
            return $this->locationService->findNearbyUsers(
                $latitude,
                $longitude,
                $radiusInMeters,
                $excludeUserId
            );
        });
    }

    /**
     * Get all active users from Redis
     * (Users who updated location in last 30 minutes)
     *
     * @return array
     */
    public function getActiveUserIds(): array
    {
        $keys = Redis::keys("user:active:*");
        $userIds = [];

        foreach ($keys as $key) {
            // Extract user ID from key like "user:active:123"
            $userId = (int) str_replace('user:active:', '', $key);
            $userIds[] = $userId;
        }

        return $userIds;
    }

    /**
     * Clear location cache for specific user
     *
     * @param int $userId
     * @return void
     */
    public function clearLocationCache(int $userId): void
    {
        Redis::del("user:location:{$userId}");
        Redis::del("user:active:{$userId}");

        // Clear nearby caches that might include this user
        $pattern = "nearby:*";
        $keys = Redis::keys($pattern);
        if (!empty($keys)) {
            Redis::del(...$keys);
        }
    }

    /**
     * Bulk update locations (useful for batch processing)
     *
     * @param array $locations Array of ['user_id' => id, 'lat' => lat, 'lon' => lon]
     * @return int Number of successful updates
     */
    public function bulkUpdateLocations(array $locations): int
    {
        $success = 0;

        foreach ($locations as $location) {
            if (isset($location['user_id'], $location['lat'], $location['lon'])) {
                if ($this->updateLocation($location['user_id'], $location['lat'], $location['lon'])) {
                    $success++;
                }
            }
        }

        return $success;
    }
}
