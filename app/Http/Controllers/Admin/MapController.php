<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;

class MapController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        $onlineUsers = $this->locationService->getOnlineUsersWithLocation(30);

        return view('admin.map.index', [
            'users' => $onlineUsers,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
        ]);
    }

    public function getUsersData()
    {
        $onlineUsers = $this->locationService->getOnlineUsersWithLocation(30);

        return response()->json([
            'users' => $onlineUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'gender' => $user->gender,
                    'latitude' => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                    'last_active_at' => $user->last_active_at->diffForHumans(),
                    'profile_image' => $user->profile_image,
                ];
            }),
        ]);
    }
}
