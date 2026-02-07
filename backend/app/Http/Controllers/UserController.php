<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile information.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => [
                'sometimes', 
                'string', 
                'max:50', 
                Rule::unique('users')->ignore($user->user_id, 'user_id')
            ],
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Search for users by username, email, first name, or last name.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('user_id', '!=', $request->user()->user_id) 
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['user_id', 'username', 'first_name', 'last_name', 'avatar_url', 'last_seen_at']);

        return response()->json($users);
    }

    /**
     * Get a user's profile information by their user ID.
     */
    public function show(User $user)
    {
        return response()->json([
            'user_id' => $user->user_id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar_url' => $user->avatar_url,
            'bio' => $user->bio,
            'last_seen_at' => $user->last_seen_at,
            'is_online' => $user->isOnline(),
        ]);
    }

    /**
     * Update the user's last seen timestamp.
     * Can be called periodically from frontend (polling) or via websockets.
     */
    public function updateLastSeen(Request $request)
    {
        $request->user()->updateLastSeen();

        return response()->json(['message' => 'Status updated']);
    }
}