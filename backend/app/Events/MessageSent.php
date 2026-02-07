<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        // Load relationships
        $this->message->load(['sender', 'chat', 'media']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'message_id' => $this->message->message_id,
                'chat_id' => $this->message->chat_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'sent_at' => $this->message->sent_at?->toISOString(),
                'is_read' => $this->message->is_read,
                'sender' => [
                    'user_id' => $this->message->sender->user_id,
                    'username' => $this->message->sender->username,
                    'full_name' => $this->message->sender->full_name,
                    'avatar' => $this->message->sender->avatar,
                ],
                'attachments' => $this->message->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->file_name,
                        'url' => $media->getUrl(),
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'collection' => $media->collection_name,
                    ];
                }),
            ],
        ];
    }
}