<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\QuizEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizEntry>
 */
class QuizEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contest_id' => Contest::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'fields_data' => null,
            'sort_order' => 0,
            'show_from' => null,
            'show_until' => null,
        ];
    }

    /**
     * Indicate that the entry has a time window.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_from' => now()->subHour(),
            'show_until' => now()->addHour(),
        ]);
    }
}
