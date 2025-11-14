<?php

namespace App\Jobs;

use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $userId;
    public string $senderName;
    public string $messagePreview;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $senderName, string $messagePreview)
    {
        $this->userId = $userId;
        $this->senderName = $senderName;
        $this->messagePreview = $messagePreview;
    }

    /**
     * Execute the job.
     * Send message notification via Firebase Cloud Messaging
     */
    public function handle(FirebaseService $firebaseService): void
    {
        $firebaseService->sendMessageNotification(
            $this->userId,
            $this->senderName,
            $this->messagePreview
        );
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
