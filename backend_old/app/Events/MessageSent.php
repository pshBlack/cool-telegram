<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;



class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->chat_id); //{chat_id} warning
    }

  public function broadcastAs()
    {
        return 'message.sent';
    }
     public function broadcastWith()
    {
        return [
            'message_id'=> $this->message->message_id,
            'chat_id'   => $this->message->chat_id,
            'sender_id' => $this->message->sender_id,
            'content'   => $this->message->content,
            'sender'    => $this->message->sender ? [
                'user_id'   => $this->message->sender->user_id,
                'username'  => $this->message->sender->username,
                'email'     => $this->message->sender->email,
                'avatar'    => $this->message->sender->avatar_url,
            ]: null,
            
        ];
    }
}
