<?php

namespace App\Http\Controllers;

use App\Models\HseReport;
use App\Models\HseReportStatusHistory;
use App\Models\User;
use App\Notifications\HseReportCreatedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HseReportController extends Controller
{
public function dashboard()
{
    $reports = HseReport::query();
    if (auth()->user()?->isEmployee()) {
        $reports->where('user_id', auth()->id());
    }

    $aggregate = (clone $reports)
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as in_progress', ['in_progress'])
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as processed', ['processed'])
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as closed', ['closed'])
        ->selectRaw('COALESCE(SUM(CASE WHEN report_type = ? THEN 1 ELSE 0 END), 0) as hazard_count', ['hazard'])
        ->selectRaw('COALESCE(SUM(CASE WHEN report_type = ? THEN 1 ELSE 0 END), 0) as incident_count', ['incident'])
        ->selectRaw('COALESCE(SUM(CASE WHEN report_type = ? THEN 1 ELSE 0 END), 0) as near_miss_count', ['near_miss'])
        ->selectRaw('COALESCE(SUM(CASE WHEN report_type = ? THEN 1 ELSE 0 END), 0) as observation_count', ['observation'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as low_risk_count', ['low'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as medium_risk_count', ['medium'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as high_risk_count', ['high'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as critical_risk_count', ['critical'])
        ->first();

    $stats = [
        'total' => (int) $aggregate->total,
        'in_progress' => (int) $aggregate->in_progress,
        'processed' => (int) $aggregate->processed,
        'closed' => (int) $aggregate->closed,
    ];
    $chartData = [
        'types' => [
            'hazard' => (int) $aggregate->hazard_count,
            'incident' => (int) $aggregate->incident_count,
            'near_miss' => (int) $aggregate->near_miss_count,
            'observation' => (int) $aggregate->observation_count,
        ],
        'risks' => [
            'low' => (int) $aggregate->low_risk_count,
            'medium' => (int) $aggregate->medium_risk_count,
            'high' => (int) $aggregate->high_risk_count,
            'critical' => (int) $aggregate->critical_risk_count,
        ],
    ];

    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'chartData' => $chartData,
        'locale' => app()->getLocale(),
        'summary' => auth()->user()->isEmployee()
            ? (app()->getLocale() === 'ar'
                ? 'ملخص تقاريرك وحالاتها ومستويات المخاطر المرتبطة بها.'
                : 'A summary of your reports, statuses, and risk levels.')
            : __('messages.dashboard_summary'),
        'labels' => [
            'dashboard' => __('messages.dashboard'),
            'total_reports' => __('messages.total_reports'),
            'in_progress' => __('messages.in_progress'),
            'processed' => __('messages.processed'),
            'closed' => __('messages.closed_reports'),
            'all_reports_summary' => auth()->user()->isEmployee()
                ? (app()->getLocale() === 'ar' ? 'إجمالي تقاريرك المرسلة' : 'Your submitted reports')
                : __('messages.all_reports_summary'),
            'need_followup_summary' => __('messages.need_followup_summary'),
            'processed_summary' => __('messages.processed_summary'),
            'completed_summary' => __('messages.completed_summary'),
            'report_types_chart' => __('messages.report_types_chart'),
            'risk_levels_chart' => __('messages.risk_levels_chart'),
            'report_status_chart' => __('messages.report_status_chart'),
            'chart_count_and_percent' => __('messages.chart_count_and_percent'),
            'no_chart_data' => __('messages.no_chart_data'),
            'hazard' => __('messages.hazard'),
            'incident' => __('messages.incident'),
            'near_miss' => __('messages.near_miss'),
            'observation' => __('messages.observation'),
            'low' => __('messages.low'),
            'medium' => __('messages.medium'),
            'high' => __('messages.high'),
            'critical' => __('messages.critical'),
        ],
        'success' => session('success'),
    ]);
}
public function index(Request $request)
{
    $user = auth()->user();
    if ($user?->isHseOfficer() || $user?->isAdmin()) {
        $user->unreadNotifications()
            ->where('type', HseReportCreatedNotification::class)
            ->update(['read_at' => now()]);
    }

    $reports = $this->reportListQuery($request)->latest()->paginate(25)->withQueryString();

    return Inertia::render('Reports/Index', [
        'reports' => $reports,
        'filters' => $request->only(['search', 'report_type', 'risk_level', 'status']),
        'canDelete' => $user?->isHseOfficer() || $user?->isAdmin(),
        'userId' => $user?->id,
        'labels' => $this->reportUiLabels(),
        'success' => session('success'),
    ]);
}

public function unreadReportNotificationCount(): JsonResponse
{
    $user = auth()->user();
    abort_unless($user?->isHseOfficer() || $user?->isAdmin(), 403);

    $count = $user->unreadNotifications()
        ->where('type', HseReportCreatedNotification::class)
        ->count();

    return response()->json(['count' => $count]);
}

public function exportExcel(Request $request)
{
    $reports = $this->reportListQuery($request)->latest();
    $filename = 'hse-reports-' . now()->format('Y-m-d') . '.csv';

    return $this->excelResponse($reports, $filename);
}

public function exportReportExcel(HseReport $hseReport)
{
    $this->authorizeReportExport($hseReport);

    return $this->excelResponse(collect([$hseReport]), 'hse-report-' . $hseReport->id . '.csv', true);
}

public function exportReportPdf(HseReport $hseReport)
{
    $this->authorizeReportExport($hseReport);

    return Inertia::render('Reports/Print', $this->printProps([], $hseReport));
}

private function excelResponse($reports, string $filename, bool $includeDetails = false)
{

    return response()->streamDownload(function () use ($reports, $includeDetails) {
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        $headers = [
            __('messages.report_number'), __('messages.title'), __('messages.report_type'),
            __('messages.location'), __('messages.risk_level'), __('messages.status'),
            __('messages.reported_at'),
        ];
        if ($includeDetails) {
            $headers[] = __('messages.description');
            $headers[] = __('messages.corrective_action');
        }
        fputcsv($output, $headers);

        $rows = $reports instanceof Builder ? $reports->cursor() : $reports;

        foreach ($rows as $report) {
            $values = [
                $report->report_number,
                $report->title,
                __('messages.' . $report->report_type),
                $report->location,
                __('messages.' . $report->risk_level),
                __('messages.' . $report->status),
                $report->reported_at?->format('Y-m-d H:i') ?? '',
            ];
            if ($includeDetails) {
                $values[] = $report->description;
                $values[] = $report->corrective_action ?? '';
            }

            fputcsv($output, array_map(fn ($value) => preg_match('/^[=+@\\-\\t\\r]/u', (string) $value) ? "'" . $value : $value, $values));
        }

        fclose($output);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
}

public function exportPdf(Request $request)
{
    $query = $this->reportListQuery($request);
    $filters = $request->only(['search', 'report_type', 'risk_level', 'status']);

    return Inertia::render('Reports/Print', $this->printProps(
        [],
        null,
        route('hse-reports.export.pdf.data', $filters),
        (clone $query)->count(),
    ));
}

public function exportPdfData(Request $request)
{
    $reports = $this->reportListQuery($request)->latest();
    $columns = [
        'report_number', 'title', 'report_type', 'location', 'risk_level',
        'status', 'reported_at',
    ];

    return response()->stream(function () use ($reports, $columns): void {
        $buffer = [];
        $flushBuffer = function () use (&$buffer): void {
            if ($buffer === []) {
                return;
            }

            echo implode("\n", $buffer) . "\n";
            $buffer = [];

            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();
        };

        foreach ($reports->cursor() as $report) {
            $buffer[] = json_encode(
                $report->only($columns),
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
            );

            if (count($buffer) >= 100) {
                $flushBuffer();
            }
        }

        $flushBuffer();
    }, 200, [
        'Content-Type' => 'application/x-ndjson; charset=UTF-8',
        'Cache-Control' => 'no-store',
        'X-Accel-Buffering' => 'no',
    ]);
}

private function printProps($reports, ?HseReport $singleReport = null, ?string $dataUrl = null, int $reportCount = 0): array
{
    return [
        'reports' => $reports,
        'singleReport' => $singleReport,
        'dataUrl' => $dataUrl,
        'reportCount' => $reportCount,
        'generatedAt' => now()->format('Y-m-d H:i'),
        'labels' => $this->reportUiLabels() + [
            'app_name' => __('messages.app_name'),
            'print' => __('Print'),
            'loading_reports' => app()->getLocale() === 'ar' ? 'جارٍ تجهيز التقارير للطباعة...' : 'Preparing reports for printing...',
            'loaded_reports' => app()->getLocale() === 'ar' ? 'تم تجهيز :loaded من :total تقرير' : 'Prepared :loaded of :total reports',
            'export_error' => app()->getLocale() === 'ar' ? 'تعذر تجهيز التقارير للطباعة. حاول مرة أخرى.' : 'Unable to prepare reports for printing. Please try again.',
        ],
    ];
}

private function reportListQuery(Request $request)
{
    $filters = $request->validate([
        'search' => ['nullable', 'string', 'max:255'],
        'report_type' => ['nullable', Rule::in(['hazard', 'incident', 'near_miss', 'observation'])],
        'risk_level' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
        'status' => ['nullable', Rule::in(['open', 'in_progress', 'processed', 'closed'])],
    ]);

    $query = HseReport::query();

    if (auth()->user()?->isEmployee()) {
        $query->where('user_id', auth()->id());
    }

    if (! empty($filters['search'])) {
        $search = trim($filters['search']);
        $query->where(function ($q) use ($search) {
            $q->where('report_number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    foreach (['report_type', 'risk_level', 'status'] as $filter) {
        if (! empty($filters[$filter])) {
            $query->where($filter, $filters[$filter]);
        }
    }

    return $query;
}

private function authorizeReportExport(HseReport $report): void
{
    $user = auth()->user();
    $canExport = $user?->isHseOfficer()
        || $user?->isAdmin()
        || ($user?->isEmployee() && (int) $report->user_id === (int) $user->id);

    abort_unless($canExport, 403);
}
    public function create()
    {
        return Inertia::render('Reports/Create', ['labels' => $this->reportUiLabels()]);
    }
public function show(HseReport $hseReport)
{
    if (auth()->user()?->isEmployee() && (int) $hseReport->user_id !== (int) auth()->id()) {
        abort(403);
    }

    return Inertia::render('Reports/Show', [
        'report' => $hseReport,
        'canEdit' => auth()->user()?->isHseOfficer() || auth()->user()?->isAdmin(),
        'canDelete' => auth()->user()?->isHseOfficer() || auth()->user()?->isAdmin() || (auth()->user()?->isEmployee() && (int) $hseReport->user_id === (int) auth()->id()),
        'labels' => $this->reportUiLabels(),
        'success' => session('success'),
    ]);
}
public function edit(HseReport $hseReport)
{
    if (! auth()->user()?->isHseOfficer() && ! auth()->user()?->isAdmin()) {
        abort(403);
    }

    return Inertia::render('Reports/Edit', ['report' => $hseReport, 'labels' => $this->reportUiLabels()]);
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'report_type' => 'required|in:hazard,incident,near_miss,observation',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_level' => 'required|in:low,medium,high,critical',
            'corrective_action' => 'nullable|string',
            'reported_at' => 'nullable|date',
        ]);

        $validated['status'] = 'in_progress';

        $validated['user_id'] = auth()->id();

        $report = DB::transaction(function () use ($validated) {
            $report = HseReport::create([
                ...$validated,
                'report_number' => 'TEMP-' . Str::uuid(),
            ]);

            $sequence = $report->id;
            do {
                $reportNumber = 'HSE-' . now()->format('Y') . '-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
                $sequence++;
            } while (HseReport::where('report_number', $reportNumber)->exists());

            $report->update(['report_number' => $reportNumber]);

            return $report;
        });

        $recipients = User::query()->whereIn('role', ['hse_officer', 'admin'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new HseReportCreatedNotification($report));
        }

        return redirect()
            ->route('hse-reports.index')
            ->with('success', __('messages.success'));
    }
    public function update(Request $request, HseReport $hseReport)
{
        if (! auth()->user()?->isHseOfficer() && ! auth()->user()?->isAdmin()) {
            abort(403);
        }


        $oldStatus = $hseReport->status;


    $validated = $request->validate([
        'report_number' => 'required|string|max:255|unique:hse_reports,report_number,' . $hseReport->id,
        'title' => 'required|string|max:255',
        'report_type' => 'required|in:hazard,incident,near_miss,observation',
        'location' => 'required|string|max:255',
        'description' => 'required|string',
        'risk_level' => 'required|in:low,medium,high,critical',
        'corrective_action' => 'nullable|string',
        'status' => 'required|in:in_progress,processed,closed',
        'reported_at' => 'nullable|date',
    ]);

    $allowedTransitions = [
        'open' => ['open', 'in_progress'],
        'in_progress' => ['in_progress', 'processed'],
        'processed' => ['processed', 'closed'],
        'closed' => ['closed'],
    ];
    if (! in_array($validated['status'], $allowedTransitions[$oldStatus] ?? [], true)) {
        return back()->withErrors(['status' => __('messages.status_transition_invalid')])->withInput();
    }

    $statusChanged = $validated['status'] !== $oldStatus;
    DB::transaction(function () use ($hseReport, $validated, $statusChanged, $oldStatus) {
        $hseReport->update($validated);

        if ($statusChanged) {
            HseReportStatusHistory::create([
                'hse_report_id' => $hseReport->id,
                'report_number' => $hseReport->report_number,
                'old_status' => $oldStatus,
                'new_status' => $hseReport->status,
                'changed_by' => auth()->id(),
                'changed_by_name' => auth()->user()->name,
            ]);
        }
    });

    if ($statusChanged && $hseReport->user_id) {
        $owner = $hseReport->user;
        if ($owner && $owner->isEmployee()) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    __('messages.status_changed_email_body', [
                        'report_number' => $hseReport->report_number,
                        'status' => __('messages.' . $validated['status']),
                    ]),
                    function ($message) use ($owner) {
                        $message->to($owner->email)
                            ->subject(__('messages.status_changed_email_subject'));
                    }
                );
            } catch (\Throwable $e) {
                // ignore mail errors
            }
        }
    }

    $message = $statusChanged
        ? __('messages.report_status_changed', ['status' => __('messages.' . $validated['status'])])
        : __('messages.report_updated');

    return redirect()
        ->route('hse-reports.show', $hseReport->id)
        ->with('success', $message);
}
public function analytics(Request $request)
{
    if (! auth()->user()?->isHseOfficer() && ! auth()->user()?->isAdmin()) {
        abort(403);
    }

    $filters = $request->validate([
        'start_date' => ['nullable', 'date'],
        'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        'risk_level' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
        'status' => ['nullable', Rule::in(['in_progress', 'processed', 'closed'])],
    ]);

    $query = HseReport::query();

    if (! empty($filters['start_date'])) {
        $query->whereDate('reported_at', '>=', $filters['start_date']);
    }

    if (! empty($filters['end_date'])) {
        $query->whereDate('reported_at', '<=', $filters['end_date']);
    }

    if (! empty($filters['risk_level'])) {
        $query->where('risk_level', $filters['risk_level']);
    }

    if (! empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    $summary = (clone $query)
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as risk_low', ['low'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as risk_medium', ['medium'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as risk_high', ['high'])
        ->selectRaw('COALESCE(SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END), 0) as risk_critical', ['critical'])
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as status_in_progress', ['in_progress'])
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as status_processed', ['processed'])
        ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as status_closed', ['closed'])
        ->first();

    $monthExpression = match (DB::connection()->getDriverName()) {
        'sqlite' => "strftime('%Y-%m', reported_at)",
        'pgsql' => "TO_CHAR(reported_at, 'YYYY-MM')",
        default => "DATE_FORMAT(reported_at, '%Y-%m')",
    };

    $monthlyData = (clone $query)
        ->whereNotNull('reported_at')
        ->selectRaw("{$monthExpression} as month")
        ->selectRaw('COUNT(*) as total')
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month');

    $chartEnd = ! empty($filters['end_date'])
        ? Carbon::parse($filters['end_date'])->startOfMonth()
        : now()->startOfMonth();
    $chartStart = ! empty($filters['start_date'])
        ? Carbon::parse($filters['start_date'])->startOfMonth()
        : $chartEnd->copy()->subMonths(11);

    $months = [];
    $monthlyCounts = [];
    for ($month = $chartStart->copy(); $month->lte($chartEnd); $month->addMonth()) {
        $monthKey = $month->format('Y-m');
        $months[] = $monthKey;
        $monthlyCounts[] = (int) ($monthlyData[$monthKey] ?? 0);
    }

    $riskLevels = ['low', 'medium', 'high', 'critical'];
    $riskCounts = [
        'low' => (int) $summary->risk_low,
        'medium' => (int) $summary->risk_medium,
        'high' => (int) $summary->risk_high,
        'critical' => (int) $summary->risk_critical,
    ];
    $statusCounts = [
        'in_progress' => (int) $summary->status_in_progress,
        'processed' => (int) $summary->status_processed,
        'closed' => (int) $summary->status_closed,
    ];
    $totalReports = (int) $summary->total;

    return Inertia::render('Analytics/Index', [
        'months' => $months,
        'monthlyCounts' => $monthlyCounts,
        'riskLevels' => $riskLevels,
        'riskCounts' => $riskCounts,
        'statusCounts' => $statusCounts,
        'totalReports' => $totalReports,
        'filters' => $filters,
        'labels' => [
            'title' => __('HSE Analytics'), 'subtitle' => __('Monthly reports and risk-level analysis'),
            'total' => __('Total Reports'), 'processed' => __('messages.processed'),
            'in_progress' => __('In Progress'), 'closed' => __('Closed Reports'),
            'filter' => __('Filter Analytics'), 'filter_hint' => __('Choose a date range, risk level, or report status.'),
            'start_date' => __('Start Date'), 'end_date' => __('End Date'), 'risk' => __('Risk Level'),
            'all_risks' => __('All Risk Levels'), 'status' => __('Report Status'), 'all_statuses' => __('All Statuses'),
            'apply' => __('Apply Filters'), 'reset' => __('Reset'), 'export_csv' => __('Export to Excel (CSV)'),
            'export_pdf' => __('Print / Save as PDF'), 'monthly' => __('Monthly Reports'),
            'monthly_hint' => __('Number of reports recorded in each of the last 12 months.'),
            'risk_analysis' => __('Risk Level Analysis'), 'risk_hint' => __('Distribution of reports across risk levels.'),
            'reports' => __('Reports'), 'low' => __('messages.low'), 'medium' => __('messages.medium'),
            'high' => __('messages.high'), 'critical' => __('messages.critical'),
        ],
    ]);
}

private function reportUiLabels(): array
{
    $keys = ['reports', 'new_report', 'edit_report', 'report_number', 'title', 'report_type', 'location', 'risk_level', 'status', 'reported_at', 'actions', 'view_details', 'delete_report', 'search_placeholder', 'all_types', 'all_risks', 'all_statuses', 'search', 'hazard', 'incident', 'near_miss', 'observation', 'low', 'medium', 'high', 'critical', 'in_progress', 'processed', 'closed', 'no_reports', 'confirm_delete_report', 'enter_details', 'please_correct', 'select_type', 'select_level', 'description', 'corrective_action', 'save', 'cancel', 'save_changes', 'back'];
    $labels = [];
    foreach ($keys as $key) $labels[$key] = __('messages.' . $key);
    $labels['export_all_hint'] = __('Export all reports matching the selected filters');
    $labels['export_report_hint'] = __('Export this report');
    $labels['export_excel'] = __('Export to Excel (CSV)');
    $labels['export_pdf'] = __('Print / Save as PDF');
    return $labels;
}
    public function destroy(HseReport $hseReport)
    {
        $user = auth()->user();
        $canDelete = $user?->isHseOfficer()
            || $user?->isAdmin()
            || ($user?->isEmployee() && (int) $hseReport->user_id === (int) $user->id);

        if (! $canDelete) {
            abort(403);
        }

        $hseReport->delete();

        $message = app()->getLocale() === 'ar'
            ? 'تم حذف تقرير HSE بنجاح'
            : 'HSE report deleted successfully.';

        return redirect()->route('hse-reports.index')->with('success', $message);
    }


}
