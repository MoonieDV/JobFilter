<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InterviewScheduleController;
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
Route::get('/api/jobs/{job}', [JobBrowserController::class, 'apiShow'])->name('api.jobs.show');
Route::get('/api/jobs/{job}/views', [JobBrowserController::class, 'getViews'])->name('api.jobs.views');
Route::put('/api/jobs/{job}', [JobController::class, 'apiUpdate'])->name('api.jobs.update')->middleware('auth');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Route to serve resume files (outside auth middleware for embed access)
Route::get('/storage/resumes/{filename}', function ($filename) {
    // Just get the first available PDF file as a fallback
    $files = glob(storage_path('app/public/resumes/*.pdf'));
    
    if (empty($files)) {
        abort(404, 'No PDF files found');
    }
    
    $firstFile = $files[0]; // Use the first available PDF
    $file = file_get_contents($firstFile);
    $mimeType = mime_content_type($firstFile);
    $actualFilename = basename($firstFile);
    
    return response($file)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline; filename="' . $actualFilename . '"');
})->name('resumes.show')->where('filename', '.*');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/employer/total-views', [DashboardController::class, 'getTotalViews'])->name('api.employer.total-views');
    Route::get('/api/employer/average-score', [DashboardController::class, 'getAverageScore'])->name('api.employer.average-score');
    Route::get('/api/job-seeker/interview-count', [DashboardController::class, 'getInterviewCount'])->name('api.job-seeker.interview-count');
    Route::get('/api/job-seeker/average-match-score', [DashboardController::class, 'getJobSeekerAverageMatchScore'])->name('api.job-seeker.average-match-score');
    Route::get('/api/job-seeker/recommended-skills', [DashboardController::class, 'getRecommendedSkills'])->name('api.job-seeker.recommended-skills');
    Route::post('/api/skills', [\App\Http\Controllers\SkillController::class, 'store'])->name('api.skills.store');
    Route::get('/api/skills/by-category', [\App\Http\Controllers\SkillController::class, 'getSkillsByCategory'])->name('api.skills.by-category');
    Route::delete('/api/skills', [\App\Http\Controllers\SkillController::class, 'destroy'])->name('api.skills.destroy');

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

    Route::post('/interview-schedules', [InterviewScheduleController::class, 'store'])->name('interview-schedules.store');
    Route::get('/interview-schedules/{interviewSchedule}', [InterviewScheduleController::class, 'show'])->name('interview-schedules.show');
    
    // Debug endpoint
    Route::post('/test-interview', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'received' => $request->all(),
            'headers' => $request->headers->all(),
        ]);
    })->name('test-interview');

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
