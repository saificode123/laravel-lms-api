<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // <-- 1. IMPORT THE CACHE FACADE
use App\Enums\CourseStatus;

class CourseController extends Controller
{
    /**
     * Fetch a paginated list of courses for the instructor.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $instructorId = Auth::id();
        $page = $request->get('page', 1); // Get current page for the cache key

        // 2. READ-THROUGH CACHE WITH TAGS
        // We tag this cache with the instructor's ID. If they fetch page 1, it saves to Redis for 1 hour.
        $courses = Cache::tags(['courses', "instructor_{$instructorId}"])
            ->remember("courses_page_{$page}", now()->addHours(1), function () use ($instructorId) {

                // This closure ONLY runs if the data is NOT in Redis.
                return Course::where('instructor_id', $instructorId)->latest()->paginate(10);
            });

        return CourseResource::collection($courses);
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            // 2. DATABASE TRANSACTIONS: If the file upload succeeds but the database insert fails, 
            // the transaction rolls everything back to prevent corrupted or orphaned data.
            $course = DB::transaction(function () use ($request) {

                $validatedData = $request->validated();
                $validatedData['instructor_id'] = Auth::id();

                // 3. FILE UPLOAD HANDLING: Check if a thumbnail was uploaded and store it securely
                if ($request->hasFile('thumbnail')) {
                    // Stores the image in storage/app/public/thumbnails and returns the file path
                    $validatedData['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
                }

                return Course::create($validatedData);
            });

            // 3. CACHE INVALIDATION
            // The instructor just created a new course. Their cached 'index' list is now outdated.
            // We instantly flush all caches tagged with their ID so the new course appears on their dashboard.
            Cache::tags(["instructor_" . Auth::id()])->flush();

            $course->load(['instructor', 'sections.lessons']);

            return response()->json([
                'status' => 'success',
                'message' => 'Course created successfully.',
                'data' => new CourseResource($course)
            ], 201);
        } catch (\Exception $e) {
            Log::error('Course creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the course. Please try again.',
            ], 500);
        }
    }

    /**
     * Display a specific course. (Scaffolding for the next step)
     */
    public function show(Course $course): CourseResource|JsonResponse
    {
        // 6. SECURITY: Ensure the logged-in instructor actually owns this course
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // 4. CACHE HEAVY RELATIONSHIPS
        // Eager loading sections and lessons is a heavy DB query. Let's cache this specific course for 24 hours.
        $courseData = Cache::tags(['courses', "course_{$course->id}"])
            ->remember("course_details_{$course->id}", now()->addHours(24), function () use ($course) {
                return $course->load(['instructor', 'sections.lessons']);
            });

        return new CourseResource($courseData);
    }

    /**
     * Update the course curriculum (sections and lessons).
     */
    public function updateCurriculum(Request $request, Course $course): JsonResponse
    {
        // SECURITY: Ensure the logged-in instructor actually owns this course
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        try {
            DB::transaction(function () use ($request, $course) {
                // Delete existing sections and lessons
                $course->sections()->delete();

                // Create new sections and lessons from request
                $sections = $request->input('sections', []);

                foreach ($sections as $sIndex => $sectionData) {
                    $section = $course->sections()->create([
                        'title' => $sectionData['title'],
                        'order_index' => $sIndex + 1,
                    ]);

                    $lessons = $sectionData['lessons'] ?? [];
                    foreach ($lessons as $lIndex => $lessonData) {
                        $section->lessons()->create([
                            'title' => $lessonData['title'],
                            'video_url' => $lessonData['video_url'] ?? null,
                            'order_index' => $lIndex + 1,
                        ]);
                    }
                }
            });

            // 5. TARGETED INVALIDATION
            // The curriculum changed. We must wipe this specific course's cache so students see the new videos immediately.
            // Also flush the instructor's course list cache to reflect updated lesson counts.
            Cache::tags(["course_{$course->id}", "instructor_{$course->instructor_id}"])->flush();

            // Return fresh data
            $course->load(['sections.lessons']);

            return response()->json([
                'status' => 'success',
                'message' => 'Curriculum updated successfully.',
                'data' => new CourseResource($course)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Curriculum update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the curriculum.',
            ], 500);
        }
    }

    /**
     * Public endpoint for students to view a course (no authentication required).
     * This is the endpoint that 5,000 students would hit when loading the same course page.
     */
    public function showPublic(Course $course): CourseResource
    {
        // Only allow viewing of published courses
        if ($course->status->value !== 'published') {
            // Or alternatively: if ($course->status !== CourseStatus::PUBLISHED) {
            abort(404, 'Course not found or not yet published.');
        }
        // CACHE HEAVY RELATIONSHIPS
        // This is the key caching scenario from Phase 3:
        // First student loads -> fetches from MySQL -> saves to Redis
        // Remaining 4,999 students -> served from Redis memory in milliseconds
        $courseData = Cache::tags(['courses', "course_{$course->id}"])
            ->remember("public_course_{$course->id}", now()->addHours(24), function () use ($course) {
                return $course->load(['instructor', 'sections.lessons']);
            });

        return new CourseResource($courseData);
    }
}
