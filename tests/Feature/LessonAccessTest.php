<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Level;

beforeEach(function () {
    // Create a level if it doesn't exist
    $level = Level::firstOrCreate([
        'slug' => 'beginner',
    ], [
        'name' => 'Beginner',
        'slug' => 'beginner',
    ]);
    
    // Create a course if it doesn't exist
    $this->course = Course::firstOrCreate([
        'slug' => 'test-course',
    ], [
        'level_id' => $level->id,
        'title' => 'Test Course',
        'slug' => 'test-course',
        'description' => 'Test course description',
        'is_published' => true,
    ]);
    
    // Create lessons
    $this->freeLesson = Lesson::firstOrCreate([
        'course_id' => $this->course->id,
        'title' => 'Free Preview Lesson',
        'order' => 1,
        'duration_seconds' => 600,
        'is_free_preview' => true,
        'video_url' => 'https://example.com/free-video.mp4',
    ]);
    
    $this->paidLesson = Lesson::firstOrCreate([
        'course_id' => $this->course->id,
        'title' => 'Paid Lesson',
        'order' => 2,
        'duration_seconds' => 900,
        'is_free_preview' => false,
        'video_url' => 'https://example.com/paid-video.mp4',
    ]);
});

afterEach(function () {
    // Clean up enrollments created during tests
    Enrollment::truncate();
});

test('guests can access free preview lessons', function () {
    $response = $this->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    $response->assertViewIs('lessons.show');
    $response->assertViewHas('lesson', function ($lesson) {
        return $lesson->id === $this->freeLesson->id && $lesson->is_free_preview;
    });
});

test('guests cannot access paid lessons', function () {
    $response = $this->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->paidLesson->id,
    ]));
    
    $response->assertRedirect(route('courses.show', $this->course->slug));
    $response->assertSessionHas('error', 'You must be enrolled to access this lesson.');
});

test('authenticated users can access free preview lessons', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    $response->assertViewIs('lessons.show');
});

test('authenticated users cannot access paid lessons without enrollment', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->paidLesson->id,
    ]));
    
    $response->assertRedirect(route('courses.show', $this->course->slug));
    $response->assertSessionHas('error', 'You must be enrolled to access this lesson.');
});

test('enrolled users can access paid lessons', function () {
    $user = User::factory()->create();
    
    // Enroll the user in the course
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $this->course->id,
        'enrolled_at' => now(),
    ]);
    
    $response = $this->actingAs($user)->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->paidLesson->id,
    ]));
    
    $response->assertStatus(200);
    $response->assertViewIs('lessons.show');
    $response->assertViewHas('lesson', function ($lesson) {
        return $lesson->id === $this->paidLesson->id;
    });
});

test('enrolled users can access free preview lessons', function () {
    $user = User::factory()->create();
    
    // Enroll the user in the course
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $this->course->id,
        'enrolled_at' => now(),
    ]);
    
    $response = $this->actingAs($user)->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    $response->assertViewIs('lessons.show');
});

test('lesson progress is created for authenticated users accessing lessons', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    
    $this->assertDatabaseHas('lesson_progresses', [
        'user_id' => $user->id,
        'lesson_id' => $this->freeLesson->id,
    ]);
});

test('lesson progress is not created for guests accessing free lessons', function () {
    $response = $this->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    
    $this->assertDatabaseMissing('lesson_progresses', [
        'lesson_id' => $this->freeLesson->id,
    ]);
});

test('lesson access works with course slug and lesson id parameters', function () {
    $response = $this->get(route('lessons.show', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]));
    
    $response->assertStatus(200);
    $response->assertViewHas('course', function ($course) {
        return $course->slug === $this->course->slug;
    });
});

test('recording lesson completion updates lesson progress', function () {
    $user = User::factory()->create();
    
    // Enroll the user in the course to access paid lessons
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $this->course->id,
        'enrolled_at' => now(),
    ]);
    
    // Test updating progress for free lesson
    $response = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 300,
        'completed' => false,
    ]);
    
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    
    $this->assertDatabaseHas('lesson_progresses', [
        'user_id' => $user->id,
        'lesson_id' => $this->freeLesson->id,
        'watch_seconds' => 300,
        'completed_at' => null,
    ]);
});

