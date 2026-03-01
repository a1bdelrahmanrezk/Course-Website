<?php

use App\Http\Controllers\Web\CourseController;
use App\Http\Controllers\Web\LessonController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\EnrollmentController;
use App\Http\Controllers\Api\EnrollmentController as ApiEnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/courses/{slug}/lessons/{lessonId}', [LessonController::class, 'show'])->name('lessons.show');
Route::put('/courses/{slug}/lessons/{lessonId}/progress', [LessonController::class, 'updateProgress'])->name('lessons.progress');

Route::post('/enroll/{course}', [EnrollmentController::class, 'enroll'])
    ->middleware(['auth'])
    ->name('enroll');

Route::post('/api/enroll/{course}', [ApiEnrollmentController::class, 'enroll'])
    ->middleware(['auth'])
    ->name('api.enroll');

Route::get('dashboard', [DashboardController::class, 'displayDashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
