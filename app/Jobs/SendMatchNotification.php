<?php

namespace App\Jobs;

use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMatchNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $userId;
    public string $matchedUserName;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $matchedUserName)
    {
        $this->userId = $userId;
        $this->matchedUserName = $matchedUserName;
    }

    /**
     * Execute the job.
     * Send match notification via Firebase Cloud Messaging
     */
    public function handle(FirebaseService $firebaseService): void
    {
        $firebaseService->sendMatchNotification($this->userId, $this->matchedUserName);
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
