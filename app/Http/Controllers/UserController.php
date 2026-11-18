<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        $users = User::where('is_approved', true)->latest()->paginate(25);
        $pendingJoinRequestCount = User::where('is_approved', false)->count();

        return Inertia::render('Admin/Users/Index', ['users' => $users, 'pendingJoinRequestCount' => $pendingJoinRequestCount, 'currentUserId' => auth()->id(), 'labels' => $this->labels(), 'success'=>session('success'), 'error'=>session('error')]);
    }

    public function create()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        return Inertia::render('Admin/Users/Form', ['labels' => $this->labels(), 'user' => null]);
    }

    public function store(Request $request)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:employee,hse_officer,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_created'));
    }

    public function edit(User $user)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        return Inertia::render('Admin/Users/Form', ['labels' => $this->labels(), 'user' => $user, 'currentUserId' => auth()->id()]);
    }

    public function update(Request $request, User $user)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:employee,hse_officer,admin'],
        ]);

        if ((int) $user->id === (int) auth()->id() && $request->input('role') !== 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', __('messages.cannot_change_own_role'));
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', __('messages.user_updated'));
    }

    public function destroy(User $user)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', __('messages.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('messages.user_deleted'));
    }
    public function resetPassword(User $user)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('messages.cannot_reset_self'));
        }

        $temporaryPassword = Str::password(10);

        $user->password = Hash::make($temporaryPassword);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.password_reset_success', ['password' => $temporaryPassword]));
    }

    private function labels(): array
    {
        $keys = ['manage_users','users','join_requests','create_user','name','email','role','actions','employee','hse_officer','admin','edit_user','reset_password','delete','confirm_reset_password','confirm_delete_user','no_users','enter_details','save','cancel','save_changes','reset_password_hint'];
        $labels=[]; foreach($keys as $key) $labels[$key]=__('messages.'.$key);
        $labels['password']=__('Password'); $labels['confirm_password']=__('Confirm Password'); $labels['leave_blank']=__('Leave blank to keep current');
        return $labels;
    }
}
