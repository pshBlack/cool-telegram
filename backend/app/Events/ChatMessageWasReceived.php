<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class ChatMessageWasReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatMessage;
    public $user;

    /**
     * Створення нового екземпляру події.
     *
     * @param mixed $chatMessage
     * @param mixed $user
     */
    public function __construct($chatMessage, $user)
    {
        $this->chatMessage = $chatMessage;
        $this->user = $user;
    }

    /**
     * Канал, на який транслюється подія.
     *
     * @return \Illuminate\Broadcasting\PresenceChannel
     */
    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('chat');
    }

    /**
     * Ім'я події на фронтенді.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'ChatMessageWasReceived';
    }
}