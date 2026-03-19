<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Enums\CourseStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create the base roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'instructor'],
            ['id' => 3, 'name' => 'student'],
        ]);

        // Create your dummy instructor (ID 1)
        $instructor = User::factory()->create([
            'id' => 1,
            'name' => 'Instructor Saifi',
            'email' => 'instructor@lms.com',
            'role_id' => 2,
        ]);

        // Create a few sample students
        User::factory()->count(5)->create([
            'role_id' => 3,
        ]);

        // Create demo courses with sections and lessons
        $this->createDemoCourse($instructor, [
            'title' => 'Complete Web Development Bootcamp',
            'description' => 'Learn HTML, CSS, JavaScript, React, Node.js and more in this comprehensive course.',
            'status' => CourseStatus::PUBLISHED,
            'sections' => [
                [
                    'title' => 'Getting Started',
                    'lessons' => [
                        ['title' => 'Course Introduction', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 300],
                        ['title' => 'Setting Up Your Environment', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 600],
                    ]
                ],
                [
                    'title' => 'HTML Fundamentals',
                    'lessons' => [
                        ['title' => 'Introduction to HTML', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 900],
                        ['title' => 'HTML Tags and Elements', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 1200],
                        ['title' => 'Forms and Inputs', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 1500],
                    ]
                ],
                [
                    'title' => 'CSS Styling',
                    'lessons' => [
                        ['title' => 'CSS Basics', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 800],
                        ['title' => 'Flexbox and Grid', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 1800],
                    ]
                ],
            ]
        ]);

        $this->createDemoCourse($instructor, [
            'title' => 'Vue.js 3 Masterclass',
            'description' => 'Master Vue 3 from scratch. Learn Composition API, Pinia, Vue Router and build real projects.',
            'status' => CourseStatus::PUBLISHED,
            'sections' => [
                [
                    'title' => 'Vue 3 Introduction',
                    'lessons' => [
                        ['title' => 'Why Vue.js?', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 450],
                        ['title' => 'Your First Vue App', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 720],
                    ]
                ],
                [
                    'title' => 'Composition API',
                    'lessons' => [
                        ['title' => 'Reactivity Fundamentals', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 1100],
                        ['title' => 'Refs and Reactive', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 950],
                        ['title' => 'Computed Properties', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 850],
                    ]
                ],
            ]
        ]);

        $this->createDemoCourse($instructor, [
            'title' => 'Laravel API Development',
            'description' => 'Build robust RESTful APIs with Laravel. Learn authentication, authorization, and best practices.',
            'status' => CourseStatus::DRAFT,
            'sections' => [
                [
                    'title' => 'Laravel Setup',
                    'lessons' => [
                        ['title' => 'Installing Laravel', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_in_seconds' => 600],
                    ]
                ],
            ]
        ]);
    }

    /**
     * Helper method to create a course with sections and lessons.
     */
    private function createDemoCourse(User $instructor, array $data): Course
    {
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => $data['title'],
            'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . uniqid(),
            'description' => $data['description'],
            'status' => $data['status'],
            'thumbnail' => null,
        ]);

        if (isset($data['sections'])) {
            foreach ($data['sections'] as $sIndex => $sectionData) {
                $section = $course->sections()->create([
                    'title' => $sectionData['title'],
                    'order_index' => $sIndex + 1,
                ]);

                if (isset($sectionData['lessons'])) {
                    foreach ($sectionData['lessons'] as $lIndex => $lessonData) {
                        $section->lessons()->create([
                            'title' => $lessonData['title'],
                            'video_url' => $lessonData['video_url'],
                            'duration_in_seconds' => $lessonData['duration_in_seconds'],
                            'order_index' => $lIndex + 1,
                        ]);
                    }
                }
            }
        }

        return $course;
    }
}
