@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details: ' . $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Profile -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                @if($user->profile_image)
                    <img src="{{ Storage::disk('s3')->url($user->profile_image) }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full mx-auto mb-4">
                @else
                    <div class="w-32 h-32 rounded-full bg-gray-300 mx-auto mb-4 flex items-center justify-center">
                        <span class="text-4xl text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif

                <h3 class="text-xl font-bold mb-2">{{ $user->name }}</h3>

                <div class="space-y-2 text-sm">
                    <p><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $user->phone }}</p>
                    <p><strong>Gender:</strong> {{ ucfirst($user->gender) }}</p>
                    <p><strong>Language:</strong> {{ strtoupper($user->language) }}</p>
                </div>

                <div class="mt-4 space-y-2">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_banned ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->is_banned ? 'Banned' : 'Active' }}
                    </span>
                    <br>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $user->is_visible ? 'Visible' : 'Ghost Mode' }}
                    </span>
                </div>

                @if($user->bio)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600">{{ $user->bio }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-6 pt-6 border-t space-y-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Edit User
                </a>

                @if($user->is_banned)
                    <form action="{{ route('admin.users.unban', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Unban User
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        <button type="submit" class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                            Ban User
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="block w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Likes Sent</p>
                <p class="text-2xl font-bold">{{ $user->likesSent->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Likes Received</p>
                <p class="text-2xl font-bold">{{ $user->likesReceived->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Messages Sent</p>
                <p class="text-2xl font-bold">{{ $user->messagesSent->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Reports Made</p>
                <p class="text-2xl font-bold">{{ $user->reportsMade->count() }}</p>
            </div>
        </div>

        <!-- Location -->
        @if($user->latitude && $user->longitude)
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Last Known Location</h4>
            <p><strong>Latitude:</strong> {{ $user->latitude }}</p>
            <p><strong>Longitude:</strong> {{ $user->longitude }}</p>
            <p><strong>Last Active:</strong> {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</p>
        </div>
        @endif

        <!-- Recent Likes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Recent Likes Received</h4>
            <div class="space-y-2">
                @forelse($user->likesReceived->take(5) as $like)
                    <div class="flex items-center justify-between p-2 border rounded">
                        <div>
                            <p class="font-medium">{{ $like->fromUser->name }}</p>
                            <p class="text-sm text-gray-500">{{ $like->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded {{ $like->status === 'accepted' ? 'bg-green-100 text-green-800' : ($like->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($like->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">No likes received yet</p>
                @endforelse
            </div>
        </div>

        <!-- Reports Against User -->
        @if($user->reportsAgainst->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4 text-red-600">Reports Against This User</h4>
            <div class="space-y-2">
                @foreach($user->reportsAgainst as $report)
                    <div class="p-3 border border-red-200 rounded bg-red-50">
                        <p class="text-sm"><strong>Reporter:</strong> {{ $report->reporter->name }}</p>
                        <p class="text-sm"><strong>Reason:</strong> {{ $report->reason }}</p>
                        <p class="text-sm text-gray-500">{{ $report->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
