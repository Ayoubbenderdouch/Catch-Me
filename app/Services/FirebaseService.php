<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $serverKey;
    protected string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key');
    }

    /**
     * Send push notification to a single user.
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array|null $data
     * @return bool
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        string $type = 'general',
        ?array $data = null
    ): bool {
        $user = User::find($userId);

        if (!$user || !$user->fcm_token) {
            Log::warning("User {$userId} has no FCM token");
            return false;
        }

        return $this->send($user->fcm_token, $title, $body, $type, $data, $userId);
    }

    /**
     * Send push notification to multiple users.
     *
     * @param array $userIds
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array|null $data
     * @return int Number of successful sends
     */
    public function sendToMultipleUsers(
        array $userIds,
        string $title,
        string $body,
        string $type = 'general',
        ?array $data = null
    ): int {
        $users = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->get();

        $successCount = 0;

        foreach ($users as $user) {
            if ($this->send($user->fcm_token, $title, $body, $type, $data, $user->id)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send push notification to all users.
     *
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array|null $data
     * @return int Number of successful sends
     */
    public function sendToAll(
        string $title,
        string $body,
        string $type = 'general',
        ?array $data = null
    ): int {
        $users = User::whereNotNull('fcm_token')
            ->notBanned()
            ->get();

        $successCount = 0;

        foreach ($users as $user) {
            if ($this->send($user->fcm_token, $title, $body, $type, $data, $user->id)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send notification via FCM.
     *
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array|null $data
     * @param int|null $userId
     * @return bool
     */
    protected function send(
        string $fcmToken,
        string $title,
        string $body,
        string $type = 'general',
        ?array $data = null,
        ?int $userId = null
    ): bool {
        try {
            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => array_merge($data ?? [], [
                    'type' => $type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            // Store notification in database
            if ($userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'data' => $data,
                    'is_sent' => $response->successful(),
                    'sent_at' => $response->successful() ? now() : null,
                ]);
            }

            if ($response->successful()) {
                Log::info("FCM notification sent successfully to user {$userId}");
                return true;
            }

            Log::error("FCM notification failed", [
                'user_id' => $userId,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("FCM notification exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send like notification.
     *
     * @param int $userId
     * @param string $likerName
     * @return bool
     */
    public function sendLikeNotification(int $userId, string $likerName): bool
    {
        return $this->sendToUser(
            $userId,
            __('notifications.new_like'),
            __('notifications.someone_liked_you', ['name' => $likerName]),
            'like',
            ['action' => 'open_likes']
        );
    }

    /**
     * Send match notification.
     *
     * @param int $userId
     * @param string $matchName
     * @return bool
     */
    public function sendMatchNotification(int $userId, string $matchName): bool
    {
        return $this->sendToUser(
            $userId,
            __('notifications.new_match'),
            __('notifications.you_matched', ['name' => $matchName]),
            'match',
            ['action' => 'open_matches']
        );
    }

    /**
     * Send message notification.
     *
     * @param int $userId
     * @param string $senderName
     * @param string $messagePreview
     * @return bool
     */
    public function sendMessageNotification(int $userId, string $senderName, string $messagePreview): bool
    {
        return $this->sendToUser(
            $userId,
            __('notifications.new_message_from', ['name' => $senderName]),
            $messagePreview,
            'message',
            ['action' => 'open_chat', 'sender_name' => $senderName]
        );
    }
}
