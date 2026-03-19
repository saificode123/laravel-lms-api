<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Instructor\CourseController;

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

        // 2. STUDENT ROUTES (For later)
        // Route::prefix('student')->name('student.')->group(function () { ... });
    });
});
