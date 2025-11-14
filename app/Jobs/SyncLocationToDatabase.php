<?php

namespace App\Jobs;

use App\Services\LocationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncLocationToDatabase implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $userId;
    public float $latitude;
    public float $longitude;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, float $latitude, float $longitude)
    {
        $this->userId = $userId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Execute the job.
     * Syncs location from Redis to PostgreSQL database
     */
    public function handle(LocationService $locationService): void
    {
        // Update location in PostgreSQL database
        $locationService->updateUserLocation(
            $this->userId,
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $retryAfter = 60;
}
