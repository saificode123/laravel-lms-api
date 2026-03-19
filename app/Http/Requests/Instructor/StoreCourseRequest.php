<?php

namespace App\Http\Requests\Instructor;

use App\Enums\CourseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated and has instructor role (role_id = 2)
        return Auth::check() && Auth::user()?->role_id === 2;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],

            // 1. UNIQUE CHECK: Ensure the generated slug doesn't already exist in the 'courses' table
            'slug'        => ['required', 'string', 'max:255', 'unique:courses,slug'],

            'description' => ['nullable', 'string'],

            // 2. FILE VALIDATION: If an instructor uploads a thumbnail, ensure it's actually a secure image under 2MB
            'thumbnail'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            // 3. ENUM VALIDATION: Use the Enum class we created earlier for strict type safety
            'status'      => ['nullable', Rule::enum(CourseStatus::class)],
        ];
    }

    /**
     * Prepare the data for validation before the rules are applied.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge([
                // Append a shorter, cleaner unique string (last 5 chars of uniqid) for better URLs
                'slug' => Str::slug($this->title) . '-' . substr(uniqid(), -5),
            ]);
        }
    }

    /**
     * 4. CUSTOM MESSAGES: Provide user-friendly errors for the Vue 3 frontend to display
     */
    public function messages(): array
    {
        return [
            'title.required'  => 'A course title is required to start building your content.',
            'thumbnail.image' => 'The thumbnail must be a valid image file (jpeg, png, jpg, webp).',
            'thumbnail.max'   => 'To keep the platform fast, thumbnails must not exceed 2MB.',
            'status.enum'     => 'The selected course status is invalid.',
        ];
    }
}