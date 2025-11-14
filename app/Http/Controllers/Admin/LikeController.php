<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function index(Request $request)
    {
        $query = Like::with(['fromUser', 'toUser']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $likes = $query->latest()->paginate(20);

        return view('admin.likes.index', compact('likes'));
    }

    public function destroy(int $id)
    {
        $like = Like::findOrFail($id);

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_like',
            "Deleted like from {$like->fromUser->name} to {$like->toUser->name}",
            ['like_id' => $id]
        );

        $like->delete();

        return back()->with('success', 'Like deleted successfully');
    }

    public function resetMatches(Request $request)
    {
        if ($request->filled('user_id')) {
            $userId = $request->user_id;

            Like::where(function ($query) use ($userId) {
                $query->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            })->delete();

            AdminActivityLog::log(
                auth('admin')->id(),
                'reset_user_matches',
                "Reset all matches for user ID: {$userId}",
                ['user_id' => $userId]
            );

            return back()->with('success', 'User matches reset successfully');
        }

        return back()->with('error', 'User ID is required');
    }
}
