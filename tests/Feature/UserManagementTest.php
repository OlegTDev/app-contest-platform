<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $moderatorUser;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();
        $this->moderatorUser = User::factory()->moderator()->create();
        $this->regularUser = User::factory()->create();
    }

    public function test_unauthenticated_user_receives_redirect_on_admin_users_page(): void
    {
        // Outer 'auth' middleware redirects unauthenticated users to login (302)
        $this->get(route('admin.users.index'))
            ->assertRedirect();
    }

    public function test_regular_user_receives_403_on_admin_users_page(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    public function test_moderator_user_receives_403_on_admin_users_page(): void
    {
        $this->actingAs($this->moderatorUser)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_update_another_user_role_via_patch(): void
    {
        $this->actingAs($this->adminUser);

        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => UserRole::MODERATOR->value,
        ])->assertRedirect();

        $this->regularUser->refresh();
        $this->assertEquals(UserRole::MODERATOR, $this->regularUser->role);
    }

    public function test_admin_can_promote_user_to_admin_role(): void
    {
        $this->actingAs($this->adminUser);

        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => UserRole::ADMIN->value,
        ])->assertRedirect();

        $this->regularUser->refresh();
        $this->assertEquals(UserRole::ADMIN, $this->regularUser->role);
    }

    public function test_admin_cannot_assign_invalid_role(): void
    {
        $this->actingAs($this->adminUser);

        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => 'invalid_role',
        ])->assertSessionHasErrors(['role']);
    }

    public function test_non_admin_user_cannot_update_role_via_patch(): void
    {
        $this->actingAs($this->regularUser);

        $this->patch(route('admin.users.update-role', $this->moderatorUser->getRouteKey()), [
            'role' => UserRole::ADMIN->value,
        ])->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_update_role_via_patch(): void
    {
        // Outer 'auth' middleware redirects unauthenticated users to login (302)
        // The 'admin' middleware only applies to authenticated non-admin users (403)
        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => UserRole::ADMIN->value,
        ])->assertRedirect();
    }

    public function test_command_assigns_role_to_existing_user_by_login(): void
    {
        $user = User::factory()->create([
            'login' => 'testuser',
        ]);

        $output = Artisan::call('user:assign-role', [
            'login' => 'testuser',
            'role' => UserRole::ADMIN->value,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(UserRole::ADMIN, $user->role);
    }

    public function test_command_fails_for_non_existent_login(): void
    {
        $output = Artisan::call('user:assign-role', [
            'login' => 'nonexistent',
            'role' => UserRole::ADMIN->value,
        ]);

        $this->assertEquals(1, $output);
    }

    public function test_command_fails_for_invalid_role(): void
    {
        $user = User::factory()->create([
            'login' => 'testuser',
        ]);

        $output = Artisan::call('user:assign-role', [
            'login' => 'testuser',
            'role' => 'superadmin',
        ]);

        $this->assertEquals(1, $output);
    }

    public function test_command_can_assign_moderator_role_by_login(): void
    {
        $user = User::factory()->create([
            'login' => 'testuser',
        ]);

        $output = Artisan::call('user:assign-role', [
            'login' => 'testuser',
            'role' => UserRole::MODERATOR->value,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(UserRole::MODERATOR, $user->role);
    }

    public function test_command_can_demote_admin_to_regular_user_by_login(): void
    {
        $user = User::factory()->admin()->create([
            'login' => 'adminuser',
        ]);

        $output = Artisan::call('user:assign-role', [
            'login' => 'adminuser',
            'role' => UserRole::USER->value,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(UserRole::USER, $user->role);
    }
}
