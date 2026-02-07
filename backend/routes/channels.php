<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User's personal channel
Broadcast::channel('user.{userId}', function (User $user, string $userId) {
    return $user->user_id === $userId;
});

// Chat channel - only participants can listen
Broadcast::channel('chat.{chatId}', function (User $user, string $chatId) {
    $chat = Chat::find($chatId);
    
    if (!$chat) {
        return false;
    }
    
    // Check if user is a participant in this chat
    return $chat->participants()
        ->where('user_id', $user->user_id)
        ->exists();
});

// Online presence channel
Broadcast::channel('online', function (User $user) {
    return [
        'id' => $user->user_id,
        'name' => $user->full_name,
        'username' => $user->username,
        'avatar' => $user->avatar,
    ];
});

// Typing indicator channel for specific chat
Broadcast::channel('typing.{chatId}', function (User $user, string $chatId) {
    $chat = Chat::find($chatId);
    
    if (!$chat) {
        return false;
    }
    
    return $chat->participants()
        ->where('user_id', $user->user_id)
        ->exists();
});