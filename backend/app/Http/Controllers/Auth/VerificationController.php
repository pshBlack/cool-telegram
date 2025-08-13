<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerificationController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    // Форма перевірки email
    public function notice()
    {
        return view('auth.verify');
    }

    // Підтвердження email
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectTo);
        }

        if ($request->user()->id !== $user->id) {
            abort(403);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect($this->redirectTo)->with('verified', true);
    }

    // Повторна відправка листа
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectTo);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', true);
    }
}
