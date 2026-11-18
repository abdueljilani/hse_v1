<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class JoinRequestController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $joinRequests = User::query()
            ->where('is_approved', false)
            ->latest()
            ->paginate(20);

        $keys=['join_requests','join_requests_description','manage_users','name','email','requested_at','actions','approve','deny','confirm_deny_join_request','no_join_requests'];
        $labels=[]; foreach($keys as $key) $labels[$key]=__('messages.'.$key);
        return Inertia::render('Admin/JoinRequests/Index', ['joinRequests'=>$joinRequests,'labels'=>$labels,'success'=>session('success')]);
    }

    public function approve(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        abort_if($user->is_approved, 404);

        $user->is_approved = true;
        $user->save();

        return redirect()->route('admin.join-requests.index')
            ->with('success', __('messages.join_request_approved'));
    }

    public function deny(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        abort_if($user->is_approved, 404);

        $user->delete();

        return redirect()->route('admin.join-requests.index')
            ->with('success', __('messages.join_request_denied'));
    }
}
