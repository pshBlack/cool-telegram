<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckTokenExpiration;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', CheckTokenExpiration::class])->group(function () {
    // auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->makeHidden(['password_hash'])
        ]);
    });

    // chats
    Route::post('/chats', [ChatController::class, 'createChat']);
    Route::get('/chats', [ChatController::class, 'getUserChats']);

    // messages
    Route::post('/chats/{chatId}/messages', [MessageController::class, 'sendMessage']);
    Route::get('/chats/{chatId}/messages', [MessageController::class, 'getMessages']);
    Route::post('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
});