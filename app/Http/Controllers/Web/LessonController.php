<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function show($courseSlug, $lessonId)
    {
        $course = Course::with(['level', 'lessons' => function($query) {
            $query->orderBy('order', 'asc');
        }])
            ->where('slug', $courseSlug)
            ->firstOrFail();

        $lesson = $course->lessons()->where('id', $lessonId)->firstOrFail();

        // Check access permissions
        if (!$lesson->is_free_preview) {
            if (!Auth::check() || !Auth::user()->isEnrolledIn($course)) {
                return redirect()->route('courses.show', $courseSlug)
                    ->with('error', 'You must be enrolled to access this lesson.');
            }
        }

        // Get adjacent lessons for navigation
        $previousLesson = $course->lessons()->where('order', $lesson->order - 1)->first();
        $nextLesson = $course->lessons()->where('order', $lesson->order + 1)->first();

        // Get or create lesson progress
        $lessonProgress = null;
        if (Auth::check()) {
            $lessonProgress = LessonProgress::firstOrCreate([
                'user_id' => Auth::id(),
                'lesson_id' => $lesson->id,
            ], [
                'started_at' => now(),
                'watch_seconds' => 0,
            ]);
        }

        return view('lessons.show', compact(
            'course', 
            'lesson', 
            'previousLesson', 
            'nextLesson', 
            'lessonProgress'
        ));
    }

    public function updateProgress(Request $request, $courseSlug, $lessonId)
    {
        $request->validate([
            'watch_seconds' => 'required|integer|min:0',
            'completed' => 'boolean',
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $lesson = $course->lessons()->where('id', $lessonId)->firstOrFail();

        // Verify user has access to this lesson
        if (!$lesson->is_free_preview && !Auth::user()->isEnrolledIn($course)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Update lesson progress
        $lessonProgress = LessonProgress::updateOrCreate([
            'user_id' => Auth::id(),
            'lesson_id' => $lesson->id,
        ], [
            'watch_seconds' => $request->watch_seconds,
            'started_at' => now(),
        ]);

        // Mark as completed if requested
        if ($request->completed && !$lessonProgress->completed_at) {
            $lessonProgress->completed_at = now();
            $lessonProgress->save();
        }

        return response()->json([
            'success' => true,
            'progress' => $lessonProgress,
            'completed_at' => $lessonProgress->completed_at,
        ]);
    }
}
