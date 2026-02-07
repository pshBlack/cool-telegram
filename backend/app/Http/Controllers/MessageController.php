<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get messages for a chat.
     */
    public function index(Request $request, Chat $chat)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        $messages = $chat->messages()
            ->with(['sender', 'media'])
            ->latest('sent_at')
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request, Chat $chat)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        $validated = $request->validate([
            'content' => 'nullable|string|max:10000',
            'type' => 'nullable|string|in:text,image,video,document,audio,attachment',
        ]);

        $message = Message::create([
            'chat_id' => $chat->chat_id,
            'sender_id' => $request->user()->user_id,
            'content' => $validated['content'] ?? null,
            'type' => $validated['type'] ?? 'text',
        ]);

        // Load relationships
        $message->load(['sender', 'media']);

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    /**
     * Get a specific message.
     */
    public function show(Request $request, Chat $chat, Message $message)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        // Verify message belongs to chat
        if ($message->chat_id !== $chat->chat_id) {
            return response()->json([
                'message' => 'Message not found in this chat',
            ], 404);
        }

        $message->load(['sender', 'media']);

        return response()->json($message);
    }

    /**
     * Update a message.
     */
    public function update(Request $request, Chat $chat, Message $message)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        // Verify message belongs to user
        if ($message->sender_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'You can only edit your own messages',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $message->update($validated);

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message->fresh(['sender', 'media']),
        ]);
    }

    /**
     * Delete a message (soft delete).
     */
    public function destroy(Request $request, Chat $chat, Message $message)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        // Verify message belongs to user
        if ($message->sender_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'You can only delete your own messages',
            ], 403);
        }

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(Request $request, Chat $chat, Message $message)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        // Don't mark own messages as read
        if ($message->sender_id === $request->user()->user_id) {
            return response()->json([
                'message' => 'Cannot mark own message as read',
            ], 400);
        }

        if (!$message->is_read) {
            $message->markAsRead();
            
            // Broadcast that message was read
            broadcast(new MessageRead($message, $request->user()->user_id))->toOthers();
        }

        return response()->json([
            'message' => 'Message marked as read',
            'data' => $message,
        ]);
    }

    /**
     * Mark all messages in chat as read.
     */
    public function markAllAsRead(Request $request, Chat $chat)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        $chat->markAsReadForUser($request->user()->user_id);

        return response()->json([
            'message' => 'All messages marked as read',
        ]);
    }

    /**
     * Send typing indicator.
     */
    public function typing(Request $request, Chat $chat)
    {
        // Verify user is participant
        $this->authorizeParticipant($request, $chat);

        $validated = $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        broadcast(new UserTyping(
            $chat->chat_id,
            $request->user(),
            $validated['is_typing']
        ))->toOthers();

        return response()->json([
            'message' => 'Typing indicator sent',
        ]);
    }

    /**
     * Verify user is a participant in the chat.
     */
    protected function authorizeParticipant(Request $request, Chat $chat): void
    {
        $isParticipant = $chat->participants()
            ->where('user_id', $request->user()->user_id)
            ->exists();

        if (!$isParticipant) {
            abort(403, 'You are not a participant in this chat');
        }
    }
}