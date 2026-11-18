<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoinRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_review_pending_join_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->create([
            'name' => 'Waiting Employee',
            'role' => 'employee',
            'is_approved' => false,
        ]);
        User::factory()->create(['name' => 'Approved Employee', 'is_approved' => true]);

        $this->actingAs($admin)
            ->get(route('admin.join-requests.index'))
            ->assertOk()
            ->assertSee('Waiting Employee')
            ->assertDontSee('Approved Employee');

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('Waiting Employee')
            ->assertSee('Approved Employee');

        $this->post(route('admin.join-requests.approve', $pendingUser))
            ->assertRedirect(route('admin.join-requests.index'));

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'is_approved' => true,
            'role' => 'employee',
        ]);

        $this->post('/logout');
        $this->post('/login', [
            'email' => $pendingUser->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($pendingUser);
    }

    public function test_admin_can_deny_and_remove_a_pending_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingUser = User::factory()->create(['is_approved' => false]);

        $this->actingAs($admin)
            ->post(route('admin.join-requests.deny', $pendingUser))
            ->assertRedirect(route('admin.join-requests.index'));

        $this->assertDatabaseMissing('users', ['id' => $pendingUser->id]);
    }

    public function test_non_admin_cannot_access_join_requests(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $pendingUser = User::factory()->create(['is_approved' => false]);

        $this->actingAs($employee)
            ->get(route('admin.join-requests.index'))
            ->assertForbidden();

        $this->post(route('admin.join-requests.approve', $pendingUser))
            ->assertForbidden();
        $this->post(route('admin.join-requests.deny', $pendingUser))
            ->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $pendingUser->id, 'is_approved' => false]);
    }
}
