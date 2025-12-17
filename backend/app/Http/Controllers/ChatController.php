<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        ->whereHas('users', fn($q) => $q->where('chat_participants.user_id', $authUser->user_id))
        ->whereHas('users', fn($q) => $q->where('chat_participants.user_id', $otherUser->user_id))
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

  
    $now = now();
    DB::table('chat_participants')->insert([
        [
            'participant_id' => (string) Str::uuid(),
            'chat_id' => $chat->chat_id,
            'user_id' => $authUser->user_id,
            'role' => 'owner',
            'joined_at' => $now,
        ],
        [
            'participant_id' => (string) Str::uuid(),
            'chat_id' => $chat->chat_id,
            'user_id' => $otherUser->user_id,
            'role' => 'member',
            'joined_at' => $now,
        ],
    ]);

    return response()->json([
        'message' => 'Chat created',
        'chat' => $chat->load('users')
    ], 201);
}

       // delete chat
    public function deleteChat(Request $request, $chatId)
    {
        $authUser = $request->user();
        $chat = Chat::with('users', 'messages')->find($chatId);
        
       if (!$chat || $chat->chat_type !== 'one_to_one') {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        if (!$chat->users->contains($authUser->user_id)) {
            return response()->json(['message' => 'You are not part of this chat'], 403);
        }

         DB::transaction(function () use ($chat) {
        $chat->messages()->delete();
        $chat->users()->detach();
        $chat->delete();
    });

        return response()->json(['message' => 'Chat deleted successfully']);
    }
     
    public function getUserChats(Request $request)
{
    $authUser = $request->user();

    $chats = $authUser->chats()
        ->with(['users:user_id,username,email,avatar_url', 'messages' => function ($q) {
            $q->latest('sent_at')->limit(1); // last message
        }])
        ->get()
        ->map(function ($chat) use ($authUser) {
            // get display name
            if ($chat->chat_type === 'one_to_one') {
                $otherUser = $chat->users->firstWhere('user_id', '!=', $authUser->user_id);
                $chat->display_name = $otherUser ? $otherUser->username : 'Unknown';
            } else {
                $chat->display_name = $chat->chat_name ?? 'Group Chat';
            }
            return $chat;
        });


    return response()->json($chats);
}




}