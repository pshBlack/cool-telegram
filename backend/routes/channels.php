<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

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

// Простий канал для всіх користувачів
Broadcast::channel('chat', fn($user) => $user);

// Канал для групових чатів (channels)
Broadcast::channel('chat.channel.{channel_id}', function (User $user, $channel_id) {
    // Користувач із ID 1 має доступ завжди
    if ($channel_id == 1) return $user;

    // Перевірка, чи користувач прив'язаний до цього каналу
    return $user->channels()->where('channel_id', $channel_id)->exists() ? $user : null;
});

// Приватний канал для дірект-меседжів (DM)
Broadcast::channel('chat.dm.{channel_id}', function (User $user, $channel_id) {
    return $user->channels()->where('channel_id', $channel_id)->exists() ? $user : null;
});

// Канал для конкретного користувача
Broadcast::channel('App.User.{id}', fn(User $user, $id) => (int)$user->id === (int)$id);

// Канал для подій типу acceptRequest
Broadcast::channel('event.acceptRequest.{id}', fn(User $user, $id) => (int)$user->id === (int)$id);
