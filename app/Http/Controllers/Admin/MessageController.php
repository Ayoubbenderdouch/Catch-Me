<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        // Get all users who have sent or received messages
        $userIds = Message::select('sender_id')
            ->union(Message::select('receiver_id'))
            ->pluck('sender_id')
            ->unique();

        $users = \App\Models\User::whereIn('id', $userIds)
            ->withCount([
                'messagesSent',
                'messagesReceived'
            ])
            ->get()
            ->map(function ($user) {
                $user->total_messages = $user->messages_sent_count + $user->messages_received_count;
                return $user;
            })
            ->sortByDesc('total_messages');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $users = $users->filter(function ($user) use ($search) {
                return stripos($user->name, $search) !== false ||
                       stripos($user->email, $search) !== false;
            });
        }

        // Stats
        $stats = [
            'total_users' => $users->count(),
            'total_messages' => Message::count(),
            'total_conversations' => Message::selectRaw('LEAST(sender_id, receiver_id) as user1, GREATEST(sender_id, receiver_id) as user2')
                ->groupBy('user1', 'user2')
                ->get()
                ->count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
        ];

        return view('admin.chats.index', compact('users', 'stats'));
    }

    public function userConversations($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        // Get all users this user has conversed with
        $conversations = collect();

        // Get users they sent messages to
        $sentTo = Message::where('sender_id', $userId)
            ->select('receiver_id')
            ->distinct()
            ->get()
            ->pluck('receiver_id');

        // Get users who sent messages to them
        $receivedFrom = Message::where('receiver_id', $userId)
            ->select('sender_id')
            ->distinct()
            ->get()
            ->pluck('sender_id');

        // Combine and get unique user IDs
        $conversationPartnerIds = $sentTo->merge($receivedFrom)->unique();

        $conversationPartners = \App\Models\User::whereIn('id', $conversationPartnerIds)
            ->get()
            ->map(function ($partner) use ($userId) {
                $messageCount = Message::betweenUsers($userId, $partner->id)->count();
                $lastMessage = Message::betweenUsers($userId, $partner->id)->latest()->first();

                $partner->message_count = $messageCount;
                $partner->last_message = $lastMessage;
                return $partner;
            })
            ->sortByDesc(function ($partner) {
                return $partner->last_message ? $partner->last_message->created_at : null;
            });

        return view('admin.chats.user-conversations', compact('user', 'conversationPartners'));
    }

    public function show($userId, $partnerId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $partner = \App\Models\User::findOrFail($partnerId);

        $messages = Message::betweenUsers($userId, $partnerId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chats.show', compact('messages', 'user', 'partner'));
    }

    public function destroy(int $id)
    {
        $message = Message::findOrFail($id);

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_message',
            "Deleted message from {$message->sender->name} to {$message->receiver->name}",
            ['message_id' => $id]
        );

        $message->delete();

        return back()->with('success', 'Message deleted successfully');
    }

    public function destroyConversation(Request $request)
    {
        $user1Id = $request->input('user1');
        $user2Id = $request->input('user2');

        if (!$user1Id || !$user2Id) {
            return back()->with('error', 'Invalid conversation');
        }

        Message::where(function ($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user1Id)->where('receiver_id', $user2Id);
        })->orWhere(function ($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user2Id)->where('receiver_id', $user1Id);
        })->delete();

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_conversation',
            "Deleted conversation between user {$user1Id} and {$user2Id}",
            ['user1_id' => $user1Id, 'user2_id' => $user2Id]
        );

        return redirect()->route('admin.messages.index')
            ->with('success', 'Conversation deleted successfully');
    }
}
