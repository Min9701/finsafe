<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/toggle-status");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'INACTIVE',
        ]);
    }

    public function test_admin_can_activate_user(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
            'status' => 'INACTIVE',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/toggle-status");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_toggle_user_status(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $target = User::factory()->create([
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user)
            ->patch("/admin/users/{$target->id}/toggle-status");

        $response->assertStatus(403);
    }

    public function test_inactive_user_gets_logged_out(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
            'status' => 'INACTIVE',
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/login');
    }
}
