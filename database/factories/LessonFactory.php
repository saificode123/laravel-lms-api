<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => fake()->sentence(3),
            'video_url' => 'https://www.youtube.com/watch?v=' . fake()->regexify('[a-zA-Z0-9_-]{11}'),
            'duration_in_seconds' => fake()->numberBetween(60, 3600),
            'order_index' => fake()->numberBetween(1, 15),
        ];
    }
}
