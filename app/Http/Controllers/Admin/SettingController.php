<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'max_distance' => 'required|integer|min:10|max:1000',
            'ghost_mode_enabled' => 'required|boolean',
            'terms_content' => 'required|string',
            'privacy_content' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value);
        }

        AdminActivityLog::log(
            auth('admin')->id(),
            'updated_settings',
            'Updated app settings',
            $validated
        );

        return back()->with('success', 'Settings updated successfully');
    }
}
