<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'role' => User::ROLE_MODERATOR,
        ])->assertRedirect();

        $this->regularUser->refresh();
        $this->assertEquals(User::ROLE_MODERATOR, $this->regularUser->role);
    }

    public function test_admin_can_promote_user_to_admin_role(): void
    {
        $this->actingAs($this->adminUser);

        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect();

        $this->regularUser->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $this->regularUser->role);
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
            'role' => User::ROLE_ADMIN,
        ])->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_update_role_via_patch(): void
    {
        // Outer 'auth' middleware redirects unauthenticated users to login (302)
        // The 'admin' middleware only applies to authenticated non-admin users (403)
        $this->patch(route('admin.users.update-role', $this->regularUser->getRouteKey()), [
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect();
    }

    public function test_command_assigns_role_to_existing_user(): void
    {
        $user = User::factory()->create();

        $output = Artisan::call('user:assign-role', [
            'email' => $user->email,
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $user->role);
    }

    public function test_command_fails_for_non_existent_email(): void
    {
        $output = Artisan::call('user:assign-role', [
            'email' => 'nonexistent@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertEquals(1, $output);
    }

    public function test_command_fails_for_invalid_role(): void
    {
        $user = User::factory()->create();

        $output = Artisan::call('user:assign-role', [
            'email' => $user->email,
            'role' => 'superadmin',
        ]);

        $this->assertEquals(1, $output);
    }

    public function test_command_fails_for_invalid_email_format(): void
    {
        $output = Artisan::call('user:assign-role', [
            'email' => 'not-an-email',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertEquals(1, $output);
    }

    public function test_command_can_assign_moderator_role(): void
    {
        $user = User::factory()->create();

        $output = Artisan::call('user:assign-role', [
            'email' => $user->email,
            'role' => User::ROLE_MODERATOR,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(User::ROLE_MODERATOR, $user->role);
    }

    public function test_command_can_demote_admin_to_regular_user(): void
    {
        $user = User::factory()->admin()->create();

        $output = Artisan::call('user:assign-role', [
            'email' => $user->email,
            'role' => User::ROLE_USER,
        ]);

        $this->assertEquals(0, $output);

        $user->refresh();
        $this->assertEquals(User::ROLE_USER, $user->role);
    }
}
