<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Add role_id to existing users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->default(3)->constrained()->cascadeOnDelete();
        });

        // 2. Courses Table
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Sections Table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // 4. Lessons Table
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('video_url')->nullable();
            $table->integer('duration_in_seconds')->default(0);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('courses');
        Schema::table('users', fn(Blueprint $table) => $table->dropForeign(['role_id']));
        Schema::dropIfExists('roles');
    }
};
