<?php

use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\QuizEntry;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->moderator = User::factory()->create(['role' => 'moderator']);
    $this->user = User::factory()->create(['role' => 'user']);
});

describe('EntryController', function () {
    describe('create', function () {
        it('renders create form for voting contest', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/entries/create");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('entries/Create'))
                ->assertInertia(fn ($page) => $page->where('contest.type', 'voting'));
        });

        it('renders create form for quiz contest', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/entries/create");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('entries/Create'))
                ->assertInertia(fn ($page) => $page->where('contest.type', 'quiz'));
        });

        it('rejects regular user from creating entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->user)->get("/admin/contests/{$contest->id}/entries/create");

            $response->assertStatus(403);
        });
    });

    describe('store for voting contest', function () {
        it('creates a voting entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $data = [
                'title' => 'My Entry',
                'description' => 'Entry description',
                'author_name' => 'John Doe',
                'author_department' => 'Engineering',
                'fields_data' => ['key' => 'value'],
            ];

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", $data);

            $response->assertRedirect();
            expect($contest->entries)->toHaveCount(1);
            $entry = $contest->entries->first();
            expect($entry->title)->toBe('My Entry');
            expect($entry->description)->toBe('Entry description');
            expect($entry->author_name)->toBe('John Doe');
            expect($entry->author_department)->toBe('Engineering');
            expect($entry->fields_data)->toBe(['key' => 'value']);
            expect($entry->user_id)->toBe($this->admin->id);
        });

        it('creates entry with nullable fields', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $data = [
                'title' => 'Minimal Entry',
            ];

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", $data);

            $response->assertRedirect();
            expect($contest->entries->first()->title)->toBe('Minimal Entry');
            expect($contest->entries->first()->description)->toBeNull();
        });

        it('validates required title', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'description' => 'No title',
            ]);

            $response->assertSessionHasErrors('title');
        });

        it('validates title max length', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'title' => str_repeat('a', 256),
            ]);

            $response->assertSessionHasErrors('title');
        });

        it('validates description max length', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'title' => 'Valid',
                'description' => str_repeat('a', 2001),
            ]);

            $response->assertSessionHasErrors('description');
        });

        it('rejects regular user from storing entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            $response = $this->actingAs($this->user)->post("/admin/contests/{$contest->id}/entries", [
                'title' => 'Unauthorized Entry',
            ]);

            $response->assertStatus(403);
        });
    });

    describe('store for quiz contest', function () {
        it('creates a quiz entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $data = [
                'title' => 'Quiz Title',
                'description' => 'Quiz description',
                'question' => 'What is 2+2?',
                'options' => [
                    ['text' => '4', 'is_correct' => true],
                    ['text' => '5', 'is_correct' => false],
                ],
                'type' => 'single',
                'sort_order' => 1,
                'show_from' => '2026-01-01 00:00:00',
                'show_until' => '2026-12-31 23:59:59',
            ];

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", $data);

            $response->assertRedirect();
            expect($contest->quizEntries)->toHaveCount(1);
            $entry = $contest->quizEntries->first();
            expect($entry->title)->toBe('Quiz Title');
            expect($entry->question)->toBe('What is 2+2?');
            expect($entry->type)->toBe('single');
            expect($entry->sort_order)->toBe(1);
            expect($entry->show_from)->not()->toBeNull();
            expect($entry->show_until)->not()->toBeNull();
        });

        it('generates options with IDs', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $data = [
                'title' => 'Quiz',
                'question' => 'Q?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                    ['text' => 'C', 'is_correct' => false],
                ],
                'type' => 'multiple',
            ];

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", $data);

            $response->assertRedirect();
            $entry = $contest->quizEntries->first();
            expect($entry->options)->toHaveCount(3);
            expect($entry->options[0]['id'])->toBe('opt_1');
            expect($entry->options[1]['id'])->toBe('opt_2');
            expect($entry->options[2]['id'])->toBe('opt_3');
        });

        it('validates minimum 2 options', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'title' => 'Quiz',
                'question' => 'Q?',
                'options' => [['text' => 'A', 'is_correct' => true]],
                'type' => 'single',
            ]);

            $response->assertSessionHasErrors('options');
        });

        it('validates option text and correct flag', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'title' => 'Quiz',
                'question' => 'Q?',
                'options' => [
                    ['text' => '', 'is_correct' => 'not_bool'],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            $response->assertSessionHasErrors(['options.0.text', 'options.0.is_correct']);
        });

        it('validates type is single or multiple', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$contest->id}/entries", [
                'title' => 'Quiz',
                'question' => 'Q?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'invalid',
            ]);

            $response->assertSessionHasErrors('type');
        });
    });

    describe('edit', function () {
        it('renders edit form for voting entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/entries/{$entry->id}/edit");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('entries/Edit'))
                ->assertInertia(fn ($page) => $page->where('entry.title', $entry->title));
        });

        it('renders edit form for quiz entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $entry = QuizEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest->id}/entries/{$entry->id}/edit");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('entries/Edit'))
                ->assertInertia(fn ($page) => $page->where('entry.title', $entry->title));
        });

        it('returns 404 for entry not belonging to contest', function () {
            $contest1 = Contest::factory()->create(['type' => ContestType::VOTING]);
            $contest2 = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create(['contest_id' => $contest2->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$contest1->id}/entries/{$entry->id}/edit");

            $response->assertStatus(404);
        });
    });

    describe('update for voting contest', function () {
        it('updates a voting entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'title' => 'Old Title',
            ]);

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$contest->id}/entries/{$entry->id}", [
                'title' => 'New Title',
                'description' => 'Updated description',
                'author_name' => 'Updated Name',
                'author_department' => 'Updated Dept',
                'fields_data' => ['new' => 'data'],
            ]);

            $response->assertRedirect();
            expect($entry->fresh()->title)->toBe('New Title');
            expect($entry->fresh()->description)->toBe('Updated description');
        });

        it('rejects regular user from updating entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->user)->patch("/admin/contests/{$contest->id}/entries/{$entry->id}", [
                'title' => 'Hacked',
            ]);

            $response->assertStatus(403);
        });
    });

    describe('update for quiz contest', function () {
        it('updates a quiz entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $entry = QuizEntry::factory()->create([
                'contest_id' => $contest->id,
                'title' => 'Old Title',
            ]);

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$contest->id}/entries/{$entry->id}", [
                'title' => 'New Title',
                'question' => 'New Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'multiple',
            ]);

            $response->assertRedirect();
            expect($entry->fresh()->title)->toBe('New Title');
            expect($entry->fresh()->question)->toBe('New Question?');
        });

        it('updates show dates only when provided', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $entry = QuizEntry::factory()->create([
                'contest_id' => $contest->id,
                'show_from' => '2026-01-01 00:00:00',
                'show_until' => '2026-12-31 23:59:59',
            ]);

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$contest->id}/entries/{$entry->id}", [
                'title' => 'Updated',
                'question' => 'Q?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            $response->assertRedirect();
            $updated = $entry->fresh();
            expect($updated->show_from)->not()->toBeNull();
            expect($updated->show_until)->not()->toBeNull();
        });
    });

    describe('destroy', function () {
        it('deletes a voting entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$contest->id}/entries/{$entry->id}");

            $response->assertRedirect();
            expect(ContestEntry::where('id', $entry->id)->exists())->toBeFalse();
        });

        it('deletes a quiz entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $entry = QuizEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$contest->id}/entries/{$entry->id}");

            $response->assertRedirect();
            expect(QuizEntry::where('id', $entry->id)->exists())->toBeFalse();
        });

        it('rejects regular user from deleting entry', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);
            $entry = ContestEntry::factory()->create(['contest_id' => $contest->id]);

            $response = $this->actingAs($this->user)->delete("/admin/contests/{$contest->id}/entries/{$entry->id}");

            $response->assertStatus(403);
        });
    });
});
