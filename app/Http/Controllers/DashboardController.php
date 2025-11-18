<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use App\Models\UserSkill;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $notifUnreadCount = $notifications->where('is_read', false)->count();

        $userSkills = UserSkill::query()
            ->where('user_id', $user->id)
            ->orderBy('skill_name')
            ->pluck('skill_name')
            ->filter()
            ->values();

        $userSkillsWithCategories = UserSkill::query()
            ->with('skill.category')
            ->where('user_id', $user->id)
            ->get()
            ->map(function (UserSkill $skill) {
                $category = $skill->skill?->category?->name ?? 'General';

                return [
                    'name' => $skill->skill_name,
                    'category' => $category,
                ];
            });

        $applicationsCount = Application::query()
            ->where('applicant_id', $user->id)
            ->count();

        $recentApplications = Application::query()
            ->with('job')
            ->where('applicant_id', $user->id)
            ->latest('applied_at')
            ->limit(10)
            ->get();

        $latestJobs = Job::query()
            ->active()
            ->latest('published_at')
            ->limit(10)
            ->get();

        $employerJobs = collect();
        $employerStats = [
            'activeJobs' => 0,
            'totalApplicants' => 0,
            'avgApplicantScore' => 0,
            'viewsCount' => 0,
        ];

        if (in_array($user->role, ['employer', 'admin'], true)) {
            $employerJobs = $user->postedJobs()
                ->with(['applications' => function ($query) {
                    $query->with('applicant')->latest('applied_at');
                }])
                ->withCount('applications')
                ->latest('created_at')
                ->get();

            $employerStats['activeJobs'] = $user->postedJobs()->active()->count();
            $employerStats['totalApplicants'] = Application::whereIn('job_id', $employerJobs->pluck('id'))->count();
            $employerStats['avgApplicantScore'] = 78; // Placeholder metric for UI
            $employerStats['viewsCount'] = 156; // Placeholder metric for UI
        }

        if ($user->role === 'admin' && request()->query('admin') === '1') {
            return view('legacy.admin-dashboard', [
                'user' => $user,
            ]);
        }

        return view('legacy.dashboard', [
            'user' => $user,
            'notifications' => $notifications,
            'notifUnreadCount' => $notifUnreadCount,
            'userSkills' => $userSkills,
            'userSkillsWithCategories' => $userSkillsWithCategories,
            'applicationsCount' => $applicationsCount,
            'recentApplications' => $recentApplications,
            'latestJobs' => $latestJobs,
            'employerJobs' => $employerJobs,
            'employerStats' => $employerStats,
        ]);
    }
}
