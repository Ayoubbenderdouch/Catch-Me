<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->paginate(20);
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,report_moderator,chat_moderator,user_moderator',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        AdminActivityLog::log(
            auth('admin')->id(),
            'created_admin',
            "Created new admin: {$admin->name}",
            ['admin_id' => $admin->id, 'role' => $admin->role]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully');
    }

    public function edit(int $id)
    {
        $admin = Admin::findOrFail($id);
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, int $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'role' => 'required|in:super_admin,report_moderator,chat_moderator,user_moderator',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $admin->update($validated);

        AdminActivityLog::log(
            auth('admin')->id(),
            'updated_admin',
            "Updated admin: {$admin->name}",
            ['admin_id' => $admin->id]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully');
    }

    public function destroy(int $id)
    {
        $admin = Admin::findOrFail($id);

        // Can't delete yourself
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete yourself');
        }

        $adminName = $admin->name;
        $admin->delete();

        AdminActivityLog::log(
            auth('admin')->id(),
            'deleted_admin',
            "Deleted admin: {$adminName}",
            ['admin_id' => $id]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully');
    }
}
