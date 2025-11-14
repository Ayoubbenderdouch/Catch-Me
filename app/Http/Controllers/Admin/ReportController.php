<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reportedUser', 'reviewer']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function show(int $id)
    {
        $report = Report::with(['reporter', 'reportedUser', 'reviewer'])->findOrFail($id);
        return view('admin.reports.show', compact('report'));
    }

    public function markAsReviewed(Request $request, int $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status' => 'reviewed',
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        AdminActivityLog::log(
            auth('admin')->id(),
            'reviewed_report',
            "Reviewed report ID: {$id}",
            ['report_id' => $id]
        );

        return back()->with('success', 'Report marked as reviewed');
    }

    public function banReportedUser(int $id)
    {
        $report = Report::with('reportedUser')->findOrFail($id);

        $report->reportedUser->update(['is_banned' => true]);

        $report->update([
            'status' => 'actioned',
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => 'User banned based on this report',
        ]);

        AdminActivityLog::log(
            auth('admin')->id(),
            'banned_user_from_report',
            "Banned user {$report->reportedUser->name} from report ID: {$id}",
            ['report_id' => $id, 'user_id' => $report->reported_user_id]
        );

        return back()->with('success', 'User banned successfully');
    }

    public function destroy(int $id)
    {
        $report = Report::findOrFail($id);

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_report',
            "Deleted report ID: {$id}",
            ['report_id' => $id]
        );

        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report deleted successfully');
    }
}
