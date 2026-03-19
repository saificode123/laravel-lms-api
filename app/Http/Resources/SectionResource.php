<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'order_index' => $this->order_index,
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
