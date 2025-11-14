@extends('layouts.admin')

@section('title', 'Push Notifications')
@section('page-title', 'Send Push Notifications')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.notifications.send') }}">
            @csrf

            <div class="space-y-6">
                <!-- Target -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="target_all" name="target" value="all" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   onchange="document.getElementById('user_id_field').style.display='none'">
                            <label for="target_all" class="ml-2 block text-sm text-gray-900">
                                All Users
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="target_single" name="target" value="single"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   onchange="document.getElementById('user_id_field').style.display='block'">
                            <label for="target_single" class="ml-2 block text-sm text-gray-900">
                                Single User
                            </label>
                        </div>
                    </div>
                </div>

                <!-- User ID (hidden by default) -->
                <div id="user_id_field" style="display: none;">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User ID</label>
                    <input type="number" id="user_id" name="user_id"
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" required maxlength="100"
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Body -->
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="body" name="body" rows="4" required maxlength="500"
                              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                </div>

                <!-- Preview -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Preview</h3>
                    <div class="max-w-sm bg-white rounded-lg shadow-lg p-4 border border-gray-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900" id="preview-title">Notification Title</p>
                                <p class="mt-1 text-sm text-gray-500" id="preview-body">Notification message will appear here...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-4 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                        Send Notification
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('title').addEventListener('input', function() {
        document.getElementById('preview-title').textContent = this.value || 'Notification Title';
    });

    document.getElementById('body').addEventListener('input', function() {
        document.getElementById('preview-body').textContent = this.value || 'Notification message will appear here...';
    });
</script>
@endpush
@endsection
