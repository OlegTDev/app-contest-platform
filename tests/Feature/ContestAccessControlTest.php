<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContestAccessControlTest extends TestCase
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

    public function test_guest_is_redirected_from_contest_management_index(): void
    {
        $this->get(route('admin.contests.index'))
            ->assertRedirect();
    }

    public function test_regular_user_gets_403_on_contest_management_index(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.contests.index'))
            ->assertForbidden();
    }

    public function test_admin_gets_403_on_contest_management_index(): void
    {
        // Contest management is reserved for the moderator role.
        $this->actingAs($this->adminUser)
            ->get(route('admin.contests.index'))
            ->assertForbidden();
    }

    public function test_moderator_can_open_contest_management_index(): void
    {
        $this->actingAs($this->moderatorUser)
            ->get(route('admin.contests.index'))
            ->assertOk();
    }

    public function test_moderator_can_open_contest_create_form(): void
    {
        $this->actingAs($this->moderatorUser)
            ->get(route('admin.contests.create'))
            ->assertOk();
    }

    public function test_non_moderator_cannot_open_contest_create_form(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.contests.create'))
            ->assertForbidden();
    }

    public function test_moderator_can_open_contest_details(): void
    {
        $contest = Contest::factory()->create();

        $this->actingAs($this->moderatorUser)
            ->get(route('admin.contests.show', $contest))
            ->assertOk();
    }

    public function test_regular_user_gets_403_on_contest_details(): void
    {
        $contest = Contest::factory()->create();

        $this->actingAs($this->regularUser)
            ->get(route('admin.contests.show', $contest))
            ->assertForbidden();
    }

    public function test_regular_user_cannot_delete_a_contest(): void
    {
        $contest = Contest::factory()->create(['user_id' => $this->regularUser->id]);

        $this->actingAs($this->regularUser)
            ->delete(route('admin.contests.destroy', $contest))
            ->assertForbidden();

        $this->assertDatabaseHas('contests', ['id' => $contest->id]);
    }

    public function test_regular_user_can_still_vote_through_contest_participation_routes(): void
    {
        // Participation routes share the "admin/contests" prefix and must stay
        // available for authenticated participants.
        $contest = Contest::factory()->published()->create();
        $entry = ContestEntry::factory()->for($contest)->create();

        $this->actingAs($this->regularUser)
            ->post(route('admin.entries.vote', [$contest, $entry]))
            ->assertRedirect();

        $this->assertDatabaseHas('contest_votes', [
            'contest_id' => $contest->id,
            'entry_id' => $entry->id,
            'user_id' => $this->regularUser->id,
        ]);
    }
}
