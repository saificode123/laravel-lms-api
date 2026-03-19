<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Instructor\CourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 1. API VERSIONING: Always version your API. If you change the structure later, 
// v1 will still work for older mobile apps or frontend clients.
Route::prefix('v1')->group(function () {

    // TEMPORARILY OUTSIDE AUTH MIDDLEWARE FOR POSTMAN TESTING
    Route::prefix('instructor')->name('instructor.')->group(function () {
        Route::apiResource('courses', CourseController::class);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        // We will move the instructor routes back here later!
    });
});