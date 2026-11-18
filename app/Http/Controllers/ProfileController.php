<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', ['user'=>$request->user()->only('id','name','email','email_verified_at'),'status'=>session('status'),'labels'=>[
            'profile'=>__('messages.profile'),'profile_information'=>__('Profile Information'),'name'=>__('Name'),'email'=>__('Email'),'save'=>__('Save'),'saved'=>__('Saved.'),'password'=>__('Update Password'),'current_password'=>__('Current Password'),'new_password'=>__('New Password'),'confirm_password'=>__('Confirm Password'),'update_profile'=>__('Update your account profile information.'),'update_password_hint'=>__('Ensure your account is using a long, random password to stay secure.'),'password_updated'=>__('Password updated successfully.'),
        ]]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

}
