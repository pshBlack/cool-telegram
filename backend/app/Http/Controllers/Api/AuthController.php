<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\users1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource; // <-- Додано імпорт UserResource

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Registration attempt', ['data' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'username' => 'nullable|string|max:32|unique:users1',
                'email' => 'required|string|email|max:64|unique:users1',
                'password' => 'required|string|min:8',
            ]);
            
            Log::info('Validation passed', $validated);

            $user = new users1();
            $expirecAt = now('UTC')->addHours(3);
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            
            if ($user->save()) {
                $token = $user->createToken('auth-token', expiresAt: $expirecAt)->plainTextToken;
                Log::info('User registered successfully', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'User registered successfully',
                    'token' => $token,
                    'token_expires_at' => $expirecAt,
                    'user' => $user,
                ], 201);
            } else {
                Log::error('Failed to save user', ['data' => $request->all()]);
                return response()->json([
                    'message' => 'Failed to save user',
                    'errors' => 'Database save failed'
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $expirecAt = now('UTC')->addHours(3);
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = users1::where('email', $request->email)->first();

        // Перевіряємо, чи користувач існує і пароль правильний
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Створюємо токен для користувача після успішного входу
        $token = $user->createToken('auth-token', expiresAt: $expirecAt )->plainTextToken;

        // Повертаємо відповідь, використовуючи UserResource для форматування даних користувача
        return response()->json([
            'token' => $token,
            'user' => new UserResource($user) // <-- Використання UserResource
        ]);
    }
}