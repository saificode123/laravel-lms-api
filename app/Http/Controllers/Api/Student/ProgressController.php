<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Jobs\RecordLessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Mark a lesson as completed asynchronously via Redis Queue.
     */
    public function completeLesson(Request $request): JsonResponse
    {
        // 1. Lightning-fast validation. 
        // Notice we DO NOT use 'exists:lessons,id' here. 
        // Doing so would force a synchronous MySQL query, defeating the purpose of the queue!
        $request->validate([
            'lesson_id' => 'required|integer'
        ]);

        // 2. Dispatch the background job to Redis.
        // This takes ~2 milliseconds to execute.
        RecordLessonProgress::dispatch(
            Auth::id(), 
            $request->integer('lesson_id')
        );

        // 3. Instantly free up the Nginx Load Balancer and return success to the Vue frontend.
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson progress queued for recording.'
        ], 200);
    }
}