@extends('layouts.admin')

@section('title', 'Chat Management')
@section('page-title', 'Chat Management')
@section('page-description', 'Select a user to view their conversations')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-users class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Total
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Total Users</p>
            <p class="text-4xl font-bold">{{ number_format($stats['total_users']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-user-group class="w-4 h-4 mr-1"/>
                <span class="text-white/90">With messages</span>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-chat-bubble-left-right class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Active
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Total Messages</p>
            <p class="text-4xl font-bold">{{ number_format($stats['total_messages']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-envelope class="w-4 h-4 mr-1"/>
                <span class="text-white/90">All messages</span>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-arrows-right-left class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Chats
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Conversations</p>
            <p class="text-4xl font-bold">{{ number_format($stats['total_conversations']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-users class="w-4 h-4 mr-1"/>
                <span class="text-white/90">Unique pairs</span>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-clock class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Today
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Messages Today</p>
            <p class="text-4xl font-bold">{{ number_format($stats['messages_today']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-calendar class="w-4 h-4 mr-1"/>
                <span class="text-white/90">Last 24 hours</span>
            </div>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <x-heroicon-o-user-group class="w-6 h-6 mr-2 text-purple-600"/>
                    Users with Messages
                </h3>
                <p class="text-sm text-gray-500 mt-1">Click on a user to view their conversations</p>
            </div>
        </div>

        <!-- Search Filter -->
        <form method="GET" class="mt-4">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="w-5 h-5 absolute left-3 top-3 text-gray-400"/>
                <input type="text" name="search" placeholder="Search users by name or email..." value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50">
            </div>
        </form>
    </div>

    <!-- Users Grid -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($users as $user)
                <a href="{{ route('admin.messages.user', $user->id) }}"
                   class="block p-6 border border-gray-200 rounded-xl hover:border-purple-500 hover:shadow-lg transition-all duration-200 group bg-gradient-to-br from-white to-gray-50">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg group-hover:scale-110 transition-transform">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg font-bold text-gray-900 truncate group-hover:text-purple-600 transition-colors">
                                {{ $user->name }}
                            </h4>
                            <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                            <div class="mt-2 flex items-center space-x-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    <x-heroicon-o-envelope class="w-3 h-3 mr-1"/>
                                    {{ number_format($user->total_messages) }} messages
                                </span>
                                @if($user->gender)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $user->gender === 'male' ? 'bg-blue-100 text-blue-800' : ($user->gender === 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($user->gender) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <x-heroicon-o-chevron-right class="w-6 h-6 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all"/>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full p-12 text-center">
                    <x-heroicon-o-inbox class="w-20 h-20 mx-auto text-gray-300 mb-4"/>
                    <p class="text-gray-500 text-lg">No users with messages found</p>
                    <p class="text-gray-400 text-sm mt-2">Messages will appear here when users start chatting</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
