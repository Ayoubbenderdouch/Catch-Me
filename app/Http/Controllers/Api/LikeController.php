<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="Send a like to another user",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to_user_id"},
     *             @OA\Property(property="to_user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Like sent"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function sendLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fromUser = $request->user();
        $toUserId = $request->to_user_id;

        // Can't like yourself
        if ($fromUser->id === $toUserId) {
            return response()->json([
                'message' => __('messages.cannot_like_yourself'),
            ], 400);
        }

        // Check if already liked
        $existingLike = Like::where('from_user_id', $fromUser->id)
            ->where('to_user_id', $toUserId)
            ->first();

        if ($existingLike) {
            return response()->json([
                'message' => __('messages.already_liked'),
                'status' => $existingLike->status,
            ], 400);
        }

        // Check if the other user already liked you (instant match)
        $reverseLike = Like::where('from_user_id', $toUserId)
            ->where('to_user_id', $fromUser->id)
            ->first();

        $isMatch = false;

        if ($reverseLike && $reverseLike->status === 'pending') {
            // It's a match! Auto-accept both likes
            $reverseLike->update(['status' => 'accepted']);
            $status = 'accepted';
            $isMatch = true;

            // Send match notification to both users (ASYNC via Queue!)
            // This is 10x faster than blocking FCM calls
            \App\Jobs\SendMatchNotification::dispatch($toUserId, $fromUser->name);
            \App\Jobs\SendMatchNotification::dispatch($fromUser->id, User::find($toUserId)->name);
        } else {
            $status = 'pending';
            // Send like notification (ASYNC via Queue!)
            \App\Jobs\SendLikeNotification::dispatch($toUserId, $fromUser->name);
        }

        // Create the like
        $like = Like::create([
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUserId,
            'status' => $status,
        ]);

        return response()->json([
            'message' => $isMatch ? __('messages.its_a_match') : __('messages.like_sent'),
            'is_match' => $isMatch,
            'like' => $like,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/likes/{id}/accept",
     *     summary="Accept a like",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Like accepted")
     * )
     */
    public function acceptLike(Request $request, int $id)
    {
        $like = Like::findOrFail($id);

        // Verify the like is for the authenticated user
        if ($like->to_user_id !== $request->user()->id) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        if ($like->status !== 'pending') {
            return response()->json([
                'message' => __('messages.like_already_processed'),
            ], 400);
        }

        $like->update(['status' => 'accepted']);

        // Check if you also liked them (create mutual match)
        $yourLike = Like::where('from_user_id', $request->user()->id)
            ->where('to_user_id', $like->from_user_id)
            ->first();

        $isMatch = $yourLike && $yourLike->status === 'accepted';

        // Send match notification (ASYNC via Queue!)
        \App\Jobs\SendMatchNotification::dispatch(
            $like->from_user_id,
            $request->user()->name
        );

        return response()->json([
            'message' => __('messages.like_accepted'),
            'is_match' => $isMatch,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/likes/{id}/reject",
     *     summary="Reject a like",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Like rejected")
     * )
     */
    public function rejectLike(Request $request, int $id)
    {
        $like = Like::findOrFail($id);

        if ($like->to_user_id !== $request->user()->id) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        if ($like->status !== 'pending') {
            return response()->json([
                'message' => __('messages.like_already_processed'),
            ], 400);
        }

        $like->update(['status' => 'rejected']);

        return response()->json([
            'message' => __('messages.like_rejected'),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/likes/received",
     *     summary="Get received likes (pending)",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Received likes retrieved")
     * )
     */
    public function receivedLikes(Request $request)
    {
        $likes = Like::where('to_user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('fromUser:id,name,profile_image,photos,gender,bio')
            ->latest()
            ->get();

        return response()->json(['likes' => $likes]);
    }

    /**
     * @OA\Get(
     *     path="/api/matches",
     *     summary="Get all matches",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Matches retrieved")
     * )
     */
    public function matches(Request $request)
    {
        $userId = $request->user()->id;

        // Get all accepted likes where user is involved
        $matches = Like::where(function ($query) use ($userId) {
            $query->where('from_user_id', $userId)
                ->orWhere('to_user_id', $userId);
        })
            ->where('status', 'accepted')
            ->with(['fromUser:id,name,profile_image,photos,gender,bio', 'toUser:id,name,profile_image,photos,gender,bio'])
            ->latest()
            ->get()
            ->map(function ($like) use ($userId) {
                // Return the other user (not the current user)
                $matchedUser = $like->from_user_id === $userId ? $like->toUser : $like->fromUser;
                return [
                    'like_id' => $like->id,
                    'matched_at' => $like->updated_at,
                    'user' => $matchedUser,
                ];
            })
            ->unique('user.id')
            ->values();

        return response()->json(['matches' => $matches]);
    }
}
