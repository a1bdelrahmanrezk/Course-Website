<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'enrollments';
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgresses()
    {
        return $this->hasManyThrough(
            LessonProgress::class,
            Lesson::class,
            'course_id',
            'lesson_id',
            'course_id',
            'id'
        );
    }

    public function getProgressPercentageAttribute()
    {
        $totalLessons = $this->course->lessons()->count();
        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = $this->user->lessonProgresses()
            ->whereHas('lesson', function ($query) {
                $query->where('course_id', $this->course_id);
            })
            ->whereNotNull('completed_at')
            ->count();

        return round(($completedLessons / $totalLessons) * 100, 2);
    }

    public function isCompleted()
    {
        return $this->completed_at !== null;
    }
}
