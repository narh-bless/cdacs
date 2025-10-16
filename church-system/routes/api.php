<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ContributionController;
use App\Http\Controllers\Api\MinistryController;
use App\Http\Controllers\Api\DashboardController;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('jwt.auth');
    Route::get('me', [AuthController::class, 'me'])->middleware('jwt.auth');
});

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    
    // Dashboard routes
    Route::get('dashboard/user', [DashboardController::class, 'userStats']);
    Route::get('dashboard/administrator', [DashboardController::class, 'adminStats']);
    Route::get('dashboard/pastor', [DashboardController::class, 'pastorStats']);
    Route::get('dashboard/finance', [DashboardController::class, 'financeStats']);
    
    // User management routes
    Route::get('users/{user}/contributions', [UserController::class, 'contributions']);
    Route::get('users/{user}/ministries', [UserController::class, 'ministries']);
    Route::apiResource('users', UserController::class);
    
    // Announcement routes
    Route::get('announcements/published', [AnnouncementController::class, 'published']);
    Route::post('announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);
    Route::apiResource('announcements', AnnouncementController::class);
    
    // Event routes
    Route::get('events/upcoming', [EventController::class, 'upcoming']);
    Route::get('events/published', [EventController::class, 'published']);
    Route::post('events/{event}/publish', [EventController::class, 'publish']);
    Route::apiResource('events', EventController::class);
    
    // Message routes
    Route::get('messages/inbox', [MessageController::class, 'inbox']);
    Route::get('messages/sent', [MessageController::class, 'sent']);
    Route::post('messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::post('messages/broadcast', [MessageController::class, 'broadcast']);
    Route::apiResource('messages', MessageController::class);
    
    // Contribution routes
    Route::get('contributions/reports/summary', [ContributionController::class, 'summary']);
    Route::get('contributions/reports/by-type', [ContributionController::class, 'byType']);
    Route::get('contributions/reports/by-ministry', [ContributionController::class, 'byMinistry']);
    Route::get('contributions/reports/by-date-range', [ContributionController::class, 'byDateRange']);
    Route::apiResource('contributions', ContributionController::class);
    
    // Ministry routes
    Route::get('ministries/{ministry}/members', [MinistryController::class, 'members']);
    Route::post('ministries/{ministry}/members', [MinistryController::class, 'addMember']);
    Route::delete('ministries/{ministry}/members/{user}', [MinistryController::class, 'removeMember']);
    Route::get('ministries/{ministry}/contributions', [MinistryController::class, 'contributions']);
    Route::apiResource('ministries', MinistryController::class);
});

// Role-based protected routes
Route::middleware(['jwt.auth', 'role:pastor,administrator'])->group(function () {
    Route::post('announcements', [AnnouncementController::class, 'store']);
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);
    
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);
});

Route::middleware(['jwt.auth', 'role:pastor,administrator,treasurer'])->group(function () {
    Route::post('contributions', [ContributionController::class, 'store']);
    Route::put('contributions/{contribution}', [ContributionController::class, 'update']);
    Route::delete('contributions/{contribution}', [ContributionController::class, 'destroy']);
});

Route::middleware(['jwt.auth', 'role:administrator'])->group(function () {
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::post('users/{user}/roles', [UserController::class, 'assignRole']);
    Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole']);
    
    Route::post('ministries', [MinistryController::class, 'store']);
    Route::put('ministries/{ministry}', [MinistryController::class, 'update']);
    Route::delete('ministries/{ministry}', [MinistryController::class, 'destroy']);
});
