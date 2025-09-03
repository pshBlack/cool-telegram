<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $users = User::where('username', 'LIKE', '%' . $validated['query'] . '%')
            ->limit(10) 
            ->get(['user_id', 'username', 'email', 'avatar_url']); 

        return response()->json($users);
    }
}

