<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordLessonProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted before failing completely.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     * Using PHP 8 Constructor Property Promotion for clean code.
     */
    public function __construct(
        public int $userId,
        public int $lessonId
    ) {}

    /**
     * Execute the job.
     * This runs entirely in the background via the Redis queue worker.
     */
    public function handle(): void
    {
        try {
            // Using the DB facade query builder instead of Eloquent for maximum write speed.
            // updateOrInsert prevents duplicate database rows if a student double-clicks the "Complete" button.
            DB::table('lesson_progress')->updateOrInsert(
                [
                    'user_id' => $this->userId,
                    'lesson_id' => $this->lessonId,
                ],
                [
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]
            );

        } catch (Throwable $e) {
            // Log the exact error with context so you can debug it in storage/logs/laravel.log
            Log::error('Queue Error: Failed to record lesson progress', [
                'user_id' => $this->userId,
                'lesson_id' => $this->lessonId,
                'error' => $e->getMessage()
            ]);

            // Rethrow the exception so Laravel's queue manager knows this job failed 
            // and will attempt to retry it (up to 3 times).
            throw $e;
        }
    }
}