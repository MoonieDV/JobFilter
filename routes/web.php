<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobBrowserController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'legacy.index')->name('landing');
Route::view('/about', 'legacy.about')->name('legacy.about');
Route::view('/contact', 'legacy.contact')->name('legacy.contact');

Route::get('/jobs', [JobBrowserController::class, 'index'])->name('jobs.browse');
Route::get('/jobs/{job}', [JobBrowserController::class, 'show'])->name('jobs.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('employer')->name('employer.')->group(function () {
        Route::resource('jobs', JobController::class)->except(['show']);
    });

    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::put('/applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::post('/applications/{application}/schedule-interview', [ApplicationController::class, 'scheduleInterview'])->name('applications.scheduleInterview');
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('jobs.apply');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('/resumes', [ResumeController::class, 'index'])->name('resumes.index');
    Route::get('/resumes/{user}', [ResumeController::class, 'show'])->name('resumes.show');
    Route::get('/resumes/{user}/download', [ResumeController::class, 'download'])->name('resumes.download');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.readAll');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Legacy-style logout endpoint for links pointing to process_logout.php
Route::get('process_logout.php', function (Request $request) {
    if (Auth::check()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // Mirror legacy cookie cleanup for compatibility
    foreach (['user_email', 'user_role', 'user_name', 'firstname', 'lastname'] as $cookie) {
        cookie()->queue(cookie()->forget($cookie));
    }

    // Redirect to Laravel login page (equivalent to legacy login.php)
    return redirect()->route('login');
})->name('legacy.process_logout');

require __DIR__.'/auth.php';
