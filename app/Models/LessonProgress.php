<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progresses';
    protected $fillable = [
        'user_id',
        'lesson_id',
        'started_at',
        'completed_at',
        'watch_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'watch_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function isCompleted()
    {
        return $this->completed_at !== null;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->lesson->duration_seconds === 0) {
            return 0;
        }

        return min(100, round(($this->watch_seconds / $this->lesson->duration_seconds) * 100, 2));
    }
}
