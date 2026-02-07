<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Social authentication
    Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Sanctum Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/user', [AuthController::class, 'user']);
        
        // Social account management
        Route::post('/google/link', [AuthController::class, 'linkGoogleAccount']);
        Route::post('/google/unlink', [AuthController::class, 'unlinkGoogleAccount']);
        
    });

    // User routes
    Route::prefix('users')->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/me', [UserController::class, 'update']);
        Route::post('/me/avatar', [UserController::class, 'updateAvatar']);
        Route::delete('/me/avatar', [UserController::class, 'deleteAvatar']);
        Route::post('/me/last-seen', [UserController::class, 'updateLastSeen']);
        
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('/search', [UserController::class, 'search']);
    });

    // Chat routes
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{chat}', [ChatController::class, 'show']);
        Route::put('/{chat}', [ChatController::class, 'update']);
        Route::delete('/{chat}', [ChatController::class, 'destroy']);
        
        // Chat avatar
        Route::post('/{chat}/avatar', [ChatController::class, 'updateAvatar']);
        Route::delete('/{chat}/avatar', [ChatController::class, 'deleteAvatar']);
        
        // Chat participants
        Route::get('/{chat}/participants', [ChatController::class, 'participants']);
        Route::post('/{chat}/participants', [ChatController::class, 'addParticipant']);
        Route::delete('/{chat}/participants/{user}', [ChatController::class, 'removeParticipant']);
        Route::put('/{chat}/participants/{user}/role', [ChatController::class, 'updateParticipantRole']);
        
        // Mark messages as read
        Route::post('/{chat}/read', [ChatController::class, 'markAsRead']);

        // Messages in chat
        Route::prefix('{chat}/messages')->group(function () {
            Route::get('/', [MessageController::class, 'index']);
            Route::post('/', [MessageController::class, 'store']);
            Route::get('/{message}', [MessageController::class, 'show']);
            Route::put('/{message}', [MessageController::class, 'update']);
            Route::delete('/{message}', [MessageController::class, 'destroy']);
            
            // Mark message as read
            Route::post('/{message}/read', [MessageController::class, 'markAsRead']);
            
            // Typing indicator
            Route::post('/typing', [MessageController::class, 'typing']);
        });
    });
});