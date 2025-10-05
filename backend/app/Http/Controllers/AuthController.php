<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ======================
    // Register
    // ======================
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        // одразу логін користувача
        Auth::login($user);
        $request->session()->regenerate(); // важливо для запобігання session fixation

        return response()->json([
            'message' => 'Register successful',
            'user' => $user->makeHidden(['password_hash']),
        ]);
    }

    // ======================
    // Login
    // ======================
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password']
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate(); // важливо

        return response()->json([
            'message' => 'Login successful',
            'user' => $request->user()->makeHidden(['password_hash']),
        ]);
    }

    // ======================
    // Logout
    // ======================
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
      

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // ======================
    // Get Authenticated User
    // ======================
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->makeHidden(['password_hash']),
        ]);
    }
}
