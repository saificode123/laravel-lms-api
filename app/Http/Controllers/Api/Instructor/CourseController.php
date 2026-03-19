<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Fetch a paginated list of courses for the instructor.
     */
    public function index(): AnonymousResourceCollection
    {
        // 1. PAGINATION: Never use ->get() on resources that can grow infinitely. 
        // ->paginate(10) ensures the database only loads 10 courses into memory at a time.
        $courses = Course::where('instructor_id', Auth::id())->latest()->paginate(10);

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

            // 4. STANDARDIZED RESPONSE: Always return a consistent JSON structure
            $course->load(['instructor', 'sections.lessons']);

            return response()->json([
                'status' => 'success',
                'message' => 'Course created successfully.',
                'data' => new CourseResource($course)
            ], 201);
        } catch (\Exception $e) {
            // 5. ERROR LOGGING: Log the actual error for you to debug later, 
            // but return a safe, generic message to the frontend so you don't leak database info.
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

        // 7. EAGER LOADING: Load the sections and lessons here so the Resource can format them
        $course->load(['instructor', 'sections.lessons']);

        return new CourseResource($course);
    }
}
