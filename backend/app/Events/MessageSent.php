<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


      public User $user;
    public Message $message;
    public string $channel;
    public string $type;

     /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Message $message
     * @param string $channel
     * @param string $type
     */

  public function __construct(User $user, Message $message, string $channel, string $type)
    {
        $this->user = $user;
        $this->message = $message;
        $this->channel = $channel;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): PresenceChannel
    {
        return match($this->type) {
            'channel' => new PresenceChannel("chat.channel.{$this->channel}"),
            'dm' => new PresenceChannel("chat.dm.{$this->channel}"),
            default => new PresenceChannel("chat"),
        };
    }

    /**
     * Optional: Назва події на фронтенді
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}