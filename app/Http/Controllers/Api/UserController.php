<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocationService;
use App\Services\LocationCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected LocationService $locationService;
    protected LocationCacheService $locationCacheService;

    public function __construct(LocationService $locationService, LocationCacheService $locationCacheService)
    {
        $this->locationService = $locationService;
        $this->locationCacheService = $locationCacheService;
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile",
     *     summary="Update user profile",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="bio", type="string"),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="language", type="string"),
     *             @OA\Property(property="is_visible", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated")
     * )
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|regex:/^[a-zA-Z0-9\s\-\_]+$/u',
            'bio' => 'sometimes|string|max:500',
            'gender' => 'sometimes|in:male,female,other',
            'language' => 'sometimes|in:fr,ar',
            'account_type' => 'sometimes|in:public,private', // Account privacy
            'is_visible' => 'sometimes|boolean', // Ghost mode
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Sanitize input to prevent XSS attacks
        $data = $request->only(['name', 'bio', 'gender', 'language', 'account_type', 'is_visible']);
        if (isset($data['name'])) {
            $data['name'] = strip_tags($data['name']);
        }
        if (isset($data['bio'])) {
            $data['bio'] = strip_tags($data['bio']);
        }

        $user->update($data);

        return response()->json([
            'message' => __('messages.profile_updated'),
            'user' => $user->fresh(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/profile-image",
     *     summary="Upload profile image (adds to photos array, max 9)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Image uploaded")
     * )
     */
    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120|dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Get current photos array (or empty array if null)
        $photos = $user->photos ?? [];

        // Check if user already has 9 photos (maximum)
        if (count($photos) >= 9) {
            return response()->json([
                'message' => __('Maximum 9 photos allowed'),
            ], 400);
        }

        // Additional security: Verify it's actually an image
        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($mimeType, $allowedMimes)) {
            return response()->json([
                'message' => __('Invalid file type. Only JPEG and PNG are allowed.'),
            ], 400);
        }

        // Use 'public_direct' disk for Laravel Cloud compatibility
        // This stores images directly in public/profile-images/ (no symlink needed)
        $disk = 'public_direct';

        // Upload new image with secure filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile-images', $filename, $disk);
        $imageUrl = Storage::disk($disk)->url($path);

        // Add to photos array
        $photos[] = $imageUrl;

        // Update user with new photos array
        $user->update(['photos' => $photos]);

        return response()->json([
            'message' => __('messages.image_uploaded'),
            'image_url' => $imageUrl,
            'photos' => $photos,
            'total_photos' => count($photos),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/main-photo",
     *     summary="Update main profile photo (ONLY profile_image field, NOT photos array)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile photo updated")
     * )
     */
    public function updateMainPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120|dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Additional security: Verify it's actually an image
        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($mimeType, $allowedMimes)) {
            return response()->json([
                'message' => __('Invalid file type. Only JPEG and PNG are allowed.'),
            ], 400);
        }

        // Use 'public_direct' disk for Laravel Cloud compatibility
        $disk = 'public_direct';

        // Delete old profile image from storage if exists
        if ($user->profile_image) {
            // Security: Ensure the path doesn't contain directory traversal
            $safePath = str_replace(['../', '..\\'], '', $user->profile_image);
            if (Storage::disk($disk)->exists($safePath)) {
                Storage::disk($disk)->delete($safePath);
            }
        }

        // Upload new profile image with secure filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile-images', $filename, $disk);
        $imageUrl = Storage::disk($disk)->url($path);

        // Update ONLY profile_image field (NOT photos array!)
        $user->update([
            'profile_image' => $path,
        ]);

        return response()->json([
            'message' => __('Profile photo updated successfully'),
            'image_url' => $imageUrl,
            'profile_image' => $imageUrl,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/photos/{index}",
     *     summary="Delete photo by index (0-8)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="index",
     *         in="path",
     *         description="Photo index (0 = first photo/main photo)",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Photo deleted")
     * )
     */
    public function deletePhoto(Request $request, $index)
    {
        $user = $request->user();

        // Get current photos array
        $photos = $user->photos ?? [];

        // Validate index
        if (!is_numeric($index) || $index < 0 || $index >= count($photos)) {
            return response()->json([
                'message' => __('Invalid photo index'),
            ], 400);
        }

        $index = (int) $index;

        // Get photo URL to delete from storage
        $photoUrl = $photos[$index];

        // Extract path from URL for storage deletion
        // Use 'public_direct' disk for Laravel Cloud compatibility
        $disk = 'public_direct';
        $urlPath = parse_url($photoUrl, PHP_URL_PATH);
        $storagePath = ltrim($urlPath, '/'); // Remove leading slash for public_direct disk

        // Security: Prevent directory traversal attacks
        $storagePath = str_replace(['../', '..\\'], '', $storagePath);

        // Security: Ensure the path is within the profile-images directory
        if (!str_contains($storagePath, 'profile-images')) {
            return response()->json([
                'message' => __('Invalid photo path'),
            ], 400);
        }

        // Delete from storage
        if (Storage::disk($disk)->exists($storagePath)) {
            Storage::disk($disk)->delete($storagePath);
        }

        // Remove from array
        array_splice($photos, $index, 1);

        // Update user photos
        $user->update(['photos' => $photos]);

        // Update profile_image to first photo if deleted photo was the main one
        if ($index === 0 && count($photos) > 0) {
            $firstPhotoUrl = $photos[0];
            $firstPhotoPath = parse_url($firstPhotoUrl, PHP_URL_PATH);
            $firstPhotoPath = str_replace('/storage/', '', $firstPhotoPath);
            $user->update(['profile_image' => $firstPhotoPath]);
        } elseif (count($photos) === 0) {
            // No photos left, clear profile_image
            $user->update(['profile_image' => null]);
        }

        return response()->json([
            'message' => __('Photo deleted successfully'),
            'photos' => $photos,
            'total_photos' => count($photos),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/location",
     *     summary="Update user location",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             @OA\Property(property="latitude", type="number", format="float"),
     *             @OA\Property(property="longitude", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location updated")
     * )
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Use LocationCacheService for SUPER FAST Redis caching!
        // This is 100x faster than direct database writes
        $updated = $this->locationCacheService->updateLocation(
            $user->id,
            $request->latitude,
            $request->longitude
        );

        if (!$updated) {
            return response()->json([
                'message' => __('messages.location_not_updated'),
            ], 400);
        }

        return response()->json([
            'message' => __('messages.location_updated'),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/nearby",
     *     summary="Get nearby users",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Search radius in meters (default: 50)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Nearby users retrieved")
     * )
     */
    public function nearbyUsers(Request $request)
    {
        $user = $request->user();

        // Get user location from Redis cache first (faster!)
        $userLocation = $this->locationCacheService->getLocation($user->id);

        if (!$userLocation) {
            return response()->json([
                'message' => __('messages.location_required'),
            ], 400);
        }

        $radius = $request->input('radius', config('app.default_max_distance', 50));

        // Use LocationCacheService for cached nearby users (30-second cache)
        $nearbyUsers = $this->locationCacheService->getNearbyUsers(
            (float) $userLocation['latitude'],
            (float) $userLocation['longitude'],
            (int) $radius,
            $user->id
        );

        $disk = config('filesystems.default');

        return response()->json([
            'users' => $nearbyUsers->map(function ($nearbyUser) use ($disk) {
                return [
                    'id' => $nearbyUser->id,
                    'name' => $nearbyUser->name,
                    'gender' => $nearbyUser->gender,
                    'profile_image' => $nearbyUser->profile_image ? Storage::disk($disk)->url($nearbyUser->profile_image) : null,
                    'photos' => $nearbyUser->photos ?? [],
                    'bio' => $nearbyUser->bio,
                    'distance' => $nearbyUser->distance,
                ];
            }),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/account",
     *     summary="Delete user account",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Account deleted")
     * )
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Delete profile image
        if ($user->profile_image) {
            Storage::disk(config('filesystems.default'))->delete($user->profile_image);
        }

        // Delete all tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'message' => __('messages.account_deleted'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/ghost-mode",
     *     summary="Toggle Ghost Mode (Hide/Show from nearby users)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="is_visible", type="boolean", description="true = visible, false = ghost mode")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ghost mode toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="is_visible", type="boolean"),
     *             @OA\Property(property="status", type="string", example="Ghost Mode ON")
     *         )
     *     )
     * )
     */
    public function toggleGhostMode(Request $request)
    {
        $user = $request->user();

        // If is_visible is provided, use it. Otherwise toggle current state
        if ($request->has('is_visible')) {
            $isVisible = $request->boolean('is_visible');
        } else {
            // Toggle: if visible, make invisible. If invisible, make visible
            $isVisible = !$user->is_visible;
        }

        $user->is_visible = $isVisible;
        $user->save();

        $status = $isVisible ? 'Visible Mode ON' : 'Ghost Mode ON';

        return response()->json([
            'message' => $isVisible
                ? __('messages.now_visible')
                : __('messages.now_hidden'),
            'is_visible' => $isVisible,
            'status' => $status,
        ]);
    }
}
