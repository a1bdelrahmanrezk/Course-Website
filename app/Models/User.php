<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonProgresses()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot('enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function completedCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->whereNotNull('completed_at')
            ->withPivot('enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function isEnrolledIn(Course $course)
    {
        return $this->enrollments()->where('course_id', $course->id)->exists();
    }

    public function hasCompletedCourse(Course $course)
    {
        return $this->enrollments()
            ->where('course_id', $course->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function getLessonProgress(Lesson $lesson)
    {
        return $this->lessonProgresses()->where('lesson_id', $lesson->id)->first();
    }

    public function hasCompletedLesson(Lesson $lesson)
    {
        return $this->lessonProgresses()
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
