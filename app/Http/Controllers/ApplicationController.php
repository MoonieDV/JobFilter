<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use App\Services\SkillExtractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applications = Application::with(['job', 'applicant'])
            ->when($user->role === 'employer', fn ($query) => $query->where('employer_id', $user->id))
            ->when($user->role !== 'employer', fn ($query) => $query->where('applicant_id', $user->id))
            ->latest('applied_at')
            ->paginate(10);

        return view('applications.index', compact('applications'));
    }

    public function store(Request $request, Job $job, SkillExtractionService $skillExtractor)
    {
        $request->validate([
            'cover_letter' => 'nullable|string|max:5000',
            'resume' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx'])->max(5 * 1024),
            ],
            'use_saved_resume' => 'nullable|boolean',
        ]);

        $user = $request->user();

        if (! $user) {
            abort(403, 'Please sign in to apply for this job.');
        }

        $existing = Application::where('job_id', $job->id)
            ->where('applicant_id', $user->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['resume' => 'You have already applied to this job.']);
        }

        $resumePath = null;
        $uploadedNewResume = false;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
            $user->update(['resume_path' => $resumePath]);
            $uploadedNewResume = true;
        } elseif ($request->boolean('use_saved_resume', true) && $user->resume_path) {
            $resumePath = $user->resume_path;
        }

        if ($uploadedNewResume && $resumePath) {
            $this->refreshSkillsFromResume($skillExtractor, $user->id, $resumePath);
        }

        $application = Application::create([
            'job_id' => $job->id,
            'applicant_id' => $user->id,
            'employer_id' => $job->posted_by,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->company_address ?? $user->bio,
            'resume_path' => $resumePath,
            'cover_letter' => $request->input('cover_letter'),
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        Notification::create([
            'user_id' => $job->posted_by,
            'title' => 'New application received',
            'message' => "{$user->name} applied for {$job->title}.",
            'type' => 'info',
            'reference_id' => $application->id,
            'reference_type' => Application::class,
        ]);

        return back()->with('status', 'Application submitted! We will keep you posted via email.');
    }

    protected function refreshSkillsFromResume(SkillExtractionService $skillExtractor, int $userId, string $resumePath): void
    {
        try {
            $skills = $skillExtractor->extract($resumePath);
            if (! empty($skills)) {
                $skillExtractor->persist($userId, $skills);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function destroy(Application $application)
    {
        if ($application->applicant_id !== Auth::id()) {
            abort(403);
        }

        if ($application->resume_path) {
            Storage::disk('public')->delete($application->resume_path);
        }

        $application->delete();

        return back()->with('status', 'Application withdrawn.');
    }
}
