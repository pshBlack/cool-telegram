<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Channel;
use Laravolt\Avatar\Facade as Avatar;


class AuthController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $messages = [
            "name.required" => "Name cannot be empty",
            "name.max" => "Name cannot be more than 50 characters",
            "email.required" => "Email cannot be empty",
            "email.email" => "Email is not valid",
            "email.unique" => "A User with that E-Mail already exists.",
            "password.required" => "Password cannot be empty",
            "password.min" => "Password must be at least 6 characters",
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['message'=> $validator->errors()->first()], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate default avatar
       /* $avatarPath = 'avatars/'.$request->name.'-default.jpg';
        Avatar::create($request->name)->save(storage_path('app/public/'.$avatarPath), 100);
        $user->details()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar' => $avatarPath]
        );*/

        // Add user to the default General Channel
        $channel = Channel::find(1);
        if ($channel) {
            $channel->users()->attach($user->id);
        }

        return response()->json([
            'message' => 'You have registered successfully! Redirecting you to the login page.'
        ], 201);
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $messages = [
            "email.required" => "Email cannot be empty",
            "email.email" => "Email is not valid",
            "password.required" => "Password cannot be empty",
            "password.min" => "Password must be at least 6 characters",
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['message'=> $validator->errors()->first()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password or the account does not exist.'], 401);
        }

        // Create token manually
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addHours(1);
        $token->save();

        // Optional: mark user as online
        Cache::put('user-is-online-'.$user->id, true, now()->addMinutes(5));

        return response()->json([
            'user' => $user,
            'token' => $tokenResult->plainTextToken,
        ], 200)->cookie('jwt', $tokenResult->plainTextToken, 60);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
            Cache::forget('user-is-online-'.$user->id);
        }

        return response()->json(['message' => 'Successfully logged out'], 200)
                         ->cookie(Cookie::forget('jwt'));
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        $user = $request->user();
        if ($user && $user->details) {
            $user->avatar = $user->details->avatar;
            $user->desc = $user->details->desc;
        }

        return response()->json($user);
    }

    /**
     * Get all users list with details
     */
    public function allUsersList()
    {
        $allUsersList = User::with('details')->get();
        return response()->json($allUsersList);
    }
}
