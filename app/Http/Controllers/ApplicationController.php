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

    public function show(Request $request, Application $application)
    {
        $user = $request->user();

        // Authorization: employer can view their own job's applications, applicant can view their own applications
        if ($user->role === 'employer') {
            if ($application->employer_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
        } else {
            if ($application->applicant_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
        }

        $application->load(['job', 'applicant']);

        if ($request->expectsJson()) {
            return response()->json([
                'application' => [
                    'id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $application->job?->title,
                    'full_name' => $application->full_name,
                    'applicant_name' => $application->applicant?->name,
                    'email' => $application->email,
                    'applicant_email' => $application->applicant?->email,
                    'phone' => $application->phone,
                    'applicant_phone' => $application->applicant?->phone,
                    'location' => $application->location,
                    'resume_path' => $application->resume_path,
                    'cover_letter' => $application->cover_letter,
                    'status' => $application->status,
                    'applied_at' => $application->applied_at,
                ],
            ]);
        }

        return view('applications.show', compact('application'));
    }

    public function store(Request $request, ?Job $job = null, SkillExtractionService $skillExtractor = null)
    {
        if ($skillExtractor === null) {
            $skillExtractor = app(SkillExtractionService::class);
        }

        // Handle both route patterns: POST /jobs/{job}/apply and POST /applications with job_id in body
        if ($job === null) {
            $jobId = $request->input('job_id');
            if (! $jobId) {
                abort(400, 'job_id is required');
            }
            $job = Job::findOrFail($jobId);
        }

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
            return response()->json(['error' => 'You have already applied to this job.'], 422);
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

        if ($request->expectsJson()) {
            return response()->json(['status' => 'Application submitted!', 'application' => $application], 201);
        }

        return back()->with('status', 'Application submitted! We will keep you posted via email.');
    }

    public function update(Request $request, Application $application, SkillExtractionService $skillExtractor = null)
    {
        if ($skillExtractor === null) {
            $skillExtractor = app(SkillExtractionService::class);
        }

        if ($application->applicant_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'cover_letter' => 'nullable|string|max:5000',
            'resume' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx'])->max(5 * 1024),
            ],
            'use_saved_resume' => 'nullable|boolean',
            'saved_resume_path' => 'nullable|string',
        ]);

        $user = $request->user();
        $resumePath = $application->resume_path;
        $uploadedNewResume = false;

        if ($request->hasFile('resume')) {
            // Delete old resume if it exists
            if ($application->resume_path) {
                Storage::disk('public')->delete($application->resume_path);
            }
            $resumePath = $request->file('resume')->store('resumes', 'public');
            $user->update(['resume_path' => $resumePath]);
            $uploadedNewResume = true;
        } elseif ($request->boolean('use_saved_resume', false)) {
            // Use the saved resume path from the request or user's current resume
            $savedPath = $request->input('saved_resume_path') ?: $user->resume_path;
            if ($savedPath) {
                $resumePath = $savedPath;
            }
        }

        if ($uploadedNewResume && $resumePath) {
            $this->refreshSkillsFromResume($skillExtractor, $user->id, $resumePath);
        }

        $application->update([
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->company_address ?? $user->bio,
            'resume_path' => $resumePath,
            'cover_letter' => $request->input('cover_letter'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'Application updated!', 'application' => $application], 200);
        }

        return back()->with('status', 'Application updated successfully.');
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
