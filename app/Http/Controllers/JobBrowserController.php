<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobBrowserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'location', 'employment_type']);

        $jobs = Job::active()
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($where) use ($search) {
                    $where->where('title', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['location'] ?? null, fn ($query, $location) => $query->where('location', 'like', "%{$location}%"))
            ->when($filters['employment_type'] ?? null, fn ($query, $type) => $query->where('employment_type', $type))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $user = Auth::user();
        $notifications = collect();
        $notifUnreadCount = 0;
        $appliedJobIds = [];

        if ($user) {
            $notifications = $user->alerts()->latest()->limit(10)->get();
            $notifUnreadCount = $notifications->where('is_read', false)->count();
            $appliedJobIds = Application::query()
                ->where('applicant_id', $user->id)
                ->whereNotIn('status', ['cancelled', 'canceled', 'rejected', 'hired'])
                ->pluck('job_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('legacy.jobs.index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'notifications' => $notifications,
            'notifUnreadCount' => $notifUnreadCount,
            'appliedJobIds' => $appliedJobIds,
            'user' => $user,
        ]);
    }

    public function show(Job $job)
    {
        if ($job->status !== 'open' && ! $this->canViewClosedJob($job)) {
            abort(404);
        }

        return view('jobs.show', compact('job'));
    }

    protected function canViewClosedJob(Job $job): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $job->posted_by === $user->id;
    }
}
