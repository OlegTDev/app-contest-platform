<?php

namespace Database\Factories;

use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAnswer>
 */
class QuizAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_entry_id' => QuizEntry::factory(),
            'user_id' => User::factory(),
            'answer' => fake()->sentence(),
            'is_correct' => fake()->boolean(50),
            'answered_at' => now(),
        ];
    }
}
