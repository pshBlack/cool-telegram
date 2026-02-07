<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected SocialAuthService $socialAuthService
    ) {}

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            #'first_name' => 'nullable|string|max:100',
            #'last_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            #'first_name' => $validated['first_name'] ?? null,
            #'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user with credentials.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last seen
        $user->updateLastSeen();

        $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            
            $user = $this->socialAuthService->handleSocialCallback($socialUser, 'google');
            
            // Update last seen
            $user->updateLastSeen();
            
            $token = $this->socialAuthService->createToken($user, $request->device_name ?? 'web');

            // For web applications, you might want to redirect with token
            // For API, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            // Redirect to frontend with token
            return redirect()->to(config('app.frontend_url') . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Link Google account to existing user.
     */
    public function linkGoogleAccount(Request $request)
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            
            $user = $request->user();
            
            // Check if Google account is already linked to another user
            $existingUser = User::where('google_id', $socialUser->getId())
                ->where('user_id', '!=', $user->user_id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'This Google account is already linked to another user',
                ], 400);
            }

            // Link Google account
            $user->update([
                'google_id' => $socialUser->getId(),
                'avatar_url' => $user->avatar_url ?? $socialUser->getAvatar(),
            ]);

            return response()->json([
                'message' => 'Google account linked successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to link Google account',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unlink Google account.
     */
    public function unlinkGoogleAccount(Request $request)
    {
        $user = $request->user();

        if (!$user->hasGoogleLinked()) {
            return response()->json([
                'message' => 'No Google account is linked',
            ], 400);
        }

        $user->update([
            'google_id' => null,
        ]);

        return response()->json([
            'message' => 'Google account unlinked successfully',
            'user' => $user,
        ]);
    }
}