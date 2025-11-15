<?php

use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LikeController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Public legal pages (for app compliance)
Route::prefix('legal')->group(function () {
    Route::get('/', function () {
        return view('legal.index');
    })->name('legal.index');

    Route::get('/privacy', function () {
        return view('legal.privacy');
    })->name('legal.privacy');

    Route::get('/terms', function () {
        return view('legal.terms');
    })->name('legal.terms');
});

// Admin authentication routes (Laravel Breeze)
require __DIR__ . '/auth.php';

// Admin root redirect - redirect /admin to dashboard if logged in, or to login if not
Route::get('/admin', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
})->name('admin');

// Admin protected routes
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users Management
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::post('users/{id}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::post('users/{id}/unban', [UserController::class, 'unban'])->name('users.unban');

    // Likes & Matches
    Route::get('likes', [LikeController::class, 'index'])->name('likes.index');
    Route::delete('likes/{id}', [LikeController::class, 'destroy'])->name('likes.destroy');
    Route::post('likes/reset-matches', [LikeController::class, 'resetMatches'])->name('likes.reset');

    // Messages & Chats
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/user/{userId}', [MessageController::class, 'userConversations'])->name('messages.user');
    Route::get('messages/{userId}/{partnerId}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::delete('messages/conversation/delete', [MessageController::class, 'destroyConversation'])->name('messages.conversation.destroy');

    // Reports
    Route::resource('reports', ReportController::class)->only(['index', 'show', 'destroy']);
    Route::post('reports/{id}/review', [ReportController::class, 'markAsReviewed'])->name('reports.review');
    Route::post('reports/{id}/ban-user', [ReportController::class, 'banReportedUser'])->name('reports.ban-user');

    // Security
    Route::get('security', [SecurityController::class, 'index'])->name('security.index');
    Route::get('security/logs', [SecurityController::class, 'activityLogs'])->name('security.logs');

    // Live Map
    Route::get('map', [MapController::class, 'index'])->name('map.index');
    Route::get('map/users-data', [MapController::class, 'getUsersData'])->name('map.users-data');

    // Push Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');

    // App Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Admin Users Management
    Route::resource('admins', AdminManagementController::class);
});
