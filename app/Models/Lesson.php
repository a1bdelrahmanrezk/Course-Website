<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lesson extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'course_id',
        'title',
        'order',
        'duration_seconds',
        'is_free_preview',
        'video_url',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgresses()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getNextLessonAttribute()
    {
        return $this->course->lessons()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    public function getPreviousLessonAttribute()
    {
        return $this->course->lessons()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function getFormattedDurationAttribute()
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('lessons');
    }
}
