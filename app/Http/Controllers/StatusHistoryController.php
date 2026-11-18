<?php

namespace App\Http\Controllers;

use App\Models\HseReportStatusHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatusHistoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $filters = $request->validate([
            'report_number' => ['nullable', 'string', 'max:255'],
            'changed_by' => ['nullable', 'string', 'max:255'],
        ]);

        $query = HseReportStatusHistory::query();

        if (! empty($filters['report_number'])) {
            $query->where('report_number', 'like', '%' . trim($filters['report_number']) . '%');
        }

        if (! empty($filters['changed_by'])) {
            $query->where('changed_by_name', 'like', '%' . trim($filters['changed_by']) . '%');
        }

        $history = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $keys=['status_history','status_history_description','report_number','changed_by','previous_status','new_status','changed_at','search','reset','in_progress','processed','closed','no_status_history'];
        $labels=[]; foreach($keys as $key) $labels[$key]=__('messages.'.$key);
        return Inertia::render('Admin/StatusHistory/Index', ['history'=>$history,'filters'=>$filters,'labels'=>$labels]);
    }
}
