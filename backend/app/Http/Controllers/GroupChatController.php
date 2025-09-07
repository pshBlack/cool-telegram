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
}
