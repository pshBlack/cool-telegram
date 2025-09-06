<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;

class AllUsersChatController extends Controller
{
    public function create(Request $request)
    {
        $authUser = $request->user();

        
        $chat = Chat::where('chat_type', 'group')
                    ->where('chat_name', 'All Users')
                    ->first();

        if ($chat) {
            return response()->json([
                'message' => 'All Users chat already exists',
                'chat' => $chat->load('users')
            ]);
        }
        $chat = Chat::create([
            'chat_type' => 'group',
            'chat_name' => 'All Users',
            'created_by' => $authUser->user_id,
        ]);

        $users = User::all()->pluck('user_id')->toArray();

        $attach = [];
        foreach ($users as $userId) {
            $attach[$userId] = [
                'role' => $userId === $authUser->user_id ? 'owner' : 'member',
                'joined_at' => now()
            ];
        }

        $chat->users()->attach($attach);

        return response()->json([
            'message' => 'All Users chat created',
            'chat' => $chat->load('users')
        ], 201);
     }
}