<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    // register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
           // 'first_name' => 'required|string|max:100',
            // 'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);


        $user = User::create([
            'username' => $validated['username'],
           // 'first_name' => $validated['first_name'],
           // 'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
        ]);

   $token = $user->createToken('api_token')->plainTextToken;
    $user->tokens()->latest()->first()->update([
        'expires_at' => Carbon::now()->addSeconds(100000) // time
    ]);

    return response()->json([
        'user' => $user,
        'token' => $token
    ], 201);
}

    // login
   public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    // one token per login
    // delete all previous tokens
    $user->tokens()->delete();

    $token = $user->createToken('api_token')->plainTextToken;
    $user->tokens()->latest()->first()->update([
        'expires_at' => Carbon::now()->addSeconds(100000) // time
    ]);
    return response()->json([
        'message' => 'Login successful',
        'user' => $user->makeHidden(['password_hash']),
        'token' => $token
    ]);
}

    // unlogin
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
