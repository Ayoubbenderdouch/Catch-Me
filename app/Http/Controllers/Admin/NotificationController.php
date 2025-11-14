<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminActivityLog;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index()
    {
        return view('admin.notifications.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,single',
            'user_id' => 'required_if:target,single|exists:users,id',
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
        ]);

        $successCount = 0;

        if ($validated['target'] === 'all') {
            $successCount = $this->firebaseService->sendToAll(
                $validated['title'],
                $validated['body']
            );

            AdminActivityLog::log(
                auth('admin')->id(),
                'sent_notification_to_all',
                "Sent notification to all users: {$validated['title']}",
                ['success_count' => $successCount]
            );

            return back()->with('success', "Notification sent to {$successCount} users");
        } else {
            $success = $this->firebaseService->sendToUser(
                $validated['user_id'],
                $validated['title'],
                $validated['body']
            );

            AdminActivityLog::log(
                auth('admin')->id(),
                'sent_notification_to_user',
                "Sent notification to user ID {$validated['user_id']}: {$validated['title']}",
                ['user_id' => $validated['user_id']]
            );

            return back()->with(
                $success ? 'success' : 'error',
                $success ? 'Notification sent successfully' : 'Failed to send notification'
            );
        }
    }
}
