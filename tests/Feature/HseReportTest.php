<?php

namespace Tests\Feature;

use App\Models\HseReport;
use App\Models\HseReportStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HseReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_dashboard_and_report_list_only_include_their_reports(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $ownReport = $this->createReport($employee, 'HSE-OWN-001');
        $this->createReport($otherEmployee, 'HSE-OTHER-001');

        $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.total', 1));

        $this->get(route('hse-reports.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->has('reports.data', 1)
                ->where('reports.data.0.report_number', $ownReport->report_number));
    }

    public function test_employee_cannot_view_or_export_another_employees_report(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $otherReport = $this->createReport($otherEmployee, 'HSE-OTHER-002');

        $this->actingAs($employee)
            ->get(route('hse-reports.show', $otherReport))
            ->assertForbidden();

        $this->get(route('hse-reports.report.export.pdf', $otherReport))
            ->assertForbidden();

        $this->get(route('hse-reports.report.export.excel', $otherReport))
            ->assertForbidden();

        $this->delete(route('hse-reports.destroy', $otherReport))
            ->assertForbidden();

        $this->assertDatabaseHas('hse_reports', ['id' => $otherReport->id]);
    }

    public function test_employee_can_delete_their_own_report(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $report = $this->createReport($employee, 'HSE-OWN-003');

        $this->actingAs($employee)
            ->delete(route('hse-reports.destroy', $report))
            ->assertRedirect(route('hse-reports.index'));

        $this->assertDatabaseMissing('hse_reports', ['id' => $report->id]);
    }

    public function test_employee_cannot_change_report_status(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $report = $this->createReport($employee, 'HSE-STATUS-EMPLOYEE');

        $this->actingAs($employee)
            ->put(route('hse-reports.update', $report), $this->reportInput($report, 'processed'))
            ->assertForbidden();

        $this->assertDatabaseHas('hse_reports', ['id' => $report->id, 'status' => 'in_progress']);
        $this->assertDatabaseCount('hse_report_status_histories', 0);
    }

    public function test_employee_report_creation_assigns_a_number_and_notifies_safety_staff(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'hse_officer']);

        $this->actingAs($employee)
            ->post(route('hse-reports.store'), [
                'title' => 'New hazard',
                'report_type' => 'hazard',
                'location' => 'Warehouse',
                'description' => 'A spill was found near the entrance.',
                'risk_level' => 'high',
            ])
            ->assertRedirect(route('hse-reports.index'));

        $this->assertDatabaseHas('hse_reports', [
            'user_id' => $employee->id,
            'report_number' => 'HSE-' . now()->format('Y') . '-000001',
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);
    }

    public function test_officer_can_advance_status_and_change_is_recorded_in_history(): void
    {
        $officer = User::factory()->create(['role' => 'hse_officer']);
        $employee = User::factory()->create(['role' => 'employee']);
        $report = $this->createReport($employee, 'HSE-STATUS-001');

        $this->actingAs($officer)
            ->put(route('hse-reports.update', $report), $this->reportInput($report, 'processed'))
            ->assertRedirect(route('hse-reports.show', $report));

        $this->assertDatabaseHas('hse_reports', ['id' => $report->id, 'status' => 'processed']);
        $this->assertDatabaseHas('hse_report_status_histories', [
            'hse_report_id' => $report->id,
            'old_status' => 'in_progress',
            'new_status' => 'processed',
            'changed_by' => $officer->id,
        ]);

        $this->put(route('hse-reports.update', $report), $this->reportInput($report, 'in_progress'))
            ->assertSessionHasErrors('status');

        $this->assertSame(1, HseReportStatusHistory::where('hse_report_id', $report->id)->count());
    }

    public function test_employee_export_download_is_filtered_to_their_reports(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $this->createReport($employee, 'HSE-EXPORT-OWN');
        $this->createReport($otherEmployee, 'HSE-EXPORT-OTHER');

        $response = $this->actingAs($employee)->get(route('hse-reports.export.excel'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('HSE-EXPORT-OWN', $csv);
        $this->assertStringNotContainsString('HSE-EXPORT-OTHER', $csv);
    }

    public function test_pdf_report_data_is_streamed_and_scoped_to_employee(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $this->createReport($employee, 'HSE-PDF-OWN');
        $this->createReport($otherEmployee, 'HSE-PDF-OTHER');

        $response = $this->actingAs($employee)->get(route('hse-reports.export.pdf.data'));

        $response->assertOk()->assertHeader('content-type', 'application/x-ndjson; charset=UTF-8');
        $stream = $response->streamedContent();
        $this->assertStringContainsString('HSE-PDF-OWN', $stream);
        $this->assertStringNotContainsString('HSE-PDF-OTHER', $stream);
    }

    public function test_pdf_print_page_receives_a_stream_url_instead_of_all_report_rows(): void
    {
        $officer = User::factory()->create(['role' => 'hse_officer']);
        $this->createReport($officer, 'HSE-PDF-STREAM');

        $this->actingAs($officer)
            ->get(route('hse-reports.export.pdf'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Print')
                ->has('reports', 0)
                ->where('reportCount', 1)
                ->where('dataUrl', route('hse-reports.export.pdf.data')));
    }

    private function createReport(User $owner, string $reportNumber): HseReport
    {
        return HseReport::create([
            'user_id' => $owner->id,
            'report_number' => $reportNumber,
            'title' => 'Test report',
            'report_type' => 'hazard',
            'location' => 'Test area',
            'description' => 'Test description',
            'risk_level' => 'medium',
            'status' => 'in_progress',
            'reported_at' => now(),
        ]);
    }

    private function reportInput(HseReport $report, string $status): array
    {
        return [
            'report_number' => $report->report_number,
            'title' => $report->title,
            'report_type' => $report->report_type,
            'location' => $report->location,
            'description' => $report->description,
            'risk_level' => $report->risk_level,
            'corrective_action' => $report->corrective_action,
            'reported_at' => $report->reported_at->format('Y-m-d H:i:s'),
            'status' => $status,
        ];
    }
}
