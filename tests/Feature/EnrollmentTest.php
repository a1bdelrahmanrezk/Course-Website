<?php

use App\Models\Course;
use App\Models\Enrollment;
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
    Course::firstOrCreate([
        'slug' => 'test-course',
    ], [
        'level_id' => $level->id,
        'title' => 'Test Course',
        'slug' => 'test-course',
        'description' => 'Test course description',
        'is_published' => true,
    ]);
});

afterEach(function () {
    // Clean up enrollments created during tests
    Enrollment::truncate();
});

test('enrollment requires authentication', function () {
    $course = Course::first();
    
    $response = $this->post(route('enroll', $course));
    
    $response->assertRedirect(route('login'));
});

test('cannot enroll in draft courses', function () {
    $user = User::factory()->create();
    $course = Course::first();
    $course->update(['is_published' => false]);
    
    $response = $this->actingAs($user)->post(route('enroll', $course));
    
    $response->assertRedirect();
    $response->assertSessionHas('error', 'This course is not available for enrollment.');
    
    $this->assertDatabaseMissing('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);
});

test('enrollment is idempotent', function () {
    $user = User::factory()->create();
    $course = Course::first();
    
    // First enrollment
    $firstResponse = $this->actingAs($user)->post(route('enroll', $course));
    $firstResponse->assertRedirect();
    $firstResponse->assertSessionHas('success');
    
    $this->assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);
    
    $enrollmentCount = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->count();
    expect($enrollmentCount)->toBe(1);
    
    // Second enrollment attempt (should be idempotent)
    $secondResponse = $this->actingAs($user)->post(route('enroll', $course));
    $secondResponse->assertRedirect();
    $secondResponse->assertSessionHas('info', 'You are already enrolled in this course.');
    
    // Should still only have one enrollment record
    $enrollmentCountAfter = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->count();
    expect($enrollmentCountAfter)->toBe(1);
});

test('can enroll in published courses when authenticated', function () {
    $user = User::factory()->create();
    $course = Course::first();
    
    $response = $this->actingAs($user)->post(route('enroll', $course));
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $this->assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);
});

test('enrollment creates proper database record', function () {
    $user = User::factory()->create();
    $course = Course::first();
    
    $this->actingAs($user)->post(route('enroll', $course));
    
    $enrollment = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();
    
    expect($enrollment)->not->toBeNull();
    expect($enrollment->user_id)->toBe($user->id);
    expect($enrollment->course_id)->toBe($course->id);
    expect($enrollment->enrolled_at)->not->toBeNull();
    expect($enrollment->completed_at)->toBeNull();
});
