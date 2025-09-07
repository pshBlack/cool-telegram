<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Broadcast;
use App\Events\MessageSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\SendMessage;
use App\Models\Message;
use App\Models\User;


class StoreMessage
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
        
    }
    public function handle(SendMessage $event)
    {
       /* $message = Message::create([
            'chat_id'   => $event->chatId,
            'sender_id' => $event->user->user_id,
            'content'   => $event->content,
        ]);

        broadcast(new MessageSent($message))->toOthers(); */
    }
}
