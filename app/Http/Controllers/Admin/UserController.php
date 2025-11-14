<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Status filter
        if ($request->filled('is_banned')) {
            $query->where('is_banned', $request->is_banned);
        }

        // Visibility filter
        if ($request->filled('is_visible')) {
            $query->where('is_visible', $request->is_visible);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(int $id)
    {
        $user = User::with(['likesSent', 'likesReceived', 'reportsMade', 'reportsAgainst'])
            ->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
            'gender' => 'required|in:male,female,other',
            'bio' => 'nullable|string|max:500',
            'account_type' => 'required|in:public,private',
            'is_visible' => 'boolean',
        ]);

        $user->update($validated);

        AdminActivityLog::log(
            auth('admin')->id(),
            'updated_user',
            "Updated user: {$user->name}",
            ['user_id' => $user->id]
        );

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'User updated successfully');
    }

    public function ban(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => true]);

        // Delete all active tokens
        $user->tokens()->delete();

        AdminActivityLog::log(
            auth('admin')->id(),
            'banned_user',
            "Banned user: {$user->name}",
            ['user_id' => $user->id]
        );

        return back()->with('success', 'User banned successfully');
    }

    public function unban(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => false]);

        AdminActivityLog::log(
            auth('admin')->id(),
            'unbanned_user',
            "Unbanned user: {$user->name}",
            ['user_id' => $user->id]
        );

        return back()->with('success', 'User unbanned successfully');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $userName = $user->name;

        // Delete profile image - use public_direct disk explicitly
        if ($user->profile_image) {
            try {
                Storage::disk('public_direct')->delete($user->profile_image);
            } catch (\Exception $e) {
                // Ignore if file doesn't exist
            }
        }

        $user->delete();

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_user',
            "Deleted user: {$userName}",
            ['user_id' => $id]
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }
}
