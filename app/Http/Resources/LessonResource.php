<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'video_url' => $this->video_url,
            'duration_in_seconds' => $this->duration_in_seconds,
            'order_index' => $this->order_index,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
