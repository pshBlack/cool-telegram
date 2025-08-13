<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Де перенаправляти після реєстрації
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Створює новий контролер
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Показує форму реєстрації
     */
    public function showRegistrationForm()
    {
        return view('auth.register'); // створіть цей Blade файл
    }

    /**
     * Обробляє реєстрацію користувача
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        Auth::login($user);

        return redirect($this->redirectTo);
    }

    /**
     * Валідатор даних
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Створює нового користувача
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
