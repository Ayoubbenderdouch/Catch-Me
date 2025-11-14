<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Like;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users_today' => User::active(1440)->count(), // Last 24 hours
            'banned_users' => User::where('is_banned', true)->count(),
            'total_likes' => Like::count(),
            'total_matches' => Like::where('status', 'accepted')->count(),
            'total_messages' => Message::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'users_online' => User::active(30)->count(), // Last 30 minutes
        ];

        // Chart data: Users registered per day (last 30 days)
        $userRegistrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chart data: Matches per day (last 30 days)
        $matchesPerDay = Like::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('status', 'accepted')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'userRegistrations', 'matchesPerDay'));
    }
}
