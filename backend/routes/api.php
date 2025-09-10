<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AllUsersChatController;
use App\Http\Controllers\GroupChatController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Гостьові роути (login/register) - тут Sanctum автоматично додає CSRF middleware
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Захищені роути
Route::middleware([EnsureFrontendRequestsAreStateful::class,'auth:sanctum'] )->group(function () {
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
    Route::post('/chats/all-users', [AllUsersChatController::class, 'create']);
    Route::post('/group-chats', [GroupChatController::class, 'createGroupChat']);
    Route::get('/group-chats/{chatId}/messages', [GroupChatController::class, 'getGroupChatMessages']);
    Route::delete('/chats/{chatId}', [ChatController::class, 'deleteChat']);
    Route::delete('/group-chats/{chatId}', [GroupChatController::class, 'deleteGroupChat']);
    
    // messages
    Route::post('/chats/{chatId}/messages', [MessageController::class, 'sendMessage']);
    Route::get('/chats/{chatId}/messages', [MessageController::class, 'getMessages']);
    Route::post('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage']);
   
    Route::get('/users/search/{username}', [UserController::class, 'search']);
    
    // Broadcast routes
    Broadcast::routes(['middleware' => ['auth:sanctum',EnsureFrontendRequestsAreStateful::class]]);
    
});