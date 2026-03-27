<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Instructor\CourseController;
use App\Http\Controllers\Api\Student\ProgressController; // <-- 1. IMPORT THE NEW CONTROLLER

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // PUBLIC ROUTES (No login required)
    // ==========================================
    Route::post('/login', [AuthController::class, 'login']);

    // Public course viewing endpoint (for students)
    // This is the endpoint that 5,000 students would hit when loading the same course page
    Route::get('/courses/{course}', [CourseController::class, 'showPublic']);


    // ==========================================
    // SECURE ROUTES (Requires valid API Token)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // Fetch the currently authenticated user
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // 1. INSTRUCTOR ROUTES
        Route::prefix('instructor')->name('instructor.')->group(function () {

            // All course endpoints are now locked behind Sanctum!
            Route::apiResource('courses', CourseController::class);

            // Curriculum update route
            Route::put('courses/{course}/curriculum', [CourseController::class, 'updateCurriculum']);
        });

        // 2. STUDENT ROUTES
        Route::prefix('student')->name('student.')->group(function () {
            
            // Phase 4: Asynchronous Queue Endpoint for tracking video progress
            Route::post('/progress/complete', [ProgressController::class, 'completeLesson']);
            
        });
    });
});