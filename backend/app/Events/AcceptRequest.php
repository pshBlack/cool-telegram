<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcceptRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public string $friendChannel;
    public int $userId;
    public string $type;

    /**
     * Створення нового екземпляру події.
     *
     * @param string $friendChannel
     * @param int $userId
     * @param string $type
     */
    public function __construct(string $friendChannel, int $userId, string $type)
    {
        $this->friendChannel = $friendChannel;
        $this->userId = $userId;
        $this->type = $type;
    }

    /**
     * Дані, які будуть транслюватися через канал.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'friendChannel' => $this->friendChannel,
            'type' => $this->type,
        ];
    }

    /**
     * Канал, на який транслюється подія.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('event.acceptRequest.' . $this->userId);
    }
}