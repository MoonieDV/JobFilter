<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JobController extends Controller
{
    public function index()
    {
        $this->ensureEmployer();

        $jobs = Auth::user()
            ->postedJobs()
            ->latest()
            ->paginate(10);

        return view('legacy.employer-jobs', compact('jobs'));
    }

    public function create()
    {
        $this->ensureEmployer();

        return view('legacy.post-job');
    }

    public function store(Request $request)
    {
        $this->ensureEmployer();

        $validated = $this->validateJob($request);

        $job = Auth::user()->postedJobs()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(6),
            'company_name' => $validated['company_name'],
            'location' => $validated['location'],
            'employment_type' => $validated['employment_type'],
            'experience_level' => $validated['experience_level'] ?? null,
            'salary' => $validated['salary'] ?? null,
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'required_skills' => $this->explodeSkills($validated['required_skills'] ?? null),
            'preferred_skills' => $this->explodeSkills($validated['preferred_skills'] ?? null),
            'status' => $validated['status'] ?? 'open',
            'published_at' => $validated['status'] === 'open' ? now() : null,
        ]);

        return redirect()->route('dashboard')->with('status', 'Job posted successfully!');
    }

    public function edit(Job $job)
    {
        $this->ensureEmployer();
        $this->authorizeJob($job);

        return view('legacy.edit-job', compact('job'));
    }

    public function update(Request $request, Job $job)
    {
        $this->ensureEmployer();
        $this->authorizeJob($job);

        $validated = $this->validateJob($request);

        $job->update([
            'title' => $validated['title'],
            'company_name' => $validated['company_name'],
            'location' => $validated['location'],
            'employment_type' => $validated['employment_type'],
            'experience_level' => $validated['experience_level'] ?? null,
            'salary' => $validated['salary'] ?? null,
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'required_skills' => $this->explodeSkills($validated['required_skills'] ?? null),
            'preferred_skills' => $this->explodeSkills($validated['preferred_skills'] ?? null),
            'status' => $validated['status'] ?? 'open',
            'published_at' => $validated['status'] === 'open' ? ($job->published_at ?? now()) : null,
        ]);

        return redirect()->route('employer.jobs.edit', $job)->with('status', 'Job updated successfully.');
    }

    public function destroy(Job $job)
    {
        $this->ensureEmployer();
        $this->authorizeJob($job);

        $job->delete();

        return redirect()->route('employer.jobs.index')->with('status', 'Job removed.');
    }

    public function apiUpdate(Request $request, Job $job)
    {
        $this->ensureEmployer();
        $this->authorizeJob($job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|string|max:50',
            'experience_level' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'required_skills' => 'nullable|array',
            'preferred_skills' => 'nullable|array',
        ]);

        $job->update([
            'title' => $validated['title'],
            'location' => $validated['location'],
            'employment_type' => $validated['employment_type'],
            'experience_level' => $validated['experience_level'] ?? null,
            'salary' => $validated['salary'] ?? null,
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'required_skills' => $validated['required_skills'] ?? null,
            'preferred_skills' => $validated['preferred_skills'] ?? null,
        ]);

        return response()->json([
            'status' => 'Job updated successfully',
            'job' => $job,
        ]);
    }

    protected function validateJob(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|string|max:50',
            'experience_level' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'required_skills' => 'nullable|string',
            'preferred_skills' => 'nullable|string',
            'status' => 'nullable|in:open,closed,draft',
        ]);
    }

    protected function explodeSkills(?string $skills): ?array
    {
        if (empty($skills)) {
            return null;
        }

        return collect(explode(',', $skills))
            ->map(fn ($skill) => trim($skill))
            ->filter()
            ->values()
            ->all();
    }

    protected function ensureEmployer(): void
    {
        if (Auth::guest() || ! in_array(Auth::user()->role, ['employer', 'admin'], true)) {
            abort(403, 'Only employers can manage jobs.');
        }
    }

    protected function authorizeJob(Job $job): void
    {
        if ($job->posted_by !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'You cannot modify this job.');
        }
    }
}
