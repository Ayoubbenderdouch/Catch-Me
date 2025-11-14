@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                    <select id="gender" name="gender" required
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea id="bio" name="bio" rows="4"
                              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Privacy -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Account Privacy</label>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" id="account_public" name="account_type" value="public"
                                       {{ old('account_type', $user->account_type ?? 'public') === 'public' ? 'checked' : '' }}
                                       class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300">
                            </div>
                            <div class="ml-3">
                                <label for="account_public" class="text-sm font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Public
                                </label>
                                <p class="text-sm text-gray-500">Everyone can see this profile</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" id="account_private" name="account_type" value="private"
                                       {{ old('account_type', $user->account_type ?? 'public') === 'private' ? 'checked' : '' }}
                                       class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300">
                            </div>
                            <div class="ml-3">
                                <label for="account_private" class="text-sm font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Private
                                </label>
                                <p class="text-sm text-gray-500">Hidden from discovery</p>
                            </div>
                        </div>
                    </div>
                    @error('account_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Visibility (Ghost Mode) -->
                <div class="flex items-center">
                    <input type="checkbox" id="is_visible" name="is_visible" value="1" {{ old('is_visible', $user->is_visible) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_visible" class="ml-2 block text-sm text-gray-900">
                        User is visible (not in ghost mode)
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