test('marking lesson as completed sets completion timestamp', function () {
    $user = User::factory()->create();
    
    // Enroll the user in the course
    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $this->course->id,
        'enrolled_at' => now(),
    ]);
    
    // Mark lesson as completed
    $response = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->paidLesson->id,
    ]), [
        'watch_seconds' => 900,
        'completed' => true,
    ]);
    
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    
    $progress = \App\Models\LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->paidLesson->id)
        ->first();
    
    expect($progress)->not->toBeNull();
    expect($progress->watch_seconds)->toBe(900);
    expect($progress->completed_at)->not->toBeNull();
    expect($progress->isCompleted())->toBeTrue();
});

test('lesson progress can be updated multiple times', function () {
    $user = User::factory()->create();
    
    // First progress update
    $response1 = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 200,
        'completed' => false,
    ]);
    
    $response1->assertStatus(200);
    
    $this->assertDatabaseHas('lesson_progresses', [
        'user_id' => $user->id,
        'lesson_id' => $this->freeLesson->id,
        'watch_seconds' => 200,
    ]);
    
    // Second progress update (should update existing record)
    $response2 = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 400,
        'completed' => true,
    ]);
    
    $response2->assertStatus(200);
    
    // Should still be only one record with updated values
    $progressCount = \App\Models\LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->freeLesson->id)
        ->count();
    expect($progressCount)->toBe(1);
    
    $this->assertDatabaseHas('lesson_progresses', [
        'user_id' => $user->id,
        'lesson_id' => $this->freeLesson->id,
        'watch_seconds' => 400,
    ]);
});

test('guests cannot update lesson progress', function () {
    $response = $this->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 300,
        'completed' => false,
    ]);
    
    $response->assertStatus(401);
    $response->assertJson(['error' => 'Unauthorized']);
    
    $this->assertDatabaseMissing('lesson_progresses', [
        'lesson_id' => $this->freeLesson->id,
    ]);
});

test('users cannot update progress for lessons they dont have access to', function () {
    $user = User::factory()->create();
    
    // Try to update progress for paid lesson without enrollment
    $response = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->paidLesson->id,
    ]), [
        'watch_seconds' => 300,
        'completed' => false,
    ]);
    
    $response->assertStatus(401);
    $response->assertJson(['error' => 'Unauthorized']);
    
    $this->assertDatabaseMissing('lesson_progresses', [
        'user_id' => $user->id,
        'lesson_id' => $this->paidLesson->id,
    ]);
});

test('lesson progress validation works correctly', function () {
    $user = User::factory()->create();
    
    // Test missing watch_seconds
    $response = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'completed' => false,
    ]);
    
    $response->assertStatus(302); // Laravel redirects back with validation errors
    $response->assertSessionHasErrors(['watch_seconds']);
    
    // Test invalid watch_seconds (negative)
    $response = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => -100,
        'completed' => false,
    ]);
    
    $response->assertStatus(302); // Laravel redirects back with validation errors
    $response->assertSessionHasErrors(['watch_seconds']);
});

test('completion timestamp is only set once', function () {
    $user = User::factory()->create();
    
    // Mark as completed first time
    $response1 = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 600,
        'completed' => true,
    ]);
    
    $response1->assertStatus(200);
    
    $progress1 = \App\Models\LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->freeLesson->id)
        ->first();
    
    $firstCompletionTime = $progress1->completed_at;
    
    // Try to mark as completed again
    sleep(1);
    
    $response2 = $this->actingAs($user)->put(route('lessons.progress', [
        'slug' => $this->course->slug,
        'lessonId' => $this->freeLesson->id,
    ]), [
        'watch_seconds' => 600,
        'completed' => true,
    ]);
    
    $response2->assertStatus(200);
    
    $progress2 = \App\Models\LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->freeLesson->id)
        ->first();
    
    // Completion time should not have changed
    expect($progress2->completed_at)->toEqual($firstCompletionTime);
});
