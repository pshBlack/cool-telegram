<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;

class ChatController extends Controller
{
    public function createChat(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string', // email or username
        ]);

        $authUser = $request->user();

        $otherUser = User::where('email', $validated['identifier'])
            ->orWhere('username', $validated['identifier'])
            ->first();

        if (!$otherUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($authUser->user_id === $otherUser->user_id) {
            return response()->json(['message' => 'Cannot create chat with yourself'], 400);
        }

        // check if chat already exists
        $existingChat = Chat::where('chat_type', 'one_to_one')
            ->whereHas('users', fn($q) => $q->where('user_id', $authUser->user_id))
            ->whereHas('users', fn($q) => $q->where('user_id', $otherUser->user_id))
            ->first();

        if ($existingChat) {
            return response()->json([
                'message' => 'Chat already exists',
                'chat' => $existingChat->load('users')
            ]);
        }

        // create new chat
        $chat = Chat::create([
            'chat_type' => 'one_to_one',
            'created_by' => $authUser->user_id,
        ]);

        // add participants
        $chat->users()->attach([
            $authUser->user_id => ['role' => 'owner', 'joined_at' => now()],
            $otherUser->user_id => ['role' => 'member', 'joined_at' => now()],
        ]);

        return response()->json([
            'message' => 'Chat created',
            'chat' => $chat->load('users')
        ], 201);
    }
     
    public function getUserChats(Request $request)
{
    $authUser = $request->user();

    $chats = $authUser->chats()
        ->with(['users:user_id,username,email,avatar_url', 'messages' => function ($q) {
            $q->latest('sent_at')->limit(1); // last message
        }])
        ->get();

    return response()->json($chats);
}




}