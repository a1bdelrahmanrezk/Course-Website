<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/enroll/{course}', [EnrollmentController::class, 'enroll'])
    ->middleware(['auth'])
    ->name('enroll');

Route::get('dashboard', [DashboardController::class, 'displayDashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');



require __DIR__.'/auth.php';
