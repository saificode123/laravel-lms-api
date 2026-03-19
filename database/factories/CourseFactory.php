<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'instructor_id' => User::factory(),
            'title' => $title,
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraphs(3, true),
            'thumbnail' => null,
            'status' => fake()->randomElement([CourseStatus::DRAFT, CourseStatus::PUBLISHED]),
        ];
    }

    /**
     * Indicate that the course is published.
     */
    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CourseStatus::PUBLISHED,
        ]);
    }

    /**
     * Indicate that the course is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CourseStatus::DRAFT,
        ]);
    }
}
