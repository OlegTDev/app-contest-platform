<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContestEntry>
 */
class ContestEntryFactory extends Factory
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
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'author_name' => fake()->name(),
            'author_department' => fake()->word(),
            'fields_data' => null,
            'votes_count' => 0,
        ];
    }
}
