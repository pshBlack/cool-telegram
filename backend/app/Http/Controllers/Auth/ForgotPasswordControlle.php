<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Показує форму для запиту скидання пароля
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password'); // створіть цей Blade файл
    }

    /**
     * Надсилає email зі скиданням пароля
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Надсилаємо email для скидання пароля
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Додатковий middleware для контролера (необов'язково)
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
