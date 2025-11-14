<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlockController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/block",
     *     summary="Block a user (Safety Feature)",
     *     tags={"Block"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", description="ID of user to block"),
     *             @OA\Property(property="reason", type="string", description="Optional reason for blocking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User blocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User blocked successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot block yourself or already blocked"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function blockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $blocker = $request->user();
        $blockedId = $request->user_id;

        if ($blocker->id === $blockedId) {
            return response()->json(['message' => 'Cannot block yourself'], 400);
        }

        $existing = BlockedUser::where('blocker_id', $blocker->id)
            ->where('blocked_id', $blockedId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'User already blocked'], 400);
        }

        BlockedUser::create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blockedId,
            'reason' => $request->reason,
        ]);

        return response()->json(['message' => 'User blocked successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/block/{userId}",
     *     summary="Unblock a user",
     *     tags={"Block"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID of user to unblock",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User unblocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User unblocked successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not blocked")
     * )
     */
    public function unblockUser(Request $request, int $userId)
    {
        $blocker = $request->user();

        $block = BlockedUser::where('blocker_id', $blocker->id)
            ->where('blocked_id', $userId)
            ->first();

        if (!$block) {
            return response()->json(['message' => 'User not blocked'], 404);
        }

        $block->delete();

        return response()->json(['message' => 'User unblocked successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/block/blocked-users",
     *     summary="Get list of blocked users",
     *     tags={"Block"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Blocked users retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="blocked_users",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="blocker_id", type="integer"),
     *                     @OA\Property(property="blocked_id", type="integer"),
     *                     @OA\Property(property="reason", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getBlockedUsers(Request $request)
    {
        $blockedUsers = BlockedUser::where('blocker_id', $request->user()->id)
            ->with('blocked:id,name,profile_image,gender')
            ->latest()
            ->get();

        return response()->json(['blocked_users' => $blockedUsers]);
    }
}
