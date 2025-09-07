<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;


class GroupChatController extends Controller
{
    public function createGroupChat(Request $request)
    {
        $validated = $request->validate([
            'chat_name' => 'required|string|max:255',
            'user_ids'  => 'required|array|min:1', // array users
            'user_ids.*'=> 'exists:users,user_id',
            'chat_avatar_url' => 'nullable|url',
        ]);

        $authUser = $request->user();

        // create chat
        $chat = Chat::create([
            'chat_type' => 'group',
            'chat_name' => $validated['chat_name'],
            'chat_avatar_url' => $validated['chat_avatar_url'] ?? null,
            'created_by' => $authUser->user_id,
        ]);
        // attach users to chat with roles
        $participantIds = $validated['user_ids'];
        if (!in_array($authUser->user_id, $participantIds)) {
            $participantIds[] = $authUser->user_id;
        }

        $attachData = [];
        foreach ($participantIds as $id) {
            $attachData[$id] = [
                'role' => $id === $authUser->user_id ? 'owner' : 'member',
                'joined_at' => now(),
            ];
        }

        $chat->users()->attach($attachData);

        return response()->json([
            'message' => 'Group chat created successfully',
            'chat' => $chat->load('users')
        ], 201);
   }



      public function getGroupChatMessages(Request $request, $chatId)
    {
        $authUser = $request->user();

        $chat = Chat::with(['messages.sender', 'users'])
            ->where('chat_type', 'group')
            ->find($chatId);

        if (!$chat) {
            return response()->json(['message' => 'Group chat not found'], 404);
        }

        // validate membership
        if (!$chat->users->contains($authUser->user_id)) {
            return response()->json(['message' => 'You are not part of this chat'], 403);
        }

        // 
        $messages = $chat->messages()->with('sender')->orderBy('sent_at')->get();

        return response()->json([
            'chat' => $chat->only(['chat_id', 'chat_name', 'chat_avatar_url']),
            'messages' => $messages
        ]);
    }

     public function deleteGroupChat(Request $request, $chatId)
    {
    $authUser = $request->user();
    $chat = Chat::with('users', 'messages')->find($chatId);

    if (!$chat || $chat->chat_type !== 'group') {
        return response()->json(['message' => 'Group chat not found'], 404);
    }

    // validate if auth user is owner
    $participant = $chat->users()->where('user_id', $authUser->user_id)->first();
    if (!$participant || $participant->pivot->role !== 'owner') {
        return response()->json(['message' => 'Only the owner can delete this group chat'], 403);
    }

    $chat->messages()->delete();
    $chat->users()->detach();
    $chat->delete();

    return response()->json(['message' => 'Group chat deleted successfully']);
  }
}