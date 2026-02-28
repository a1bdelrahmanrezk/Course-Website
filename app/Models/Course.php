<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'level_id',
        'title',
        'slug',
        'description',
        'is_published',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function completedEnrollments()
    {
        return $this->hasMany(Enrollment::class)->whereNotNull('completed_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('courses')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('courses') ?: 'https://via.placeholder.com/400x300?text=Course+Image';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getCompletedUsersCountAttribute()
    {
        return $this->completedEnrollments()->count();
    }

    public function getTotalLessonsAttribute()
    {
        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('duration_seconds');
    }

    public function getFormattedDurationAttribute()
    {
        $totalSeconds = $this->total_duration;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }
}
