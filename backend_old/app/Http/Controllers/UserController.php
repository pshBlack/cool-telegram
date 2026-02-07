<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function search($username)
    {
      /*  $validated = $request->validate([
            'query' => 'required|string|min:1',
        ]);
*/
        $users = User::where('username', 'LIKE', '%' . $username . '%')
            ->limit(10)     
            ->get(['user_id', 'username', 'email', 'avatar_url']); 

        return response()->json($users);
    }

    // Upload avatar
    public function uploadAvatar(Request $request)
    {
    $validated = $request->validate([
        'avatar' => 'required|image|max:2048', // max 2MB
    ]);

    $user = $request->user();

    
    if ($user->avatar_url) {
        $oldPath = str_replace('/storage/', '', $user->avatar_url);
        Storage::disk('public')->delete($oldPath);
    }

    // Завантажуємо новий
    $path = $validated['avatar']->store('avatars', 'public');

    
    $user->avatar_url = '/storage/' . $path;
    $user->save();

    return response()->json([
        'message' => 'Avatar uploaded successfully',
        'avatar_url' => $user->avatar_url,
    ]);
}
     public function updateBio(Request $request)
     {
    $validated = $request->validate([
        'bio' => 'nullable|string|max:1000',
    ]);

    $user = $request->user();
    $user->bio = $validated['bio'];
    $user->save();

    return response()->json([
        'message' => 'Bio updated successfully',
        'bio' => $user->bio,
    ]);
}



}

