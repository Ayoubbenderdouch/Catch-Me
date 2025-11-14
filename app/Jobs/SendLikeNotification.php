<?php

namespace App\Jobs;

use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLikeNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $userId;
    public string $likerName;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $likerName)
    {
        $this->userId = $userId;
        $this->likerName = $likerName;
    }

    /**
     * Execute the job.
     * Send like notification via Firebase Cloud Messaging
     */
    public function handle(FirebaseService $firebaseService): void
    {
        $firebaseService->sendLikeNotification($this->userId, $this->likerName);
    }

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $retryAfter = 30;
}
