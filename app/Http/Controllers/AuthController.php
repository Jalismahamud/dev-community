<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    use ApiResponse;

    public function registerForm() { return Inertia::render('Auth/Register'); }
    public function loginForm() { return Inertia::render('Auth/Login'); }
    public function forgotPasswordForm() { return Inertia::render('Auth/ForgotPassword'); }

    public function forgotPassword(\Illuminate\Http\Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return back()->with('status', 'If your email is registered, we have sent a password reset link.');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('feed');
    }

    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->validated(), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('feed'));
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}