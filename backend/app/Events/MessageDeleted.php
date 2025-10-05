<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted   implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $chatId;
    public $username;
    /**
     * Create a new event instance.
     */
    public function __construct($messageId, $chatId, $username)
    {
        $this->messageId = $messageId;
        $this->chatId = $chatId;
        $this->username = $username;
        
    }

    public function broadcastOn()
    {
        return  new PrivateChannel('chat.' . $this->chatId);
    }
     public function broadcastAs()
    {
        return 'message.deleted';
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
            'username' => $this->username
        ];
    }
}
