<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function index()
    {
        $this->ensureEmployer();

        $candidates = User::query()
            ->where('role', 'job_seeker')
            ->whereNotNull('resume_path')
            ->latest()
            ->get();

        return view('legacy.resumes.index', compact('candidates'));
    }

    public function show(User $user)
    {
        $this->ensureEmployer();
        abort_unless($user->role === 'job_seeker', 404);

        $resumeUrl = $user->resume_path ? Storage::disk('public')->url($user->resume_path) : null;

        return view('legacy.resumes.show', [
            'candidate' => $user,
            'resumeUrl' => $resumeUrl,
        ]);
    }

    public function download(User $user)
    {
        $this->ensureEmployer();
        abort_unless($user->role === 'job_seeker' && $user->resume_path, 404);

        $path = $user->resume_path;
        abort_unless(Storage::disk('public')->exists($path), 404);

        $filename = basename($path);

        return Storage::disk('public')->download($path, $filename);
    }

    protected function ensureEmployer(): void
    {
        if (Auth::guest() || ! in_array(Auth::user()->role, ['employer', 'admin'], true)) {
            abort(403, 'Only employers can view resumes.');
        }
    }
}

