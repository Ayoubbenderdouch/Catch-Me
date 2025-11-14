@extends('layouts.admin')

@section('title', 'User Conversations')
@section('page-title', $user->name . '\'s Conversations')
@section('page-description', 'View all conversations for ' . $user->name)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold">
        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2"/>
        Back to All Users
    </a>
</div>

<!-- User Info Card -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="h-20 w-20 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-bold text-3xl shadow-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-gray-500">{{ $user->email }}</p>
                <div class="mt-2 flex items-center space-x-3">
                    @if($user->gender)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $user->gender === 'male' ? 'bg-blue-100 text-blue-800' : ($user->gender === 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($user->gender) }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                        <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 mr-1"/>
                        {{ $conversationPartners->count() }} Conversations
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conversations List -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <x-heroicon-o-users class="w-6 h-6 mr-2 text-purple-600"/>
            Chat Partners
        </h3>
        <p class="text-sm text-gray-500 mt-1">Click on a conversation to view messages</p>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($conversationPartners as $partner)
            <a href="{{ route('admin.messages.show', [$user->id, $partner->id]) }}"
               class="block p-6 hover:bg-purple-50 transition-colors group">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg group-hover:scale-110 transition-transform">
                                {{ substr($partner->name, 0, 1) }}
                            </div>
                            @if($partner->last_message)
                                <div class="absolute -bottom-1 -right-1 h-6 w-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                    <x-heroicon-o-check class="w-3 h-3 text-white"/>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors">
                                {{ $partner->name }}
                            </h4>
                            @if($partner->last_message)
                                <span class="text-xs text-gray-500 flex items-center">
                                    <x-heroicon-o-clock class="w-3 h-3 mr-1"/>
                                    {{ $partner->last_message->created_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 truncate">{{ $partner->email }}</p>

                        @if($partner->last_message)
                            <p class="text-sm text-gray-600 truncate mt-2 bg-gray-100 px-3 py-2 rounded-lg inline-block">
                                <x-heroicon-o-chat-bubble-left class="w-3 h-3 inline mr-1"/>
                                {{ Str::limit($partner->last_message->message, 60) }}
                            </p>
                        @endif

                        <div class="mt-3 flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                <x-heroicon-o-envelope class="w-3 h-3 mr-1"/>
                                {{ number_format($partner->message_count) }} messages
                            </span>
                            @if($partner->gender)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $partner->gender === 'male' ? 'bg-blue-100 text-blue-800' : ($partner->gender === 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($partner->gender) }}
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
            <div class="p-12 text-center">
                <x-heroicon-o-inbox class="w-20 h-20 mx-auto text-gray-300 mb-4"/>
                <p class="text-gray-500 text-lg">No conversations found</p>
                <p class="text-gray-400 text-sm mt-2">This user hasn't chatted with anyone yet</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
