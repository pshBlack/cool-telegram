<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use App\Events\MessageSent;
use App\Http\Controllers\MessageController;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->user_id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return \App\Models\Chat::where('chat_id', $chatId)
        ->whereHas('users', fn($q) => $q->where('chat_participants.user_id', $user->user_id))
        ->exists();
});