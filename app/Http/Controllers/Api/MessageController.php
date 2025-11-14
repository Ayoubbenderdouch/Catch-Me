<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Like;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Send a message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_id","message"},
     *             @OA\Property(property="receiver_id", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Message sent")
     * )
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sender = $request->user();
        $receiverId = $request->receiver_id;

        // Can't message yourself
        if ($sender->id === $receiverId) {
            return response()->json([
                'message' => __('messages.cannot_message_yourself'),
            ], 400);
        }

        // Check if they are matched (both accepted each other's likes)
        $isMatch = Like::where(function ($query) use ($sender, $receiverId) {
            $query->where('from_user_id', $sender->id)
                ->where('to_user_id', $receiverId);
        })->orWhere(function ($query) use ($sender, $receiverId) {
            $query->where('from_user_id', $receiverId)
                ->where('to_user_id', $sender->id);
        })->where('status', 'accepted')->exists();

        if (!$isMatch) {
            return response()->json([
                'message' => __('messages.must_match_to_message'),
            ], 403);
        }

        // Create message with 'sent' status (one checkmark)
        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'status' => 'sent', // WhatsApp-style: starts with one checkmark
        ]);

        // Send push notification (ASYNC via Queue for better performance!)
        \App\Jobs\SendMessageNotification::dispatch(
            $receiverId,
            $sender->name,
            substr($request->message, 0, 50)
        );

        return response()->json([
            'message' => __('messages.message_sent'),
            'data' => $message,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/{userId}",
     *     summary="Get conversation with a user",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Messages retrieved")
     * )
     */
    public function getConversation(Request $request, int $userId)
    {
        $currentUser = $request->user();

        // Add pagination! Load 50 messages per page (infinite scroll)
        // This prevents loading thousands of messages at once
        $messages = Message::betweenUsers($currentUser->id, $userId)
            ->with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
            ->latest()
            ->paginate(50);

        // Mark messages from the other user as read (two green checkmarks)
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'status' => 'read',
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json($messages);
    }

    /**
     * @OA\Get(
     *     path="/api/conversations",
     *     summary="Get all conversations",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Conversations retrieved")
     * )
     */
    public function getConversations(Request $request)
    {
        $userId = $request->user()->id;

        // Get all users the current user has messaged with
        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
            ->latest()
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id === $userId ? $message->receiver_id : $message->sender_id;
            })
            ->map(function ($messages, $otherUserId) use ($userId) {
                $lastMessage = $messages->first();
                $otherUser = $lastMessage->sender_id === $userId ? $lastMessage->receiver : $lastMessage->sender;

                // Count unread messages from other user
                $unreadCount = $messages->filter(function ($msg) use ($userId) {
                    return $msg->receiver_id === $userId && !$msg->is_read;
                })->count();

                return [
                    'user' => $otherUser,
                    'last_message' => $lastMessage->message,
                    'last_message_at' => $lastMessage->created_at,
                    'unread_count' => $unreadCount,
                ];
            })
            ->values();

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * @OA\Delete(
     *     path="/api/messages/{id}",
     *     summary="Delete a message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Message deleted")
     * )
     */
    public function deleteMessage(Request $request, int $id)
    {
        $message = Message::findOrFail($id);

        // Only sender can delete the message
        if ($message->sender_id !== $request->user()->id) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        $message->delete();

        return response()->json(['message' => __('messages.message_deleted')]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/{id}/delivered",
     *     summary="Mark message as delivered (two gray checkmarks)",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Message marked as delivered")
     * )
     */
    public function markAsDelivered(Request $request, int $id)
    {
        $message = Message::findOrFail($id);

        // Only receiver can mark as delivered
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        $message->markAsDelivered();

        return response()->json([
            'message' => 'Message marked as delivered',
            'status' => $message->status,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/{id}/read",
     *     summary="Mark message as read (two green checkmarks)",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Message marked as read")
     * )
     */
    public function markAsRead(Request $request, int $id)
    {
        $message = Message::findOrFail($id);

        // Only receiver can mark as read
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        $message->markAsRead();

        return response()->json([
            'message' => 'Message marked as read',
            'status' => $message->status,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/bulk-delivered",
     *     summary="Mark multiple messages as delivered (for efficiency)",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_ids"},
     *             @OA\Property(property="message_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Messages marked as delivered")
     * )
     */
    public function bulkMarkAsDelivered(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        // Update only messages where user is the receiver
        $updated = Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', $userId)
            ->where('status', 'sent')
            ->update(['status' => 'delivered']);

        return response()->json([
            'message' => 'Messages marked as delivered',
            'updated_count' => $updated,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/bulk-read",
     *     summary="Mark multiple messages as read (for efficiency)",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_ids"},
     *             @OA\Property(property="message_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Messages marked as read")
     * )
     */
    public function bulkMarkAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        // Update only messages where user is the receiver
        $updated = Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', $userId)
            ->whereIn('status', ['sent', 'delivered'])
            ->update([
                'status' => 'read',
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'Messages marked as read',
            'updated_count' => $updated,
        ]);
    }
}
