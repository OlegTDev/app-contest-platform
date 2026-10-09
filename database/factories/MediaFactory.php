<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
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
            'file_name' => fake()->word().'.'.fake()->fileExtension(),
            'file_path' => 'media/'.fake()->filePath(),
            'file_type' => fake()->mimeType(),
            'file_extension' => fake()->fileExtension(),
            'file_size' => fake()->numberBetween(1024, 10485760),
            'is_main' => false,
        ];
    }
}
