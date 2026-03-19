<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            
            // 1. DATA TRANSFORMATION: Send a fully qualified URL for the image, not just the database path
            'thumbnail_url' => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null,
            
            // 2. ENUM HANDLING: Safely extract the string value from your CourseStatus Enum
            'status' => $this->status?->value ?? $this->status,

            // 3. CONDITIONAL RELATIONSHIPS: Only include these if explicitly requested by the controller
            // This proves you know how to prevent N+1 database performance bottlenecks.
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),

            // 4. UI/UX FOCUS: Provide a standard date for sorting, and a human-readable date for the UI
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at?->diffForHumans(), // Outputs: "2 hours ago", "1 day ago"
        ];
    }
}