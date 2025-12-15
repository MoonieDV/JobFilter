<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobViewService
{
    public function recordView(Job $job, Request $request): void
    {
        $userId = Auth::id();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Prevent duplicate views from the same user/IP within 5 minutes
        $existingView = JobView::query()
            ->where('job_id', $job->id)
            ->where(function ($query) use ($userId, $ipAddress) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('ip_address', $ipAddress);
                }
            })
            ->where('created_at', '>', now()->subMinutes(5))
            ->latest('created_at')
            ->first();

        if (!$existingView) {
            JobView::create([
                'job_id' => $job->id,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        }
    }

    public function getJobViewsCount(Job $job): int
    {
        return $job->views()->count();
    }

    public function getEmployerTotalViews($employerId): int
    {
        return JobView::query()
            ->whereIn('job_id', function ($query) use ($employerId) {
                $query->select('id')
                    ->from('jobs')
                    ->where('posted_by', $employerId);
            })
            ->count();
    }
}
