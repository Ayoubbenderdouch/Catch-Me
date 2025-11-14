<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index()
    {
        $bannedUsers = User::where('is_banned', true)->latest()->paginate(20);
        $activityLogs = AdminActivityLog::with('admin')->latest()->paginate(20);

        return view('admin.security.index', compact('bannedUsers', 'activityLogs'));
    }

    public function activityLogs(Request $request)
    {
        $query = AdminActivityLog::with('admin');

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.security.logs', compact('logs'));
    }
}
