<?php

use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->moderator = User::factory()->create(['role' => 'moderator']);
    $this->user = User::factory()->create(['role' => 'user']);
    $this->contest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'published']);
    $this->entry = QuizEntry::factory()->create([
        'contest_id' => $this->contest->id,
        'fields_data' => [
            'question' => 'What is 2+2?',
            'options' => [
                ['id' => 'opt_1', 'text' => '4', 'is_correct' => true],
                ['id' => 'opt_2', 'text' => '5', 'is_correct' => false],
            ],
            'type' => 'single',
        ],
    ]);
});

describe('QuizController admin routes', function () {
    describe('index', function () {
        it('renders quiz index for admin', function () {
            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/quizzes");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/index'));
        });

        it('renders quiz index for moderator', function () {
            $response = $this->actingAs($this->moderator)->get("/admin/contests/{$this->contest->id}/quizzes");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/index'));
        });

        it('rejects regular user from quiz index', function () {
            $response = $this->actingAs($this->user)->get("/admin/contests/{$this->contest->id}/quizzes");

            $response->assertStatus(403);
        });
    });

    describe('create', function () {
        it('renders create form for admin', function () {
            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/quizzes/create");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/builder'));
        });
    });

    describe('store', function () {
        it('creates a quiz entry', function () {
            $data = [
                'title' => 'New Quiz',
                'description' => 'Quiz description',
                'question' => 'What is PHP?',
                'options' => [
                    ['text' => 'Language', 'is_correct' => true],
                    ['text' => 'Snake', 'is_correct' => false],
                ],
                'type' => 'single',
            ];

            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/quizzes", $data);

            $response->assertRedirect();
            expect($this->contest->quizEntries)->toHaveCount(2);
            expect($this->contest->quizEntries->last()->title)->toBe('New Quiz');
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->admin)->post("/admin/contests/{$this->contest->id}/quizzes", []);

            $response->assertSessionHasErrors(['title', 'question', 'options', 'type']);
        });

        it('rejects regular user from storing quiz', function () {
            $data = [
                'title' => 'Unauthorized Quiz',
                'question' => 'Q?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ];

            $response = $this->actingAs($this->user)->post("/admin/contests/{$this->contest->id}/quizzes", $data);

            $response->assertStatus(403);
        });
    });

    describe('edit', function () {
        it('renders edit form for quiz entry', function () {
            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/quizzes/{$this->entry->id}/edit");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/builder'))
                ->assertInertia(fn ($page) => $page->where('quiz.title', $this->entry->title));
        });

        it('returns 404 for entry not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $otherEntry = QuizEntry::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->admin)->get("/admin/contests/{$this->contest->id}/quizzes/{$otherEntry->id}/edit");

            $response->assertStatus(404);
        });
    });

    describe('update', function () {
        it('updates a quiz entry', function () {
            $data = [
                'title' => 'Updated Quiz',
                'description' => 'Updated description',
                'question' => 'Updated question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                    ['text' => 'C', 'is_correct' => false],
                ],
                'type' => 'multiple',
            ];

            $response = $this->actingAs($this->admin)->patch("/admin/contests/{$this->contest->id}/quizzes/{$this->entry->id}", $data);

            $response->assertRedirect();
            expect($this->entry->fresh()->title)->toBe('Updated Quiz');
            expect($this->entry->fresh()->question)->toBe('Updated question?');
        });

        it('rejects regular user from updating quiz', function () {
            $response = $this->actingAs($this->user)->patch("/admin/contests/{$this->contest->id}/quizzes/{$this->entry->id}", [
                'title' => 'Hacked',
                'question' => 'Q?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            $response->assertStatus(403);
        });
    });

    describe('destroy', function () {
        it('deletes a quiz entry', function () {
            $response = $this->actingAs($this->admin)->delete("/admin/contests/{$this->contest->id}/quizzes/{$this->entry->id}");

            $response->assertRedirect();
            expect(QuizEntry::where('id', $this->entry->id)->exists())->toBeFalse();
        });

        it('rejects regular user from deleting quiz', function () {
            $response = $this->actingAs($this->user)->delete("/admin/contests/{$this->contest->id}/quizzes/{$this->entry->id}");

            $response->assertStatus(403);
        });
    });
});

