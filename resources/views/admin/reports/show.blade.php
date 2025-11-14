@extends('layouts.admin')

@section('title', 'Report Details')
@section('page-title', 'Report Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold mb-4">Reporter Information</h3>
                <p><strong>Name:</strong> {{ $report->reporter->name }}</p>
                <p><strong>Email:</strong> {{ $report->reporter->email }}</p>
                <p><strong>Phone:</strong> {{ $report->reporter->phone }}</p>
                <a href="{{ route('admin.users.show', $report->reporter_id) }}" class="text-blue-600 hover:text-blue-900">View Profile</a>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Reported User</h3>
                <p><strong>Name:</strong> {{ $report->reportedUser->name }}</p>
                <p><strong>Email:</strong> {{ $report->reportedUser->email }}</p>
                <p><strong>Phone:</strong> {{ $report->reportedUser->phone }}</p>
                <a href="{{ route('admin.users.show', $report->reported_user_id) }}" class="text-blue-600 hover:text-blue-900">View Profile</a>
            </div>
        </div>

        <div class="border-t pt-6 mb-6">
            <h3 class="text-lg font-semibold mb-2">Report Details</h3>
            <p><strong>Status:</strong>
                <span class="px-2 py-1 text-xs rounded {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($report->status === 'actioned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ ucfirst($report->status) }}
                </span>
            </p>
            <p class="mt-2"><strong>Date:</strong> {{ $report->created_at->format('Y-m-d H:i') }}</p>
            <p class="mt-2"><strong>Reason:</strong></p>
            <div class="mt-2 p-4 bg-gray-50 rounded">{{ $report->reason }}</div>
        </div>

        @if($report->status !== 'pending')
        <div class="border-t pt-6 mb-6">
            <h3 class="text-lg font-semibold mb-2">Admin Review</h3>
            <p><strong>Reviewed By:</strong> {{ $report->reviewer->name ?? 'N/A' }}</p>
            <p><strong>Reviewed At:</strong> {{ $report->reviewed_at ? $report->reviewed_at->format('Y-m-d H:i') : 'N/A' }}</p>
            @if($report->admin_notes)
            <p class="mt-2"><strong>Admin Notes:</strong></p>
            <div class="mt-2 p-4 bg-gray-50 rounded">{{ $report->admin_notes }}</div>
            @endif
        </div>
        @endif

        @if($report->status === 'pending')
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold mb-4">Actions</h3>

            <form action="{{ route('admin.reports.review', $report->id) }}" method="POST" class="mb-4">
                @csrf
                <div class="mb-4">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Mark as Reviewed
                </button>
            </form>

            <form action="{{ route('admin.reports.ban-user', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to ban this user?')">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Ban Reported User
                </button>
            </form>
        </div>
        @endif

        <div class="border-t pt-6 mt-6">
            <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-900">← Back to Reports</a>
        </div>
    </div>
</div>
@endsection
