<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes with rate limiting (prevent brute force)
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social', [AuthController::class, 'socialAuth']);
});

// Protected routes (require authentication + rate limiting)
// Default: 60 requests per minute per user
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });

    // User profile
    Route::prefix('user')->group(function () {
        // Profile updates: Max 10 per minute to prevent abuse
        Route::put('profile', [UserController::class, 'updateProfile'])
            ->middleware('throttle:10,1');

        // Image uploads: Max 5 per minute (resource intensive)
        Route::post('profile-image', [UserController::class, 'uploadProfileImage'])
            ->middleware('throttle:5,1');
        Route::post('main-photo', [UserController::class, 'updateMainPhoto'])
            ->middleware('throttle:5,1');
        Route::delete('photos/{index}', [UserController::class, 'deletePhoto'])
            ->middleware('throttle:10,1');

        // Location updates: Max 6 per minute (every 10 seconds)
        // This prevents database overload from too frequent updates
        Route::post('location', [UserController::class, 'updateLocation'])
            ->middleware('throttle:6,1');

        Route::get('nearby', [UserController::class, 'nearbyUsers']);
        Route::post('ghost-mode', [UserController::class, 'toggleGhostMode']);
        Route::delete('account', [UserController::class, 'deleteAccount']);
    });

    // Likes and matches
    // Max 30 likes per minute to prevent spam
    Route::prefix('likes')->middleware('throttle:30,1')->group(function () {
        Route::post('/', [LikeController::class, 'sendLike']);
        Route::put('{id}/accept', [LikeController::class, 'acceptLike']);
        Route::put('{id}/reject', [LikeController::class, 'rejectLike']);
        Route::get('received', [LikeController::class, 'receivedLikes']);
    });

    Route::get('matches', [LikeController::class, 'matches']);

    // Messages - 60 per minute (1 per second)
    Route::prefix('messages')->middleware('throttle:60,1')->group(function () {
        Route::post('/', [MessageController::class, 'sendMessage']);
        Route::get('{userId}', [MessageController::class, 'getConversation']);
        Route::delete('{id}', [MessageController::class, 'deleteMessage']);

        // WhatsApp-style message status
        Route::post('{id}/delivered', [MessageController::class, 'markAsDelivered']);
        Route::post('{id}/read', [MessageController::class, 'markAsRead']);

        // Bulk status updates (more efficient for multiple messages)
        Route::post('bulk-delivered', [MessageController::class, 'bulkMarkAsDelivered']);
        Route::post('bulk-read', [MessageController::class, 'bulkMarkAsRead']);
    });

    Route::get('conversations', [MessageController::class, 'getConversations']);

    // Reports - Lower limit to prevent abuse
    Route::prefix('reports')->middleware('throttle:10,60')->group(function () {
        Route::post('/', [ReportController::class, 'reportUser']);
        Route::get('/', [ReportController::class, 'myReports']);
    });

    // Block/Unblock Users (App Store Compliance!)
    Route::prefix('block')->group(function () {
        Route::post('/', [BlockController::class, 'blockUser']);
        Route::delete('{userId}', [BlockController::class, 'unblockUser']);
        Route::get('blocked-users', [BlockController::class, 'getBlockedUsers']);
    });
});
