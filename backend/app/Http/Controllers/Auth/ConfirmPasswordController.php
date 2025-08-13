<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    /**
     * Показує форму підтвердження пароля
     */
    public function showConfirmForm()
    {
        return view('auth.confirm-password'); // створіть цей Blade файл
    }

    /**
     * Обробляє підтвердження пароля
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password does not match our records.'),
            ]);
        }

        // Позначаємо сесію як підтверджену
        $request->session()->put('auth.password_confirmed_at', time());

        // Переадресація після успішного підтвердження
        return redirect()->intended('/');
    }

    /**
     * Використовуємо middleware auth, щоб лише авторизовані користувачі могли підтверджувати пароль
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}
