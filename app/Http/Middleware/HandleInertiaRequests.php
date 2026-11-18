<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Notifications\HseReportCreatedNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'inertia-root';

    public function rootView(Request $request): string
    {
        if (preg_match('#^hse-reports/(?:export/pdf|\d+/export/pdf)/?$#', $request->path())) {
            return 'inertia-print-root';
        }

        return $request->user() ? $this->rootView : 'inertia-root-guest';
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user?->isAdmin() ?? false;
        $showsReports = $isAdmin || ($user?->isHseOfficer() ?? false);

        return array_merge(parent::share($request), [
            'auth' => ['user' => $user?->only('id', 'name', 'email', 'role')],
            'app' => [
                'locale' => app()->getLocale(),
                'labels' => collect(['app_name','dashboard','reports','new_report','analytics','manage_users','join_requests','status_history','profile','language','english','arabic','logout','close','menu'])
                    ->mapWithKeys(fn (string $key) => [$key => __('messages.' . $key)])
                    ->all(),
            ],
            'pendingJoinRequestCount' => $isAdmin ? User::where('is_approved', false)->count() : 0,
            'unreadReportNotificationCount' => $showsReports
                ? $user->unreadNotifications()->where('type', HseReportCreatedNotification::class)->count()
                : 0,
        ]);
    }
}
