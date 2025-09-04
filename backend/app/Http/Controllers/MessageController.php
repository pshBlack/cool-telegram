<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Chat;
use App\Events\MessageSent;
use App\Events\SendMessage;


class MessageController extends Controller
{

    public function sendMessage(Request $request, $chatId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $authUser = $request->user();

        // validate chat membership
        $chat = Chat::where('chat_id', $chatId)
            ->whereHas('users', fn($q) => $q->where('user_id', $authUser->user_id))
            ->first();

        if (!$chat) {
            return response()->json(['message' => 'You are not in this chat'], 403);
        }

       /* $message = Message::create([
            'chat_id' => $chat->chat_id,
            'sender_id' => $authUser->user_id,
            'content' => $validated['content'],
            'sent_at' => now(),
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Message sent',
            'data' => $message->load('sender')
        ], 201);*/

        event(new SendMessage($chatId, $validated['content'], $authUser));

        return response()->json(['message' => 'Message sent'], 201);
      

    }

    // history of messages in chat
    public function getMessages(Request $request, $chatId)
    {
        $authUser = $request->user();

        $chat = Chat::where('chat_id', $chatId)
            ->whereHas('users', fn($q) => $q->where('user_id', $authUser->user_id))
            ->first();

        if (!$chat) {
            return response()->json(['message' => 'You are not in this chat'], 403);
        }

        $messages = Message::where('chat_id', $chat->chat_id)
            ->orderBy('sent_at', 'asc')
            ->with('sender')
            ->get();

        return response()->json($messages);
    }

    // mark message as read
    public function markAsRead(Request $request, $messageId)
    {
        $authUser = $request->user();

        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        // Перевірка, чи користувач учасник чату
        $chat = $message->chat;
        if (!$chat->users()->where('users.user_id', $authUser->user_id)->exists()) {
            return response()->json(['message' => 'You are not in this chat'], 403);
        }

        $message->is_read = true;
        $message->save();

        return response()->json(['message' => 'Marked as read']);
    }
}