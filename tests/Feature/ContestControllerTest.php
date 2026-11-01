<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use App\Models\QuizEntry;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->moderator = User::factory()->create(['role' => 'moderator']);
    $this->user = User::factory()->create(['role' => 'user']);
});

describe('ContestController', function () {
    describe('publicIndex', function () {
        it('renders public index with published contests', function () {
            $contest = Contest::factory()->create(['status' => 'published']);

            $response = $this->actingAs($this->user)->get('/');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/PublicIndex'));
        });

        it('does not include draft contests in public index', function () {
            Contest::factory()->create(['status' => 'draft']);

            $response = $this->actingAs($this->user)->get('/');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->has('contests', 0));
        });

        it('redirects guests to login', function () {
            Contest::factory()->create(['status' => 'published']);

            $response = $this->get('/');

            $response->assertRedirect();
        });
    });

    describe('publicShow', function () {
        it('renders contest show page for published contest', function () {
            $contest = Contest::factory()->create(['status' => 'published']);

            $response = $this->actingAs($this->user)->get("/contests/{$contest->slug}");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/PublicShow'));
        });

        it('returns 404 for draft contest', function () {
            $contest = Contest::factory()->create(['status' => 'draft']);

            $response = $this->actingAs($this->user)->get("/contests/{$contest->slug}");

            $response->assertStatus(404);
        });

        it('returns 404 for unknown contest', function () {
            $contest = Contest::factory()->create();

            $response = $this->actingAs($this->user)->get("/contests/{$contest->id}");

            $response->assertStatus(404);
        });

        it('marks entry as viewed for authenticated user', function () {
            $contest = Contest::factory()->create(['status' => 'published']);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->post("/contests/{$contest->slug}/entries/{$entry->id}/view");

            $response->assertRedirect();
            expect($this->user->entryViews()->where('contest_id', $contest->id)
                ->where('entry_id', $entry->id)->count())->toBe(1);
        });
    });
});

describe('ContestAdminController', function () {
    describe('index', function () {
        it('renders admin contests list for admin', function () {
            $contest = Contest::factory()->create();

            $response = $this->actingAs($this->admin)->get('/admin/contests');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/index'));
        });

        it('renders admin contests list for moderator', function () {
            Contest::factory()->create();

            $response = $this->actingAs($this->moderator)->get('/admin/contests');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/index'));
        });

        it('filters contests by status', function () {
            Contest::factory()->create(['status' => 'published']);
            Contest::factory()->create(['status' => 'draft']);

            $response = $this->actingAs($this->admin)->get('/admin/contests?status=published');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->where('filters.status', 'published'));
        });

        it('filters contests by type', function () {
            Contest::factory()->create(['type' => \App\Enums\ContestType::VOTING]);
            Contest::factory()->create(['type' => \App\Enums\ContestType::QUIZ]);

            $response = $this->actingAs($this->admin)->get('/admin/contests?type=voting');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->where('filters.type', 'voting'));
        });

        it('filters contests by search', function () {
            Contest::factory()->create(['title' => 'Test Contest']);
            Contest::factory()->create(['title' => 'Other Contest']);

            $response = $this->actingAs($this->admin)->get('/admin/contests?search=Test');

            $response->assertStatus(200);
        });
    });

    describe('create', function () {
        it('renders create form for admin', function () {
            $response = $this->actingAs($this->admin)->get('/admin/contests/create');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/create'));
        });

        it('includes contest types in create form', function () {
            $response = $this->actingAs($this->admin)->get('/admin/contests/create');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->has('contestTypes'));
        });
    });

    describe('store', function () {
        it('creates a new contest for admin', function () {
            $data = [
                'title' => 'New Contest',
                'type' => 'voting',
                'description' => 'Contest description',
                'status' => 'draft',
            ];

            $response = $this->actingAs($this->admin)->post('/admin/contests', $data);

            $response->assertRedirect();
            expect(\App\Models\Contest::where('title', 'New Contest')->exists())->toBeTrue();
        });

        it('creates a new contest for moderator', function () {
            $data = [
                'title' => 'New Contest',
                'type' => 'quiz',
                'status' => 'draft',
            ];

            $response = $this->actingAs($this->moderator)->post('/admin/contests', $data);

            $response->assertRedirect();
            expect(\App\Models\Contest::where('title', 'New Contest')->exists())->toBeTrue();
        });

        it('rejects regular user from creating contest', function () {
            $data = ['title' => 'New Contest', 'type' => 'voting', 'status' => 'draft'];

            $response = $this->actingAs($this->user)->post('/admin/contests', $data);

            $response->assertStatus(403);
        });
    });

    describe('show', function () {
        it('renders contest show page for admin', function () {
            $contest = Contest::factory()->create();

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/show'));
        });
    });

    describe('edit', function () {
        it('renders edit form for contest author', function () {
            $contest = Contest::factory()->create(['user_id' => $this->admin->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/edit");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/edit'));
        });

        it('renders edit form for moderator', function () {
            $contest = Contest::factory()->create(['user_id' => $this->user->id]);

            $response = $this->actingAs($this->moderator)->get("/admin/contests/{$contest->id}/edit");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('contests/edit'));
        });
    });

    describe('update', function () {
        it('updates contest for author', function () {
            $contest = Contest::factory()->create([
                'user_id' => $this->admin->id,
                'title' => 'Old Title',
            ]);

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$contest->id}", [
                'title' => 'Updated Title',
                'type' => 'voting',
                'status' => 'published',
            ]);

            $response->assertRedirect();
            expect($contest->fresh()->title)->toBe('Updated Title');
        });
    });

    describe('destroy', function () {
        it('deletes contest for author', function () {
            $contest = Contest::factory()->create(['user_id' => $this->admin->id]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$contest->id}");

            $response->assertRedirect();
            expect(\App\Models\Contest::where('id', $contest->id)->exists())->toBeFalse();
        });

        it('deletes contest for moderator', function () {
            $contest = Contest::factory()->create(['user_id' => $this->user->id]);

            $response = $this->actingAs($this->moderator)->delete("/admin/contests/{$contest->id}");

            $response->assertRedirect();
            expect(\App\Models\Contest::where('id', $contest->id)->exists())->toBeFalse();
        });
    });

    describe('updateStatus', function () {
        it('updates contest status for author', function () {
            $contest = Contest::factory()->create([
                'user_id' => $this->admin->id,
                'status' => 'draft',
            ]);

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$contest->id}/status", [
                'status' => 'published',
            ]);

            $response->assertRedirect();
            expect($contest->fresh()->status)->toBe('published');
        });
    });
});
