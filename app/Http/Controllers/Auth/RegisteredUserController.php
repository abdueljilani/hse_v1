<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return Inertia::render('Auth/Register', ['labels'=>[
            'app_name'=>__('messages.app_name'),'register'=>__('messages.create_account'),'register_hint'=>__('messages.registration_intro'),'name'=>__('Name'),'email'=>__('Email Address'),'password'=>__('messages.password'),'confirm_password'=>__('messages.confirm_password'),'submit'=>__('messages.register'),'has_account'=>__('messages.already_have_account'),'login'=>__('messages.login_now'),
        ]]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'is_approved' => false,
        ]);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('status', __('messages.registration_submitted', [], 'ar'));
    }
}
