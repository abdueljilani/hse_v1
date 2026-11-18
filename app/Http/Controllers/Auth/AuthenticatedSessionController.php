<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        App::setLocale('ar');

        return Inertia::render('Auth/Login', ['status'=>session('status'),'labels'=>[
            'app_name'=>__('messages.app_name'),'welcome'=>app()->getLocale()==='ar'?'مرحباً بعودتك':'Welcome back','login_hint'=>app()->getLocale()==='ar'?'سجّل الدخول للمتابعة إلى نظام تقارير الصحة والسلامة والبيئة.':'Sign in to your account to continue.','email'=>__('messages.email'),'password'=>__('messages.password'),'remember'=>app()->getLocale()==='ar'?'تذكرني':'Remember me','login'=>__('messages.login_now'),'no_account'=>__('messages.no_account_yet'),'register'=>__('messages.create_account'),'forgot'=>app()->getLocale()==='ar'?'هل نسيت كلمة المرور؟':'Forgot your password?','validation'=>__('Please check the highlighted fields.'),
        ]]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
