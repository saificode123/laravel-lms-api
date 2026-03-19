<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'status'
    ];

    protected $casts = [
        'status' => CourseStatus::class, // Auto-casts to Enum
    ];

    // Strict return types
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_index');
    }
}