describe('QuizController participant routes', function () {
    describe('take', function () {
        it('renders quiz take page for authenticated user', function () {
            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/take");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/take'))
                ->assertInertia(fn ($page) => $page->where('quiz.question', 'What is 2+2?'));
        });

        it('marks quiz as not already completed on first visit', function () {
            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/take");

            $response->assertInertia(fn ($page) => $page->where('alreadyCompleted', false));
        });

        it('returns 404 for unpublished contest', function () {
            $draftContest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'draft']);
            $entry = QuizEntry::factory()->create(['contest_id' => $draftContest->id]);

            $response = $this->actingAs($this->user)->get("/contest/{$draftContest->id}/quizzes/{$entry->id}/take");

            $response->assertStatus(404);
        });

        it('returns 404 for entry not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'published']);
            $otherEntry = QuizEntry::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$otherEntry->id}/take");

            $response->assertStatus(404);
        });
    });

    describe('submit', function () {
        it('submits a correct answer', function () {
            $response = $this->actingAs($this->user)->post("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/submit", [
                'selected_options' => ['opt_1'],
            ]);

            $response->assertRedirect();
            expect(QuizAnswer::where('user_id', $this->user->id)->where('quiz_entry_id', $this->entry->id)->count())->toBe(1);
            expect(QuizAnswer::where('user_id', $this->user->id)->first()->is_correct)->toBeTrue();
        });

        it('submits an incorrect answer', function () {
            $response = $this->actingAs($this->user)->post("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/submit", [
                'selected_options' => ['opt_2'],
            ]);

            $response->assertRedirect();
            expect(QuizAnswer::where('user_id', $this->user->id)->first()->is_correct)->toBeFalse();
        });

        it('validates selected_options', function () {
            $response = $this->actingAs($this->user)->post("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/submit", []);

            $response->assertSessionHasErrors('selected_options');
        });

        it('rejects entry not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'published']);
            $otherEntry = QuizEntry::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->user)->post("/contest/{$this->contest->id}/quizzes/{$otherEntry->id}/submit", [
                'selected_options' => ['opt_1'],
            ]);

            $response->assertStatus(404);
        });
    });

    describe('result', function () {
        it('shows result page after submission', function () {
            QuizAnswer::create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
                'selected_options' => ['opt_1'],
                'is_correct' => true,
            ]);

            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/result");

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('quizzes/result'))
                ->assertInertia(fn ($page) => $page->where('answer.is_correct', true));
        });

        it('redirects to take page if no answer exists', function () {
            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/result");

            $response->assertRedirect();
        });

        it('returns 404 for entry not belonging to contest', function () {
            $otherContest = Contest::factory()->create(['type' => ContestType::QUIZ]);
            $otherEntry = QuizEntry::factory()->create(['contest_id' => $otherContest->id]);

            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$otherEntry->id}/result");

            $response->assertStatus(404);
        });
    });

    describe('leaderboard', function () {
        it('returns leaderboard as JSON', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            QuizAnswer::create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $user1->id,
                'selected_options' => ['opt_1'],
                'is_correct' => true,
            ]);

            QuizAnswer::create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $user2->id,
                'selected_options' => ['opt_2'],
                'is_correct' => false,
            ]);

            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/leaderboard");

            $response->assertStatus(200)
                ->assertJsonStructure(['leaderboard']);

            $data = $response->json('leaderboard');
            expect($data)->toHaveCount(1);
            expect($data[0]['user_id'])->toBe($user1->id);
        });

        it('returns empty leaderboard when no correct answers', function () {
            $response = $this->actingAs($this->user)->get("/contest/{$this->contest->id}/quizzes/{$this->entry->id}/leaderboard");

            $response->assertStatus(200)
                ->assertJson(['leaderboard' => []]);
        });

        it('returns 404 for unpublished contest', function () {
            $draftContest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'draft']);
            $entry = QuizEntry::factory()->create(['contest_id' => $draftContest->id]);

            $response = $this->actingAs($this->user)->get("/contest/{$draftContest->id}/quizzes/{$entry->id}/leaderboard");

            $response->assertStatus(404);
        });
    });
});
