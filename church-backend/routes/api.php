<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FinanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // User routes
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/my-contributions', [UserController::class, 'contributions']);
    Route::get('/my-donations', [UserController::class, 'donations']);

    // Announcement routes
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);

    // Event routes
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::post('/events/{event}/register', [EventController::class, 'register']);
    Route::delete('/events/{event}/unregister', [EventController::class, 'unregister']);

    // Message routes
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages/{message}/unread', [MessageController::class, 'markAsUnread']);
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply']);

    // Message group routes
    Route::get('/message-groups', [MessageController::class, 'groups']);
    Route::post('/message-groups', [MessageController::class, 'createGroup']);
    Route::post('/message-groups/{group}/send', [MessageController::class, 'sendToGroup']);

    // Finance routes (user's own data)
    Route::get('/my-financial-history', [FinanceController::class, 'userHistory']);

    // Pastor routes
    Route::middleware('role:pastor')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);
        Route::post('/announcements/{announcement}/unpublish', [AnnouncementController::class, 'unpublish']);

        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/attendance', [EventController::class, 'markAttendance']);
    });

    // Finance Committee routes
    Route::middleware('role:finance_committee')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);
        Route::post('/announcements/{announcement}/unpublish', [AnnouncementController::class, 'unpublish']);

        Route::get('/contributions', [FinanceController::class, 'contributions']);
        Route::post('/contributions', [FinanceController::class, 'storeContribution']);
        Route::put('/contributions/{contribution}/status', [FinanceController::class, 'updateContributionStatus']);

        Route::get('/donations', [FinanceController::class, 'donations']);
        Route::post('/donations', [FinanceController::class, 'storeDonation']);
        Route::put('/donations/{donation}/status', [FinanceController::class, 'updateDonationStatus']);

        Route::get('/financial-summary', [FinanceController::class, 'summary']);
    });

    // Administrator routes
    Route::middleware('role:administrator')->group(function () {
        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/users/{user}/contributions', [UserController::class, 'contributions']);
        Route::get('/users/{user}/donations', [UserController::class, 'donations']);

        // Announcement management
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);
        Route::post('/announcements/{announcement}/unpublish', [AnnouncementController::class, 'unpublish']);

        // Event management
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/attendance', [EventController::class, 'markAttendance']);

        // Finance management
        Route::get('/contributions', [FinanceController::class, 'contributions']);
        Route::post('/contributions', [FinanceController::class, 'storeContribution']);
        Route::put('/contributions/{contribution}/status', [FinanceController::class, 'updateContributionStatus']);

        Route::get('/donations', [FinanceController::class, 'donations']);
        Route::post('/donations', [FinanceController::class, 'storeDonation']);
        Route::put('/donations/{donation}/status', [FinanceController::class, 'updateDonationStatus']);

        Route::get('/financial-summary', [FinanceController::class, 'summary']);
    });
});
