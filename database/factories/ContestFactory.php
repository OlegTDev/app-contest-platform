<?php

namespace Database\Factories;

use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contest>
 */
class ContestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'type' => ContestType::VOTING,
            'status' => 'draft',
            'description' => fake()->paragraph(),
            'project_schema' => null,
            'start_at' => null,
            'end_at' => null,
        ];
    }

    /**
     * Indicate that the contest is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the contest is of quiz type.
     */
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContestType::QUIZ,
        ]);
    }

    /**
     * Indicate that the contest is scheduled between the given dates.
     */
    public function scheduled(?string $startAt = null, ?string $endAt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt ?? now()->subDay(),
            'end_at' => $endAt ?? now()->addDay(),
        ]);
    }
}
