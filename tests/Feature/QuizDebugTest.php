<?php

use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\QuizEntry;
use App\Models\User;

it('debug: entry exists and contest exists', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $contest = Contest::factory()->create(['type' => ContestType::QUIZ, 'status' => 'published']);
    $entry = QuizEntry::factory()->create([
        'contest_id' => $contest->id,
        'fields_data' => [
            'question' => 'What is 2+2?',
            'options' => [
                ['id' => 'opt_1', 'text' => '4', 'is_correct' => true],
                ['id' => 'opt_2', 'text' => '5', 'is_correct' => false],
            ],
            'type' => 'single',
        ],
    ]);

    // Verify entry exists
    expect(QuizEntry::find($entry->id))->not()->toBeNull();
    expect(QuizEntry::find($entry->id)->contest_id)->toBe($contest->id);
    
    // Verify contest exists
    expect(Contest::find($contest->id))->not()->toBeNull();
    
    // Try the route
    $response = $this->actingAs($admin)->get("/admin/contests/{$contest->id}/quizzes/{$entry->id}/edit");
    
    // Dump the response
    dump([
        'status' => $response->status(),
        'content' => $response->content(),
    ]);
    
    $response->assertStatus(200);
});
