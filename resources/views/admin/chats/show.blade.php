@extends('layouts.admin')

@section('title', 'Conversation')
@section('page-title', 'Conversation Details')
@section('page-description', $user->name . ' & ' . $partner->name)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('admin.messages.user', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold">
        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2"/>
        Back to {{ $user->name }}'s Conversations
    </a>
</div>

<!-- User Info Header -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <!-- User 1 -->
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Arrow -->
            <div class="text-gray-400">
                <x-heroicon-o-arrows-right-left class="w-8 h-8"/>
            </div>

            <!-- User 2 -->
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                    {{ substr($partner->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $partner->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $partner->email }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-purple-100 text-purple-800">
                <x-heroicon-o-envelope class="w-4 h-4 mr-2"/>
                {{ $messages->count() }} Messages
            </span>
            <form action="{{ route('admin.messages.conversation.destroy') }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="user1" value="{{ $user->id }}">
                <input type="hidden" name="user2" value="{{ $partner->id }}">
                <button type="submit"
                        onclick="return confirm('Are you sure you want to delete this entire conversation?')"
                        class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors font-semibold">
                    <x-heroicon-o-trash class="w-4 h-4 mr-2"/>
                    Delete Conversation
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Chat Messages -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 mr-2 text-purple-600"/>
            Conversation Timeline
        </h3>
        <p class="text-sm text-gray-500 mt-1">All messages in chronological order</p>
    </div>

    <div class="p-6 space-y-4 max-h-[600px] overflow-y-auto bg-gray-50">
        @forelse($messages as $message)
            @php
                $isUser = $message->sender_id == $user->id;
            @endphp

            <div class="flex items-start space-x-3 {{ $isUser ? '' : 'flex-row-reverse space-x-reverse' }}">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full {{ $isUser ? 'bg-gradient-to-br from-purple-400 to-pink-500' : 'bg-gradient-to-br from-blue-400 to-indigo-500' }} flex items-center justify-center text-white font-semibold shadow-lg">
                        {{ substr($message->sender->name ?? 'U', 0, 1) }}
                    </div>
                </div>

                <div class="flex-1 max-w-2xl">
                    <div class="flex items-center space-x-2 mb-1 {{ $isUser ? '' : 'flex-row-reverse space-x-reverse' }}">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $message->sender->name ?? 'Unknown User' }}
                        </p>
                        <span class="text-xs text-gray-500 flex items-center">
                            <x-heroicon-o-clock class="w-3 h-3 mr-1"/>
                            {{ $message->created_at->format('M d, Y H:i') }}
                        </span>
                    </div>

                    <div class="{{ $isUser ? 'bg-gradient-to-br from-purple-500 to-purple-600 text-white' : 'bg-white text-gray-900 border border-gray-200' }} rounded-2xl px-4 py-3 inline-block shadow-sm">
                        <p class="text-sm">{{ $message->message }}</p>
                    </div>

                    <div class="mt-2">
                        <form action="{{ route('admin.messages.destroy', $message->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Delete this message?')"
                                    class="inline-flex items-center px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                <x-heroicon-o-trash class="w-3 h-3 mr-1"/>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <x-heroicon-o-chat-bubble-left-right class="w-20 h-20 mx-auto text-gray-300 mb-4"/>
                <p class="text-gray-500 text-lg">No messages in this conversation</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
